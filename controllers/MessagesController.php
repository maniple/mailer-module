<?php

class ManipleMailer_MessagesController extends Zefram_Controller_Action
{
    public function markReadAction()
    {
        $trackingKey = $this->getScalarParam('tracking_key');
        if ($trackingKey) {
            /** @var \Doctrine\ORM\EntityManager $entityManager */
            $entityManager = $this->getResource('EntityManager');
            $repository = $entityManager->getRepository('ManipleMailer\Entity\Message');

            // get message by tracking key
            /** @var \ManipleMailer\Entity\Message $message */
            $message = $repository->findOneBy(array(
                'trackingKey' => $trackingKey,
                'status' => \ManipleMailer\MailStatus::SENT,
            ));
            if ($message) {
                $message->setReadAt(new \DateTime('now'));
                $message->setStatus(\ManipleMailer\MailStatus::READ);
                $entityManager->persist($message);

                if ($campaign = $message->getCampaign()) {
                    $campaign->setReadMessageCount($campaign->getReadMessageCount() + 1);
                    $entityManager->persist($campaign);
                }

                $entityManager->flush();
            }
        }

        switch ($this->getScalarParam('format')) {
            case 'gif':
                $response = __DIR__ . '/../data/blank.gif';
                $contentType = 'image/gif';
                break;

            case 'mid':
                $response = __DIR__ . '/../data/blank.mid';
                $contentType = 'application/x-midi';
                break;

            default:
                $response = null;
                break;
        }

        if ($response) {
            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . filesize($response));
            readfile($response);
        }
        exit;
    }
}
