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
 *         @Index(name="mailer_mails_status_idx", columns={"status", "priority", "created_at"})
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
    protected $_id;

    /**
     * @Column(name="created_at", type="epoch")
     */
    protected $_createdAt;

    /**
     * @Column(name="sent_at", type="epoch", nullable=true)
     */
    protected $_sentAt;

    /**
     * @Column(name="priority", type="smallint", options={"default"=0})
     */
    protected $_priority = 0;

    /**
     * @Column(name="fail_count", type="smallint", options={"default"=0})
     */
    protected $_failCount = 0;

    /**
     * @Column(name="status", type="string", length=32)
     */
    protected $_status = MailStatus::PENDING;

    /**
     * @Column(name="reply_to_email", type="string", length=255, nullable=true)
     */
    protected $_replyToEmail;

    /**
     * @Column(name="reply_to_name", type="string", length=255, nullable=true)
     */
    protected $_replyToName;

    /**
     * @Column(name="subject", type="string", length=255)
     */
    protected $_subject;

    /**
     * @Column(name="body_text", type="text", nullable=true)
     */
    protected $_bodyText;

    /**
     * @Column(name="body_html", type="text", nullable=true)
     */
    protected $_bodyHtml;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @OneToMany(targetEntity="MailerModule\Entity\Recipient", mappedBy="mail")
     */
    protected $_recipients;

    public function __construct()
    {
        $this->_recipients = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     * @return Mail
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Mail
     */
    public function setCreatedAt($createdAt)
    {
        $this->_createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSentAt()
    {
        return $this->_sentAt;
    }

    /**
     * @param mixed $sentAt
     * @return Mail
     */
    public function setSentAt($sentAt)
    {
        $this->_sentAt = $sentAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     * @param mixed $priority
     * @return Mail
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFailCount()
    {
        return $this->_failCount;
    }

    /**
     * @param mixed $failCount
     * @return Mail
     */
    public function setFailCount($failCount)
    {
        $this->_failCount = $failCount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        if ((null !== $this->_status) && !$this->_status instanceof MailStatus) {
            $this->_status = MailStatus::create($this->_status);
        }
        return $this->_status;
    }

    /**
     * @param mixed $status
     * @return Mail
     */
    public function setStatus($status)
    {
        $this->_status = MailStatus::createOrNull($status);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReplyToEmail()
    {
        return $this->_replyToEmail;
    }

    /**
     * @param mixed $replyToEmail
     * @return Mail
     */
    public function setReplyToEmail($replyToEmail)
    {
        $this->_replyToEmail = $replyToEmail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReplyToName()
    {
        return $this->_replyToName;
    }

    /**
     * @param mixed $replyToName
     * @return Mail
     */
    public function setReplyToName($replyToName)
    {
        $this->_replyToName = $replyToName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * @param mixed $subject
     * @return Mail
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBodyText()
    {
        return $this->_bodyText;
    }

    /**
     * @param mixed $bodyText
     * @return Mail
     */
    public function setBodyText($bodyText)
    {
        $this->_bodyText = $bodyText;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBodyHtml()
    {
        return $this->_bodyHtml;
    }

    /**
     * @param mixed $bodyHtml
     * @return Mail
     */
    public function setBodyHtml($bodyHtml)
    {
        $this->_bodyHtml = $bodyHtml;
        return $this;
    }

    /**
     * @param string|RecipientType $recipientType
     * @return ArrayCollection
     */
    public function getRecipients($recipientType = null)
    {
        if (null === $recipientType) {
            return $this->_recipients;
        }

        if (!$recipientType instanceof RecipientType) {
            $recipientType = RecipientType::create($recipientType);
        }

        return $this->_recipients->filter(function (Recipient $entry) use ($recipientType) {
            return $recipientType->equals($entry->getType());
        });
    }
}
