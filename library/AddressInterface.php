<?php

namespace ManipleMailer;

interface AddressInterface {

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getName();
}
