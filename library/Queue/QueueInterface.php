<?php

namespace MailerModule\Queue;

use MailerModule\Entity\Mail;
use Doctrine\Common\Collections\ArrayCollection;

interface QueueInterface
{
    /**
     * Inserts mail in the queue
     *
     * @param \MailerModule\Entity\Mail $mail
     */
    public function enqueue(Mail $mail);

    /**
     * Locks and returns first maxResults mails that are pending in the queue.
     *
     * @param int $maxResults
     * @param int $lockTimeout
     * @return \Doctrine\Common\Collections\ArrayCollection<\MailerModule\Entity\Mail>
     */
    public function dequeue($maxResults = 1, $lockTimeout = null);
}
