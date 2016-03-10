<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Model;

use Sebk\SmallOrmBundle\Dao\ModelException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Sebk\SmallOrmBundle\Dao\Model;

class User extends Model implements UserInterface, EquatableInterface
{
    /**
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     */
    public function onLoad() {
        $this->loadToMany("roles", array());
    }

    public function beforeSave()
    {
        try {
            if (($encoder = $this->getEncoder()) && ($plainPassword = $this->getPasswordToEncode())) {
                $this->setPassword($encoder->encodePassword($plainPassword, $this->getSalt()));
            }
        } catch (\Exception $e) {
            $this->setPassword(Model::FIELD_NOT_PERIST);
            $this->setSalt(Model::FIELD_NOT_PERIST);
        }

        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime);
        }
        $this->setUpdatedAt(new \DateTime);
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $rolesArray = array();
        foreach (parent::getRoles() as $role) {
            $rolesArray[] = $role->getRole();
        }

        return $rolesArray;
    }

    public function setRoles($roles) {
        if($roles === null) {
            parent::setRoles(null);
        } elseif(count($roles) == 0) {
            parent::setRoles(array());
        } elseif($roles[0] instanceof UserRole) {
            parent::setRoles($roles);
        } else {
            // TODO: manage array roles
        }
    }

    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role) {
        foreach($this->getRoles() as $userRole) {
            if($role == $userRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return parent::getPassword();
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return parent::getSalt();
    }

    public function eraseCredentials()
    {
        $this->setPassword("");
        $this->setSalt("");
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->getPassword() != $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() != $user->getSalt()) {
            return false;
        }

        if ($this->getUsername() != $user->getUsername()) {
            return false;
        }

        return true;
    }
}