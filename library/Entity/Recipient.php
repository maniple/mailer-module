<?php

namespace MailerModule\Entity;

use MailerModule\RecipientType;

/**
 * @Entity
 * @Table(
 *     name="mailer_recipients",
 *     indexes={
 *         @Index(columns={"mail_id"})
 *     },
 *     uniqueConstraints={
 *         @UniqueConstraint(columns={"mail_id", "email"})
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
    protected $id;

    /**
     * @ManyToOne(targetEntity="MailerModule\Entity\Mail")
     * @JoinColumn(name="mail_id", referencedColumnName="mail_id")
     * @var \MailerModule\Entity\Mail
    */
    protected $mail;

    /**
     * @Column(name="email")
     * @var string
     */
    protected $email;

    /**
     * @Column(name="name")
     * @var string
     */
    protected $name;

    /**
     * @Column(name="recipient_type")
     * @var string
     */
    protected $type = RecipientType::TO;

    /**
     * @return \MailerModule\Entity\Mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param \MailerModule\Entity\Mail $mail
     * @return Recipient
     */
    public function setMail(Mail $mail = null)
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Recipient
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Recipient
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \MailerModule\RecipientType
     */
    public function getType()
    {
        if ((null !== $this->type) && !$this->type instanceof RecipientType) {
            $this->type = RecipientType::create($this->type);
        }
        return $this->type;
    }

    /**
     * @param string|\MailerModule\RecipientType $type
     * @return Recipient
     */
    public function setType($type)
    {
        $this->type = RecipientType::createOrNull($type);
        return $this;
    }
}
