<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Validator;

use Sebk\SmallOrmBundle\Validator\AbstractValidator;
use Sebk\SmallOrmBundle\Dao\ModelException;

class User extends AbstractValidator
{

    /**
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
        
        /*
        if ($this->model->getPasswordToEncode() == "" && $this->fromDb == false) {
            $message .= "The password can't be empty\n";
            $result = false;
        }
        */
        try {
            if (strlen($this->model->getPasswordToEncode()) < 8) {
                $message .= "The password length can't be under 8 chars\n";
                $result = false;
            }
        } catch(ModelException $e) {
            
        }

        if (filter_var($this->model->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
            $message .= "Email format is not valid\n";
            $result = false;
        }

        $this->message = $message;
        return $result;
    }
}