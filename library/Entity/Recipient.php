<?php

namespace ManipleMailer\Entity;

use ManipleMailer\RecipientType;

/**
 * @Entity
 * @Table(
 *     name="mailer_recipients",
 *     indexes={
 *         @Index(columns={"message_id"})
 *     }
 * )
 */
class Recipient
{
    /**
     * @Id
     * @ManyToOne(targetEntity="ManipleMailer\Entity\Message")
     * @JoinColumn(name="message_id", referencedColumnName="message_id")
     * @var \ManipleMailer\Entity\Message
    */
    protected $message;

    /**
     * @Column(name="recipient_type", length=16)
     * @var string
     */
    protected $type = RecipientType::TO;

    /**
     * @Id
     * @Column(name="email", type="string", length=255)
     * @var string
     */
    protected $email;

    /**
     * @Column(name="name", type="string", length=255, nullable=true)
     * @var string
     */
    protected $name;

    /**
     * @return \ManipleMailer\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \ManipleMailer\Entity\Message $message
     * @return Recipient
     */
    public function setMessage(Message $message = null)
    {
        $this->message = $message;
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
     * @return \ManipleMailer\RecipientType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|\ManipleMailer\RecipientType $type
     * @return Recipient
     */
    public function setType($type)
    {
        if ($type !== null) {
            $type = RecipientType::assert($type);
        }
        $this->type = $type;
        return $this;
    }
}
