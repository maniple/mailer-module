<?php

namespace MailerModule\Queue;

use MailerModule\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;

interface QueueInterface
{
    /**
     * Inserts messages in the queue
     *
     * @param \MailerModule\Entity\Message|\Traversable|array $messages
     */
    public function enqueue($messages);

    /**
     * Locks and returns first maxResults messages that are pending in the queue.
     *
     * @param int $maxResults
     * @param int $lockTimeout
     * @return \Doctrine\Common\Collections\ArrayCollection<\MailerModule\Entity\Mail>
     */
    public function dequeue($maxResults = 1, $lockTimeout = null);

    /**
     * Saves messages
     *
     * @param \MailerModule\Entity\Message|\Traversable|array $messages
     */
    public function save($messages);
}
