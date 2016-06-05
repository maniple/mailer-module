<?php

namespace ManipleMailer\Queue;

use Doctrine\Common\Collections\ArrayCollection;
use ManipleMailer\Entity\Message;

abstract class AbstractQueue implements QueueInterface
{
    public function insert($messages)
    {
        $messages = $this->_toMessageCollection($messages);
        $this->_doInsert($messages->toArray());
    }

    public function save($messages)
    {
        $messages = $this->_toMessageCollection($messages);
        $this->_doSave($messages->toArray());
    }

    /**
     * Generates 32 character key for locking purposes.
     *
     * @return string
     */
    public function generateRandomKey()
    {
        return bin2hex(random_bytes(16)); // 32 chars
    }

    /**
     * @param mixed $messages
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \InvalidArgumentException
     */
    protected function _toMessageCollection($messages)
    {
        if ($messages instanceof Message) {
            $messages = array($messages);
        }

        if (!is_array($messages) && !$messages instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf(
                'Messages must be provided in an array or a Traversable, %s given',
                is_object($messages) ? get_class($messages) : gettype($messages)
            ));
        }

        $messageCollection = new ArrayCollection();

        foreach ($messages as $message) {
            if (!$message instanceof Message) {
                throw new \InvalidArgumentException(sprintf(
                    'Collection items must be instances of Message, %s given',
                    is_object($message) ? get_class($message) : gettype($message)
                ));
            }
            $messageCollection->add($message);
        }

        return $messageCollection;
    }

    /**
     * Performs storage dependent insertion of messages
     *
     * @param array<\ManipleMailer\Entity\Message> $messages
     */
    abstract protected function _doInsert(array $messages);

    /**
     * Performs storage dependent update of messages
     *
     * @param array<\ManipleMailer\Entity\Message> $messages
     */
    abstract protected function _doSave(array $messages);
}
