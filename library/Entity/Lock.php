<?php

namespace MailerModule\Entity;

/**
 * @Entity
 * @Table(name="mailer_locks")
 */
class Lock
{
    /**
     * @Id
     * @Column(name="lock_id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $_id;

    /**
     * @OneToOne(targetEntity="MailerModule\Entity\Mail")
     * @JoinColumn(name="mail_id", referencedColumnName="mail_id")
     * @var \MailerModule\Entity\Mail
     */
    protected $_mail;

    /**
     * @Column(name="lock_key", type="string", length=64, unique=true)
     * @var string
     */
    protected $_lockKey;

    /**
     * @Column(name="created_at", type="epoch")
     * @var \DateTime
     */
    protected $_createdAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     * @return Lock
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @return \MailerModule\Entity\Mail
     */
    public function getMail()
    {
        return $this->_mail;
    }

    /**
     * @param \MailerModule\Entity\Mail $mail
     * @return Lock
     */
    public function setMail(Mail $mail = null)
    {
        $this->_mail = $mail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLockKey()
    {
        return $this->_lockKey;
    }

    /**
     * @param mixed $lockKey
     * @return Lock
     */
    public function setLockKey($lockKey)
    {
        $this->_lockKey = $lockKey;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Lock
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->_createdAt = $createdAt;
        return $this;
    }
}
