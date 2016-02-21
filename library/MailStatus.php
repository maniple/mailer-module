<?php

namespace MailerModule;

class MailStatus extends Enum
{
    const __default = self::PENDING;

    const PENDING = 'pending';

    const LOCKED = 'locked';

    const SENT = 'sent';

    const FAIL = 'fail';
}
