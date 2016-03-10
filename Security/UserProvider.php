<?php

/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallOrmBundle\Factory\Validator;
use Sebk\SmallOrmBundle\Dao\DaoException;
use Sebk\SmallUserBundle\Model\User;

class UserProvider implements UserProviderInterface {

    protected $userDao;
    protected $validatorFactory;
    protected $encoderFactory;

    /**
     * @param Dao $daoFactory
     */
    public function __construct(Dao $daoFactory, Validator $validatorFactory, EncoderFactoryInterface $encoderFactory) {
        $this->userDao = $daoFactory->get("SebkSmallUserBundle", "User");
        $this->validatorFactory = $validatorFactory;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     *
     * @param string $username
     * @return User
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username) {
        try {
            $user = $this->userDao->findOneBy(array("email" => $username));
        } catch (DaoException $e) {
            try {
                $user = $this->userDao->findOneBy(array("nickname" => $username));
            } catch (DaoException $e) {
                throw new UsernameNotFoundException("Username $username does not exist.");
            }
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     * @return type
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user) {
        if (!$user instanceof User) {
            throw new UnsupportedUserException("Instances of " . get_class($user) . " are not supported.");
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class) {
        return $class === 'Sebk\SmallUserBundle\Model\User';
    }

    /**
     * @param string $email
     * @param string $nickname
     * @param string $plainPassword
     * @return User
     */
    public function createUser($email, $nickname, $plainPassword) {
        $user = $this->userDao->newModel();

        $user->setEncoder($this->encoderFactory->getEncoder($user));
        $user->setPasswordToEncode($plainPassword);
        $user->setEmail($email);
        $user->setNickname($nickname);
        $user->setSalt(md5(time()));

        $userValidator = $this->validatorFactory->get($user);
        if ($userValidator->validate()) {
            $this->userDao->persist($user);
        } else {
            throw new \Exception($userValidator->getMessage());
        }

        return $user;
    }

    /**
     * @param User $user
     * @param string $email
     * @param string $nickname
     * @param string $plainPassword
     * @return \Sebk\SmallUserBundle\Security\UserProvider
     * @throws \Exception
     */
    public function updateUser(User $user, $email, $nickname, $plainPassword = null) {
        $user->setEmail($email);
        $user->setNickname($nickname);
        if ($plainPassword !== null) {
            $user->setEncoder($this->encoderFactory->getEncoder($user));
            $user->setPasswordToEncode($plainPassword);
            $user->setSalt(md5(time()));
        }

        // validate and persist
        $validator = $this->validatorFactory->get($user);
        if ($validator->validate()) {
            $this->userDao->persist($user);
        } else {
            throw new \Exception($validator->getMessage());
        }

        return $this;
    }

}
