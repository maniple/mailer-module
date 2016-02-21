<?php

namespace MailerModule\Entity;

use MailerModule\RecipientType;

/**
 * @Entity
 * @Table(
 *     name="mailer_recipients",
 *     indexes={
 *         @Index(name="mailer_recipients_mail_id", columns={"mail_id"})
 *     },
 *     uniqueConstraints={
 *         @UniqueConstraint(name="mailer_recipients_mail_id_email_key", columns={"mail_id", "email"})
 *     }
 * )
 */
class Recipient
{
    /**
     * @Id
     * @Column(name="recipient_id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $_id;

    /**
     * @ManyToOne(targetEntity="MailerModule\Entity\Mail")
     * @JoinColumn(name="mail_id", referencedColumnName="mail_id")
     * @var \MailerModule\Entity\Mail
    */
    protected $_mail;

    /**
     * @Column(name="email")
     * @var string
     */
    protected $_email;

    /**
     * @Column(name="name")
     * @var string
     */
    protected $_name;

    /**
     * @Column(name="recipient_type")
     * @var string
     */
    protected $_type = RecipientType::TO;

    /**
     * @return \MailerModule\Entity\Mail
     */
    public function getMail()
    {
        return $this->_mail;
    }

    /**
     * @param \MailerModule\Entity\Mail $mail
     * @return Recipient
     */
    public function setMail(Mail $mail = null)
    {
        $this->_mail = $mail;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * @param string $email
     * @return Recipient
     */
    public function setEmail($email)
    {
        $this->_email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     * @return Recipient
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * @return \MailerModule\RecipientType
     */
    public function getType()
    {
        if ((null !== $this->_type) && !$this->_type instanceof RecipientType) {
            $this->_type = RecipientType::create($this->_type);
        }
        return $this->_type;
    }

    /**
     * @param string|\MailerModule\RecipientType $type
     * @return Recipient
     */
    public function setType($type)
    {
        $this->_type = RecipientType::createOrNull($type);
        return $this;
    }
}
