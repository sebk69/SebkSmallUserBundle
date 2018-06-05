<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2018 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Validator;

use Sebk\SmallOrmBundle\Validator\AbstractValidator;
use Sebk\SmallOrmBundle\Dao\ModelException;

class User extends AbstractValidator
{

    /**
     * Validate model user
     * @return boolean
     */
    public function validate()
    {
        $message = "";
        $result  = true;

        if(!$this->testNonEmpty("email")) {
            $message .= "The email is mandatory\n";
            $result = false;
        }

        if(!$this->testUnique("email")) {
            $message .= "This email has been already been registered\n";
            $result = false;
        }

        if(!$this->testNonEmpty("nickname")) {
            $message .= "The nickname is mandatory\n";
            $result = false;
        }

        if(!$this->testUnique("nickname")) {
            $message .= "This nickname has been already been registered\n";
            $result = false;
        }

        if (filter_var($this->model->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
            $message .= "Email format is not valid\n";
            $result = false;
        }

        $this->message = $message;

        return $result;
    }
}