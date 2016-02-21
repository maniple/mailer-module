<?php

namespace MailerModule\Queue;

use MailerModule\Entity\Mail;

interface QueueInterface
{
    /**
     * Inserts mail in the queue
     *
     * @param \MailerModule\Entity\Mail $mail
     */
    public function enqueue(Mail $mail);

    /**
     * Locks and return first pending mail in the queue.
     *
     * @return \MailerModule\Entity\Mail|null
     */
    public function dequeue();
}
