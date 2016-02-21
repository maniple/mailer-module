<?php

namespace MailerModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use MailerModule\MailStatus;
use MailerModule\RecipientType;

/**
 * @Entity
 * @Table(
 *     name="mailer_mails",
 *     indexes={
 *         @Index(columns={"status", "priority", "created_at"})
 *     }
 * )
 */
class Mail
{
    /**
     * @Id
     * @Column(name="mail_id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     */
    protected $priority = 0;

    /**
     * @Column(name="fail_count", type="smallint", options={"default"=0})
     */
    protected $failCount = 0;

    /**
     * @Column(name="status", type="string", length=32)
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
     */
    protected $replyToName;

    /**
     * @Column(name="subject", type="string", length=255)
     */
    protected $subject;

    /**
     * @Column(name="body_text", type="text", nullable=true)
     */
    protected $bodyText;

    /**
     * @Column(name="body_html", type="text", nullable=true)
     */
    protected $bodyHtml;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @OneToMany(targetEntity="MailerModule\Entity\Recipient", mappedBy="mail")
     */
    protected $recipients;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
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
     * @return Mail
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return Mail
     */
    public function setCreatedAt($createdAt)
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
     * @return Mail
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
     * @return Mail
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
     * @return Mail
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
     * @return Mail
     */
    public function setFailCount($failCount)
    {
        $this->failCount = $failCount;
        return $this;
    }

    /**
     * @return mixed
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
     * @return Mail
     */
    public function setStatus($status)
    {
        $this->status = MailStatus::createOrNull($status);
        return $this;
    }

    /**
     * @return string
     */
    public function getLockKey()
    {
        return $this->lockKey;
    }

    /**
     * @param string $lockKey
     * @return Mail
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
     * @return Mail
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
     * @return Mail
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
     * @return Mail
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBodyText()
    {
        return $this->bodyText;
    }

    /**
     * @param mixed $bodyText
     * @return Mail
     */
    public function setBodyText($bodyText)
    {
        $this->bodyText = $bodyText;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBodyHtml()
    {
        return $this->bodyHtml;
    }

    /**
     * @param mixed $bodyHtml
     * @return Mail
     */
    public function setBodyHtml($bodyHtml)
    {
        $this->bodyHtml = $bodyHtml;
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
