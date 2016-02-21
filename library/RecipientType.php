<?php

namespace MailerModule;

class RecipientType extends Enum
{
    const __default = self::TO;

    const TO = 'to';

    const CC = 'cc';

    const BCC = 'bcc';
}
