<?php

namespace ManipleMailer\Service;

use Doctrine\ORM\EntityManager;
use ManipleMailer\Queue\DoctrineQueue;

abstract class QueueFactory
{
    /**
     * @param object $container
     * @return \ManipleMailer\Queue\QueueInterface
     * @throws Exception
     */
    public static function createService($container)
    {
        $entityManager = $container->EntityManager;

        if ($entityManager instanceof EntityManager) {
            $queue = new DoctrineQueue($entityManager);
        } else {
            throw new Exception('DbTableQueue is not implemented');
        }

        return $queue;
    }
}
