<?php

namespace MailerModule\Queue;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use MailerModule\Entity\Message;
use MailerModule\MailStatus;

/**
 * Doctrine based queue for mail entities
 */
class DoctrineQueue extends AbstractQueue
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_entityManager;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @return \MailerModule\Queue\DoctrineQueue
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->_entityManager = $entityManager;
        return $this;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     * @throws \Exception
     */
    public function getEntityManager()
    {
        if (!$this->_entityManager) {
            throw new \Exception('Entity manager is not provided');
        }
        return $this->_entityManager;
    }

    public function enqueue(Message $mail)
    {
        $this->getEntityManager()->transactional(function (EntityManager $em) use ($mail) {
            $mail->setStatus(MailStatus::PENDING);
            $mail->setCreatedAt(new \DateTime('now'));
            $mail->setLockedAt(null);
            $mail->setSentAt(null);
            $mail->setFailCount(0);

            $em->persist($mail);
        });

        return $this;
    }

    public function dequeue($maxResults = 1, $lockTimeout = null)
    {
        $maxResults = (int) $maxResults;
        $lockTimeout = (int) $lockTimeout;

        $collection = new ArrayCollection();

        $self = $this;
        $transactional = function (EntityManager $em) use ($self, $maxResults, $lockTimeout, $collection) {
            $qb = $em->createQueryBuilder();
            $qb->select('m');
            $qb->from('MailerModule\Entity\Message', 'm');
            $qb->where('m.status = :statusPending');
            $qb->setParameter('statusPending', MailStatus::PENDING);
            if ($lockTimeout > 0) {
                $qb->orWhere('m.status = :statusLocked AND m.lockedAt < :minLockedAt');
                $qb->setParameter('statusLocked', MailStatus::LOCKED);
                $qb->setParameter('minLockedAt', time() - $lockTimeout);
            }
            $qb->orderBy('m.priority', 'DESC');
            $qb->addOrderBy('m.createdAt', 'ASC');
            $qb->setMaxResults($maxResults);

            $query = $qb->getQuery();
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);

            foreach ($query->getResult() as $mail) {
                /** @var \MailerModule\Entity\Message $mail */
                $mail->setStatus(MailStatus::LOCKED);
                $mail->setLockedAt(new \DateTime('now'));
                $mail->setLockKey($self->generateLockKey());

                $em->persist($mail);
                $collection->add($mail);
            }
        };
        $this->getEntityManager()->transactional($transactional);

        return $collection;
    }
}
