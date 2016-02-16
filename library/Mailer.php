<?php

/*class Cronjob_Mailer implements Service_Cron_Cronjob
{
    public function run(Service_Cron $cron)
    {
        $db = $cron->getServiceLocator()->getService('db');

        // release mails that area locked for more than 1 hour

        // try to send 10 mails in a single run
        $processed = array();
        $mails_in_a_run = 10;
        while ($mails_in_a_run > 0) {
            // fail_count limit!!! && mail_id NOT IN ($processed)
            $sql = "SELECT * FROM mail_queue WHERE state = 'PENDING'";
            if ($processed) {
                $sql .= " AND mail_id NOT IN (";
                $sql .= implode(', ', array_map(array($db, 'quote'), $processed));
                $sql .= ")";
            }
            $sql .= " ORDER BY created_at LIMIT 1 FOR UPDATE"; // TODO maybe fetch (and lock) all messages needed
                                        // this will minimize index updates
            $mail = $db->fetchRow($sql);
            if (empty($mail)) {
                echo 'NO MORE MAILS TO SEND';
                break; // no more mails to send
            }

            $stmt = $db->query(
                "UPDATE mail_queue SET state = 'LOCKED', locked_at = ? WHERE mail_id = ? AND state = 'PENDING'",
                array(date('Y-m-d H:i:s'), $mail['mail_id'])
            );
            if ($stmt->rowCount()) {
                --$mails_in_a_run;
                // remember this mail's id so that it wont be queried in this run
                $processed[] = $mail['mail_id'];

                try {
                // row was successfully locked
                // TODO send mail
                $mailer = new Zefram_Mail;

                if ($mail['reply_to_email']) {
                    $mailer->setReplyTo($mail['reply_to_email'], $mail['reply_to_name']);
                }
                $mailer->addTo($mail['to_email'], $mail['to_name']);

                $bcc = json_decode($mail['bcc']);
                foreach ((array) $bcc as $address) {
                    $mailer->addBcc($address);
                }

                $mailer->setSubject($mail['subject']);
                if ($mail['format'] == Model_MailQueue::FORMAT_HTML) {
                    $mailer->setBodyHtml($mail['body']);
                } else {
                    $mailer->setBodyText($mail['body']);
                }
                $mailer->send();

                // att = mail->createAttachment(file_contents, type, content-disposition, encoding)
                // att->id = ...
                // $mail->send();

                $db->query(
                    "UPDATE mail_queue SET state = 'SENT', sent_at = ? WHERE mail_id = ?",
                    array(date('Y-m-d H:i:s'), $mail['mail_id'])
                );
                } catch (Exception $e) {
                    $db->query(
                        "UPDATE mail_queue SET state = ?, fail_count = fail_count + 1 WHERE mail_id = ?",
                        array($mail['fail_count'] > 3 ? 'FAILED' : 'PENDING', $mail['mail_id'])
                    );
                }
                usleep(500); // sleep 500 mseconds to avoid server load
            }
        }
    }
}*/
