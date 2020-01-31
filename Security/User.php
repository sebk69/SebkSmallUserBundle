<?php

/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2018 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Security;


use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Sebk\SmallUserBundle\Model\User as UserModel;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 * @package Sebk\SmallUserBundle\Security
 */
class User implements UserInterface, EquatableInterface
{
    protected $id;
    protected $email;
    protected $password;
    protected $nickname;
    protected $salt;
    protected $enabled;
    protected $createdAt;
    protected $updatedAt;
    protected $roles = [];

    /**
     * Return user id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get username : alias of getEmail
     * @return string
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * GEt password
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Get nickname
     * @return string
     */
    public function getNickname(): string
    {
        return $this->nickname;
    }

    /**
     * Get salt
     * @return string
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * Get enabled
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get created at
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Get updated at
     * @return \DateTime
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Get roles as array
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Set email
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        
        return $this;
    }

    /**
     * Set password
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        
        return $this;
    }

    /**
     * Set nickname
     * @param string $nickname
     * @return $this
     */
    public function setNickname(string $nickname): User
    {
        $this->nickname = $nickname;
        
        return $this;
    }

    /**
     * Set salt
     * @param string $salt
     * @return $this
     */
    public function setSalt(string $salt): User
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set enabled
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Set created at
     * @param \DateTime|null $createdAt
     * @return User
     */
    public function setCreatedAt(?\DateTime $createdAt): User
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Set updated at
     * @param \DateTime|null $updatedAt
     * @return User
     */
    public function setUpdatedAt(?\DateTime $updatedAt): User
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Set array of roles
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Add a role to user
     * @param string $roleToAdd
     * @return $this
     */
    public function addRole(string $roleToAdd): User
    {
        foreach($this->roles as $role) {
            if($role == $roleToAdd) {
                return $this;
            }
        }

        $this->roles[] = $roleToAdd;

        return $this;
    }

    /**
     * Delete a role for user
     * @param string $roleToSuppr
     * @return $this
     */
    public function removeRole(string $roleToSuppr): User
    {
        foreach ($this->roles as $key => $role) {
            if($role == $roleToSuppr) {
                unset($this->roles[$key]);

                return $this;
            }
        }

        return $this;
    }

    /**
     * Return true if user has role
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
     * Erase credentials
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Return true if same user as this
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if(!$user instanceof User) {
            return false;
        }

        if($this->getUsername() != $user->getUsername()) {
            return false;
        }
        return true;
    }

    /**
     * Convert model user to security token user
     * @param SecurityTokenUser $userSecurityToken
     * @return $this
     */
    public function setFromModel(UserModel $modelUser): User
    {
        $this->id = $modelUser->getId();
        $this->setEmail($modelUser->getEmail());
        $this->setPassword($modelUser->getPassword());
        $this->setSalt($modelUser->getSalt());
        $this->setNickname($modelUser->getNickname());
        $this->setEnabled($modelUser->getEnabled());
        $this->setCreatedAt($modelUser->getCreatedAt());
        $this->setUpdatedAt($modelUser->getUpdatedAt());
        $this->setRoles($modelUser->getRoles());

        return $this;
    }

    /**
     * Return true if user is enabled
     * @return bool|void
     */
    public function isEnabled()
    {
        return $this->getEnabled();
    }

    /**
     * Return true if account non expired
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Return true if credentials is not expired
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Return true if account is not locked
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }
}