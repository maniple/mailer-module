<?php

namespace ManipleMailer\Queue;

use ManipleMailer\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;

interface QueueInterface
{
    /**
     * Locks and returns first maxResults messages that are pending in the queue.
     *
     * @param int $maxResults
     * @param int $lockTimeout
     * @param int $retryDelay
     * @return \Doctrine\Common\Collections\ArrayCollection<\ManipleMailer\Entity\Mail>
     */
    public function fetch($maxResults, $lockTimeout, $retryDelay);

    /**
     * Inserts messages in the queue.
     *
     * @param \ManipleMailer\Entity\Message|\Traversable|array $messages
     */
    public function insert($messages);

    /**
     * Saves messages
     *
     * @param \ManipleMailer\Entity\Message|\Traversable|array $messages
     */
    public function save($messages);
}
