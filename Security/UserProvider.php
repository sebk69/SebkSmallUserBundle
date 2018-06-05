<?php

/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2018 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Security;

use Sebk\SmallOrmBundle\Dao\DaoEmptyException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallOrmBundle\Factory\Validator;
use Sebk\SmallOrmBundle\Dao\DaoException;
use Sebk\SmallUserBundle\Model\User as UserModel;
use Sebk\SmallUserBundle\Dao\User as UserDao;

/**
 * Class UserProvider
 * @package Sebk\SmallUserBundle\Security
 */
class UserProvider implements UserProviderInterface
{

    protected $daoFactory;
    protected $validatorFactory;
    protected $encoderFactory;

    /**
     * UserProvider constructor.
     * @param Dao $daoFactory
     * @param Validator $validatorFactory
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(Dao $daoFactory, Validator $validatorFactory, EncoderFactoryInterface $encoderFactory) {
        $this->daoFactory = $daoFactory;
        $this->validatorFactory = $validatorFactory;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Return the user dao
     * @return Dao
     */
    public function getUserDao(): UserDao
    {
        return $this->daoFactory->get("SebkSmallUserBundle", "User");
    }

    /**
     * Load user by email or nickname
     * @param string $username
     * @return User
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        return (new User())->setFromModel($this->getModelByUsername($username));
    }

    /**
     * Get user model by email or nickname
     * @param string $username
     * @return UserModel
     */
    public function getModelByUsername(string $username): UserModel
    {
        try {
            $user = $this->getUserDao()->findOneBy(array("email" => $username));
        } catch (DaoEmptyException $e) {
            try {
                $user = $this->getUserDao()->findOneBy(array("nickname" => $username));
            } catch (DaoEmptyException $e) {
                throw new UsernameNotFoundException("User $username does not exist.");
            }
        }

        return $user;
    }

    /**
     * Get model by user
     * @param User $user
     * @return UserModel
     */
    public function getModelByUser(User $user): UserModel
    {
        return $this->getModelByUsername($user->getUsername());
    }

    /**
     * Refresh user
     * @param UserInterface $user
     * @return User
     */
    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException("Instances of " . get_class($user) . " are not supported.");
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Is class is supported by provider
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Sebk\SmallUserBundle\Security\User';
    }

    /**
     * Create user
     * @param $email
     * @param $nickname
     * @param $plainPassword
     * @param $enabled
     * @return UserProvider
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function createUser($email, $nickname, $plainPassword, $enabled = true): UserProvider
    {
        $user = new User;

        $user->setSalt(md5(time()));
        $user->setPassword($this->encoderFactory->getEncoder($user)->encodePassword($plainPassword, $user->getSalt()));
        $user->setEmail($email);
        $user->setNickname($nickname);
        $user->setEnabled($enabled);
        $user->addRole("ROLE_USER");

        $model = $this->daoFactory->get("SebkSmallUserBundle", "User")->newModel();
        $model->setFromSecurityTokenUser($user);

        if ($model->getValidator()->validate()) {
            $model->persist();
        } else {
            throw new \Exception($model->getValidator()->getMessage());
        }

        return $this;
    }


    /**
     * Update user
     * @param User $user
     * @param string|null $plainPassword
     * @return UserProvider
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function updateUser(User $user, string $plainPassword = null): UserProvider
    {
        $user->setPassword($this->encoderFactory->getEncoder($user)->encodePassword($plainPassword, $user->getSalt()));

        $model = $this->daoFactory->get("SebkSmallUserBundle", "User")->newModel();
        $model->setFromSecurityTokenUser($user);

        if ($model->getValidator()->validate()) {
            $model->persist();
        } else {
            throw new \Exception($model->getValidator()->getMessage());
        }

        return $this;
    }
}
