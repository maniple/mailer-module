<?php

namespace MailerModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use MailerModule\MailStatus;
use MailerModule\RecipientType;

/**
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
     * @Column(name="message_id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="MailerModule\Entity\Campaign")
     * @JoinColumn(name="campaign_id", referencedColumnName="campaign_id")
     * @var \MailerModule\Entity\Campaign
     */
    protected $campaign;

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
     * @Column(name="priority", type="smallint", options={"default"=0})
     * @var int
     */
    protected $priority = 0;

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
     * @Column(name="lock_key", type="string", length=32, nullable=true, unique=true)
     * @var string
     */
    protected $lockKey;
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
     * @Column(name="body_text", type="text")
     * @var string
     */
    protected $content;

    /**
     * @OneToMany(targetEntity="MailerModule\Entity\Recipient", mappedBy="message")
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $recipients;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();

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
     * @return \MailerModule\Entity\Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param \MailerModule\Entity\Campaign $campaign
     * @return Message
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->campaign = $campaign;
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
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     * @return Message
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
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
        if ((null !== $this->status) && !$this->status instanceof MailStatus) {
            $this->status = MailStatus::create($this->status);
        }
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Message
     */
    public function setStatus($status)
    {
        $this->status = MailStatus::createOrNull($status);
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
     * @return mixed
     */
    public function getReplyToEmail()
    {
        return $this->replyToEmail;
    }

    /**
     * @param mixed $replyToEmail
     * @return Message
     */
    public function setReplyToEmail($replyToEmail)
    {
        $this->replyToEmail = $replyToEmail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReplyToName()
    {
        return $this->replyToName;
    }

    /**
     * @param mixed $replyToName
     * @return Message
     */
    public function setReplyToName($replyToName)
    {
        $this->replyToName = $replyToName;
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
     * @param string|RecipientType $recipientType
     * @return ArrayCollection
     */
    public function getRecipients($recipientType = null)
    {
        if (null === $recipientType) {
            return $this->recipients;
        }

        if (!$recipientType instanceof RecipientType) {
            $recipientType = RecipientType::create($recipientType);
        }

        return $this->recipients->filter(function (Recipient $entry) use ($recipientType) {
            return $recipientType->equals($entry->getType());
        });
    }
}
