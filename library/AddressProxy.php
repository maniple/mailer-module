<?php

namespace ManipleMailer;

class AddressProxy implements AddressInterface
{
    /**
     * @var \Zefram_Stdlib_CallbackHandler
     */
    protected $_emailGetter;

    /**
     * @var \Zefram_Stdlib_CallbackHandler
     */
    protected $_nameGetter;

    /**
     * @param callable $emailGetter
     * @param callable $nameGetter
     */
    public function __construct($emailGetter, $nameGetter)
    {
        $this->_emailGetter = new \Zefram_Stdlib_CallbackHandler($emailGetter);
        $this->_nameGetter = new \Zefram_Stdlib_CallbackHandler($nameGetter);
    }

    public function getEmail()
    {
        return $this->_emailGetter->call();
    }

    public function getName()
    {
        return $this->_nameGetter->call();
    }
}
