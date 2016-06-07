<?php

namespace ManipleMailer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use ManipleMailer\Address;
use ManipleMailer\MailStatus;
use ManipleMailer\RecipientType;

/**
 * Message represents a single mailer job.
 *
 * @Entity
 * @Table(
 *     name="mailer_messages",
 *     indexes={
 *         @Index(columns={"campaign_id"}),
 *         @Index(columns={"status", "priority", "created_at"})
 *     }
 * )
 */
class Message
{
    /**
     * @Id
     * @Column(name="message_id", type="bigint")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="ManipleMailer\Entity\Campaign")
     * @JoinColumn(name="campaign_id", referencedColumnName="campaign_id")
     * @var \ManipleMailer\Entity\Campaign
     */
    protected $campaign;

    /**
     * @Column(name="priority", type="smallint", options={"default"=0})
     * @var int
     */
    protected $priority = 0;

    /**
     * @Column(name="created_at", type="epoch")
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @Column(name="locked_at", type="epoch", nullable=true)
     * @var \DateTime
     */
    protected $lockedAt;

    /**
     * @Column(name="sent_at", type="epoch", nullable=true)
     * @var \DateTime
     */
    protected $sentAt;

    /**
     * @Column(name="read_at", type="epoch", nullable=true)
     * @var \DateTime
     */
    protected $readAt;

    /**
     * @Column(name="failed_at", type="epoch", nullable=true)
     * @var \DateTime
     */
    protected $failedAt;

    /**
     * @Column(name="fail_count", type="smallint", options={"default"=0})
     * @var int
     */
    protected $failCount = 0;

    /**
     * @Column(name="status", type="string", length=32)
     * @var string
     */
    protected $status = MailStatus::PENDING;

    /**
     * @Column(name="lock_key", type="string", length=64, nullable=true, unique=true)
     * @var string
     */
    protected $lockKey;

    /**
     * @Column(name="tracking_key", type="string", length=64, nullable=true, unique=true)
     * @var string
     */
    protected $trackingKey;

    /**
     * @Column(name="reply_to_email", type="string", length=255, nullable=true)
     */
    protected $replyToEmail;

    /**
     * @Column(name="reply_to_name", type="string", length=255, nullable=true)
     * @var string
     */
    protected $replyToName;

    /**
     * @Column(name="recipient_email", type="string", length=255)
     * @var string
     */
    protected $recipientEmail;

    /**
     * @Column(name="recipient_name", type="string", length=255, nullable=true)
     * @var string
     */
    protected $recipientName;

    /**
     * @Column(name="subject", type="string", length=255)
     * @var string
     */
    protected $subject;

    /**
     * @Column(name="content_type", type="string", length=16)
     * @var string
     */
    protected $contentType;

    /**
     * @Column(name="content", type="text")
     * @var string
     */
    protected $content;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime('now'));
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Message
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \ManipleMailer\Entity\Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param \ManipleMailer\Entity\Campaign $campaign
     * @return Message
     */
    public function setCampaign(Campaign $campaign = null)
    {
        $this->campaign = $campaign;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return Message
     */
    public function setPriority($priority)
    {
        $this->priority = (int) $priority;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Message
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLockedAt()
    {
        return $this->lockedAt;
    }

    /**
     * @param \DateTime $lockedAt
     * @return Message
     */
    public function setLockedAt(\DateTime $lockedAt = null)
    {
        $this->lockedAt = $lockedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @param mixed $sentAt
     * @return Message
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * @param \DateTime $readAt
     * @return Message
     */
    public function setReadAt(\DateTime $readAt = null)
    {
        $this->readAt = $readAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFailedAt()
    {
        return $this->failedAt;
    }

    /**
     * @param \DateTime $failedAt
     * @return Message
     */
    public function setFailedAt(\DateTime $failedAt = null)
    {
        $this->failedAt = $failedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFailCount()
    {
        return $this->failCount;
    }

    /**
     * @param mixed $failCount
     * @return Message
     */
    public function setFailCount($failCount)
    {
        $this->failCount = $failCount;
        return $this;
    }

    /**
     * @return MailStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Message
     */
    public function setStatus($status)
    {
        if ($status !== null) {
            $status = MailStatus::assert($status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     * @internal This method is used for locking purposes, and is not meant for public use
     */
    public function getLockKey()
    {
        return $this->lockKey;
    }

    /**
     * @param string $lockKey
     * @return Message
     * @internal This method is used for locking purposes, and is not meant for public use
     */
    public function setLockKey($lockKey)
    {
        $this->lockKey = $lockKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrackingKey()
    {
        return $this->trackingKey;
    }

    /**
     * @param string $trackingKey
     * @return Message
     */
    public function setTrackingKey($trackingKey)
    {
        $this->trackingKey = $trackingKey;
        return $this;
    }

    /**
     * @return Address
     */
    public function getReplyTo()
    {
        if (null === $this->replyToEmail) {
            return null;
        }
        return new Address($this->replyToEmail, $this->replyToName);
    }

    /**
     * @param Address $replyTo
     * @return Message
     */
    public function setReplyTo(Address $replyTo = null)
    {
        if (null === $replyTo) {
            $this->replyToEmail = null;
            $this->replyToName = null;
        } else {
            $this->replyToEmail = $replyTo->getEmail();
            $this->replyToName = $replyTo->getName();
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     * @return Message
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     * @return Message
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return Address
     */
    public function getRecipient()
    {
        if (null === $this->recipientEmail) {
            return null;
        }
        return new Address($this->recipientEmail, $this->recipientName);
    }

    /**
     * @param Address|string $email
     * @param string $name
     * @return Message
     */
    public function setRecipient($email, $name = null)
    {
        if ($email instanceof Address) {
            $address = $email;
        } else {
            $address = new Address($email, $name);
        }
        $this->recipientEmail = $address->getEmail();
        $this->recipientName = $address->getName();
        return $this;
    }
}
