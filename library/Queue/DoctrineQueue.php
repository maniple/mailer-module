<?php

namespace MailerModule\Queue;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use MailerModule\Entity\Campaign;
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

    public function enqueue($messages)
    {
        $messages = $this->_toMessageCollection($messages);

        $this->getEntityManager()->transactional(function (EntityManager $em) use ($messages) {
            foreach ($messages as $message) {
                /** @var Message $message */
                $message->setStatus(MailStatus::PENDING);
                $message->setTrackingKey(null);
                $message->setCreatedAt(new \DateTime('now'));
                $message->setLockedAt(null);
                $message->setSentAt(null);
                $message->setReadAt(null);
                $message->setFailCount(0);

                $em->persist($message);
            }
        });

        $this->refreshCampaignCounters(
            $this->_extractCampaignsFromMessages($messages)
        );
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

            foreach ($query->getResult() as $message) {
                /** @var Message $message */
                $message->setStatus(MailStatus::LOCKED);
                $message->setLockedAt(new \DateTime('now'));
                $message->setLockKey($self->generateLockKey());

                $em->persist($message);
                $collection->add($message);
            }
        };
        $this->getEntityManager()->transactional($transactional);

        return $collection;
    }

    public function save($messages)
    {
        $messages = $this->_toMessageCollection($messages);

        $this->getEntityManager()->transactional(function (EntityManager $em) use ($messages) {
            foreach ($messages as $message) {
                $em->persist($message);
            }
        });
    }

    public function refreshCampaignCounters(array $campaigns)
    {
        if (empty($campaigns)) {
            return;
        }

        $em = $this->getEntityManager();

        // due to compatibility issues (no uniform syntax for RDBMS)
        // it must be done in 3 selects - fortunately status column
        // is indexed, so the cost is not that big

        // Subselects aren't supported in DQL, we have to deal with raw SQL
        // The main complexity is properly handling the mapping between
        // entity fields and columns

        $campaignInfo = $em->getClassMetadata('MailerModule\Entity\Campaign');
        $messageInfo = $em->getClassMetadata('MailerModule\Entity\Message');

        $sql = "
            UPDATE [campaignTable]
            SET
                [messageCountColumn] = (
                    SELECT COUNT(1)
                        FROM [messageTable]
                        WHERE [campaignIdColumn] = [campaignTable].[idColumn]
                ),
                [sentMessageCountColumn] = (
                    SELECT COUNT(1)
                        FROM [messageTable]
                        WHERE [campaignIdColumn] = [campaignTable].[idColumn] AND [statusColumn] = :statusSent
                ),
                [failedMessageCountColumn] = (
                    SELECT COUNT(1)
                        FROM [messageTable]
                        WHERE [campaignIdColumn] = [campaignTable].[idColumn] AND [statusColumn] = :statusFailed
                ),
                [readMessageCountColumn] = (
                    SELECT COUNT(1)
                        FROM [messageTable]
                        WHERE [campaignIdColumn] = [campaignTable].[idColumn] AND [statusColumn] = :statusRead
                )
            WHERE
                [idColumn] IN (:campaignIds)
        ";

        $sql = strtr($sql, array(
            '[campaignTable]' => $campaignInfo->getTableName(),
            '[idColumn]' => $campaignInfo->getColumnName('id'),
            '[messageCountColumn]' => $campaignInfo->getColumnName('messageCount'),
            '[sentMessageCountColumn]' => $campaignInfo->getColumnName('sentMessageCount'),
            '[failedMessageCountColumn]' => $campaignInfo->getColumnName('failedMessageCount'),
            '[readMessageCountColumn]' => $campaignInfo->getColumnName('readMessageCount'),
            '[messageTable]' => $messageInfo->getTableName(),
            '[statusColumn]' => $messageInfo->getColumnName('status'),
            '[campaignIdColumn]' => $messageInfo->getSingleAssociationJoinColumnName('campaign'),
        ));

        $em->getConnection()->executeQuery(
            $sql,
            array(
                'statusSent' => MailStatus::SENT,
                'statusFailed' => MailStatus::FAILED,
                'statusRead' => MailStatus::READ,
                'campaignIds' => array_map(function (Campaign $campaign) {
                    return $campaign->getId();
                }, $campaigns),
            ),
            array(
                'campaignIds' => Connection::PARAM_INT_ARRAY,
            )
        );

        // refresh counter values in Campaign entities
        array_map(array($em, 'refresh'), $campaigns);
    }

    protected function _extractCampaignsFromMessages($messages)
    {
        // at this point all campaigns are expected to be persisted
        $campaigns = array();
        foreach ($messages as $message) {
            /** @var Message $message */
            if (null !== ($campaign = $message->getCampaign())) {
                $campaigns[] = $campaign;
            }
        }
        return $campaigns;
    }
}
