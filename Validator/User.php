<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Validator;

use Sebk\SmallOrmCore\Validator\AbstractValidator;
use Sebk\SmallOrmForms\Message\Message;
use Sebk\SmallOrmForms\Message\MessageCollection;

class User extends AbstractValidator
{

    /**
     * Validate model user
     * @return boolean
     */
    public function validate()
    {
        $message = new MessageCollection();
        $result  = true;

        if(!$this->testUnique("email")) {
            $message[] = new Message(Message::BLANK_TEMPLATE, ["Email already taken"]);
            $result = false;
        }

        if(!$this->testNonEmpty("email")) {
            $message[] = new Message(Message::BLANK_TEMPLATE, ["Email must be filled"]);
            $result = false;
        }

        if(!$this->testUnique("nickname")) {
            $message[] = new Message(Message::BLANK_TEMPLATE, ["Nickname already taken"]);
            $result = false;
        }

        if(!$this->testNonEmpty("nickname")) {
            $message[] = new Message(Message::BLANK_TEMPLATE, ["Nickname must be filled"]);
            $result = false;
        }

        $this->message = $message;
        return $result;
    }
}