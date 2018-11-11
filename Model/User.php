<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2018 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Model;

use Sebk\SmallOrmBundle\Dao\ModelException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Sebk\SmallOrmBundle\Dao\Model;
use Symfony\Component\Validator\Constraints\DateTime;
use Sebk\SmallUserBundle\Security\User as SecurityTokenUser;

class User extends Model
{
    /**
     * Action after loading model
     */
    public function onLoad(): void
    {
        // Convert database to model fields types
        $this->setRoles(json_decode($this->getRoles()));
        $this->setEnabled($this->getEnabled() == 1);
        $this->setCreatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $this->getCreatedAt()));
        $this->setUpdatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $this->getUpdatedAt()));
    }

    /**
     * Action before saving model
     */
    public function beforeSave(): void
    {
        // Convert model to database fields types
        $this->setRoles(json_encode($this->getRoles()));
        $this->setEnabled($this->getEnabled() ? 1 : 0);
        if($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime);
        }
        $this->setCreatedAt($this->getCreatedAt()->format("Y-m-d H:i:s"));
        $this->setUpdatedAt(date("Y-m-d H:i:s"));
    }

    /**
     * Action after saving model
     */
    public function afterSave(): void
    {
        // Convert database to model fields types
        $this->setRoles(json_decode($this->getRoles()));
        $this->setEnabled($this->getEnabled() == 1);
        $this->setCreatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $this->getCreatedAt()));
        $this->setUpdatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $this->getUpdatedAt()));
    }

    /**
     * Check if user has role
     * @param $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        foreach($this->getRoles() as $userRole) {
            if($role == $userRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set user from security token user
     * @param SecurityTokenUser $securityTokenUser
     * @return User
     */
    public function setFromSecurityTokenUser(SecurityTokenUser $securityTokenUser): User
    {
        $this->setEmail($securityTokenUser->getEmail());
        $this->setPassword($securityTokenUser->getPassword());
        $this->setSalt($securityTokenUser->getSalt());
        $this->setNickname($securityTokenUser->getNickname());
        $this->setEnabled($securityTokenUser->getEnabled());
        $this->setCreatedAt($securityTokenUser->getCreatedAt());
        $this->setUpdatedAt($securityTokenUser->getUpdatedAt());
        $this->setRoles($securityTokenUser->getRoles());

        return $this;
    }

    /**
     * Custom json serialize to convert dates and unset password
     * @return array
     */
    public function jsonSerialize()
    {
        $this->setCreatedAt($this->getCreatedAt()->format("Y-m-d H:i:s"));
        $this->setUpdatedAt(date("Y-m-d H:i:s"));
        $password = $this->getPassword();
        $this->setPassword(Model::FIELD_NOT_PERSIST);
        $salt = $this->getSalt();
        $this->setSalt(Model::FIELD_NOT_PERSIST);
        $result = parent::jsonSerialize();
        $this->setCreatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $this->getCreatedAt()));
        $this->setUpdatedAt(\DateTime::createFromFormat("Y-m-d H:i:s", $this->getUpdatedAt()));
        $this->setPassword($password);
        $this->setSalt($password);

        return $result;
    }
}