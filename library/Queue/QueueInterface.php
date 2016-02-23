<?php

namespace MailerModule\Queue;

use MailerModule\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;

interface QueueInterface
{
    /**
     * Locks and returns first maxResults messages that are pending in the queue.
     *
     * @param int $maxResults
     * @param int $lockTimeout
     * @return \Doctrine\Common\Collections\ArrayCollection<\MailerModule\Entity\Mail>
     */
    public function fetch($maxResults = 1, $lockTimeout = null);

    /**
     * Inserts messages in the queue.
     *
     * @param \MailerModule\Entity\Message|\Traversable|array $messages
     */
    public function insert($messages);

    /**
     * Saves messages
     *
     * @param \MailerModule\Entity\Message|\Traversable|array $messages
     */
    public function save($messages);
}
