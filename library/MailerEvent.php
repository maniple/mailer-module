<?php

namespace ManipleMailer;

class MailerEvent extends \Zend_EventManager_Event
{
    const EVENT_SEND       = 'send';
    const EVENT_SEND_PRE   = 'send.pre';
    const EVENT_SEND_ERROR = 'send.error';

    /**
     * @var \ManipleMailer\Entity\Message
     */
    protected $_message;

    /**
     * @param \ManipleMailer\Entity\Message $message
     * @return $this
     */
    public function setMessage(Entity\Message $message)
    {
        $this->_message = $message;
        return $this;
    }

    /**
     * @return \ManipleMailer\Entity\Message
     */
    public function getMessage()
    {
        return $this->_message;
    }
}
