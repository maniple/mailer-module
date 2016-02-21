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

            $lockKey = bin2hex(random_bytes(16)); // 32 chars

            $qb = QB::create($conn);
            $qb->insert($locksTable);
            $qb->values(array(
                '`lock_key`' => $lockKey,
                '`created_at`' => time(),
                '`mail_id`' =>
                    QB::create($conn)
                        ->select('mail_id')
                        ->from($mailsTable)
                        ->where($qb->expr()->eq('status', MailStatus::PENDING))
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

// QB with built-in quoting
class QB extends \Doctrine\DBAL\Query\QueryBuilder
{
    public function values(array $values)
    {
        $quotedKeys = array();
        foreach ($values  as $key => $value) {
            if (substr($key, 0, 1) === '`') {
                $key = $this->getConnection()->quoteIdentifier(trim($key, '`'));
            }
            if (is_scalar($value)) {
                $value = $this->quote($value);
            }
            $quotedKeys[$key] = $value;
        }
        return parent::values($quotedKeys);
    }

    public function quote($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        return $this->getConnection()->quote($value);
    }

    public static function create($conn)
    {
        return new self($conn);
    }
}
