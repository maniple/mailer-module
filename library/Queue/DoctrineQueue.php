<?php

namespace MailerModule\Queue;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use MailerModule\Entity\Mail;
use MailerModule\MailStatus;

/**
 * Doctrine based queue for mail entities
 */
class DoctrineQueue implements QueueInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_entityManager;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @return $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->_entityManager = $entityManager;
        return $this;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     * @throws Exception
     */
    public function getEntityManager()
    {
        if (!$this->_entityManager) {
            throw new Exception('Entity manager is not provided');
        }
        return $this->_entityManager;
    }

    public function enqueue(Mail $mail)
    {
        $this->getEntityManager()->transactional(function (EntityManager $em) use ($mail) {
            if ($em->contains($mail)) {
                /*
                $qb = $em->createQueryBuilder();
                $qb->delete('MailerModule\Entity\Lock', 'lock');
                $qb->where('lock.mail = :mail');
                $qb->setParameter('mail', $mail);
                $qb->getQuery()->execute();
                */
                $q = $em->createQuery('DELETE MailerModule\Entity\Lock lock WHERE lock.mail = :mail');
                $q->setParameter('mail', $mail);
                $q->execute();
            }

            $mail->setStatus(MailStatus::PENDING);
            $mail->setCreatedAt(new \DateTime('now'));
            $mail->setSentAt(null);
            $mail->setFailCount(0);

            $em->persist($mail);
        });

        return $this;
    }

    public function dequeue()
    {
        $em = $this->getEntityManager();
        $em->getConnection()->transactional(function (Connection $conn) use ($em, &$lock) {
            $locksTable = $em->getClassMetadata('MailerModule\Entity\Lock')->getTableName();
            $mailsTable = $em->getClassMetadata('MailerModule\Entity\Mail')->getTableName();

            $lockKey = bin2hex(random_bytes(128)); // 32 chars

            $qb = $conn->createQueryBuilder();
            $qb->insert($locksTable);
            $qb->values(array(
                'lock_key' => $lockKey,
                'created_at' => time(),
                'mail_id' =>
                    $conn->createQueryBuilder()
                        ->select('mail_id')
                        ->from($mailsTable)
                        ->where('status = :pending')
                        ->setParameter('pending', MailStatus::PENDING)
                        ->orderBy('priority', 'DESC')
                        ->addOrderBy('created_at', 'ASC')
                        ->setMaxResults(1)
            ));

            echo $qb->getSQL();exit;

            $lock = $em->getRepository('MailerModule\Entity\Lock')->findOneBy(array(
                'lock_key' => $lockKey,
            ));
        });

        /** @var \MailerModule\Entity\Lock $lock */
        if ($lock) {
            return $lock->getMail();
        }

        return null;
    }
}
