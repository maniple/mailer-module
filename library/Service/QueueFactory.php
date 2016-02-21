<?php

namespace MailerModule\Service;

use Doctrine\ORM\EntityManager;
use MailerModule\Queue\DoctrineQueue;

abstract class QueueFactory
{
    /**
     * @param $container
     * @return \MailerModule\Queue\QueueInterface
     * @throws Exception
     */
    public static function createService($container)
    {
        $entityManager = $container->EntityManager;

        if ($entityManager instanceof EntityManager) {
            $queue = new DoctrineQueue();
            $queue->setEntityManager($entityManager);
        } else {
            throw new Exception('DbTableQueue is not implemented');
        }

        return $queue;
    }
}
