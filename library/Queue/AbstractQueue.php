<?php

namespace MailerModule\Queue;

abstract class AbstractQueue implements QueueInterface
{
    /**
     * Generates 32 character key for locking purposes.
     *
     * @return string
     */
    public function generateLockKey()
    {
        return bin2hex(random_bytes(16)); // 32 chars
    }
}
