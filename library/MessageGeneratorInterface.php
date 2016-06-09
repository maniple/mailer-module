<?php

namespace ManipleMailer;

use ManipleMailer\Entity\Message;

interface MessageGeneratorInterface
{
    /**
     * @return Message
     */
    public function nextMessage();
}
