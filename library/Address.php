<?php

namespace ManipleMailer;

class Address
{
    /**
     * @var string
     */
    protected $_email;

    /**
     * @var string
     */
    protected $_name;

    /**
     * Address constructor.
     * @param string $email
     * @param string $name OPTIONAL
     */
    public function __construct($email, $name = null)
    {
        $this->_setEmail($email);

        if (null !== $name) {
            $this->_setName($name);
        }
    }

    /**
     * @param string $email
     * @throws \InvalidArgumentException
     */
    protected function _setEmail($email)
    {
        if (!is_string($email) || empty($email)) {
            throw new \InvalidArgumentException('Email must be a valid email address');
        }
        if (preg_match("/[\r\n]/", $email)) {
            throw new \InvalidArgumentException('CRLF injection detected');
        }
        $validator = new \Zend_Validate_EmailAddress(\Zend_Validate_Hostname::ALLOW_DNS | \Zend_Validate_Hostname::ALLOW_LOCAL);
        if (!$validator->isValid($email)) {
            $errorMessages = $validator->getMessages();
            throw new \InvalidArgumentException(reset($errorMessages));
        }
        $this->_email = $email;
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     */
    protected function _setName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Name must be a string');
        }
        if (preg_match("/[\r\n]/", $name)) {
            throw new \InvalidArgumentException('CRLF injection detected');
        }
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}
