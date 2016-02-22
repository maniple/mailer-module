<?php

namespace MailerModule;

class MailStatus extends Enum
{
    const PENDING = 'pending';

    const LOCKED = 'locked';

    const SENT = 'sent';

    const FAILED = 'failed';

    const READ = 'read';
}
