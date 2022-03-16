<?php

/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Security;

use Sebk\SmallOrmCore\Dao\DaoEmptyException;
use Sebk\SmallOrmCore\Dao\Model;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Sebk\SmallOrmCore\Factory\Dao;
use Sebk\SmallOrmCore\Factory\Validator;
use Sebk\SmallOrmCore\Dao\DaoException;
use Sebk\SmallUserBundle\Model\User as UserModel;
use Sebk\SmallUserBundle\Dao\User as UserDao;

/**
 * Class UserProvider
 * @package Sebk\SmallUserBundle\Security
 */
class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{

    protected Dao $daoFactory;
    protected Validator $validatorFactory;
    protected UserPasswordHasher $encoderFactory;

    /**
     * UserProvider constructor.
     * @param Dao $daoFactory
     * @param Validator $validatorFactory
     * @param UserPasswordHasherInterface $encoderFactory
     */
    public function __construct(Dao $daoFactory, Validator $validatorFactory, UserPasswordHasherInterface $encoderFactory)
    {
        $this->daoFactory = $daoFactory;
        $this->validatorFactory = $validatorFactory;
        $this->encoderFactory = $encoderFactory;
    }

    public function getUserDao()
    {
        return $this->daoFactory->get("SebkSmallUserBundle", "User");
    }

    /**
     * Load user by email or nickname
     * @param string $username
     * @return User
     * @throws DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     */
    public function loadUserByUsername(string $username): User
    {
        // Get model from db
        $model = $this->getModelByUsername($username);

        // Create security user
        $user = (new User())->setFromModel($model);
        $user->setPassword($model->getPassword());

        if (!$user->getEnabled() && PHP_SAPI != 'cli') {
            throw new AccessDeniedHttpException("Disabled !");
        }

        // Return it
        return $user;
    }

    /**
     * Load user by id
     * @param $userId
     * @param int $userId
     * @return User
     * @throws DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     */
    public function loadUserById(int $userId): User
    {
        // Get model from db
        $model = $this->getModelById($userId);

        // Create security user
        $user = (new User())->setFromModel($model);
        $user->setPassword($model->getPassword());

        // Return it
        return $user;
    }

    /**
     * Get user model by email or nickname
     * @param string $username
     * @return UserModel
     * @throws DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
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
     * Get user model by id
     * @param int $userId
     * @return UserModel
     * @throws DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     */
    public function getModelById(int $userId): UserModel
    {
        try {
            $user = $this->getUserDao()->findOneBy(["id" => $userId]);
        } catch (DaoEmptyException $e) {
            throw new UsernameNotFoundException("User id $userId does not exist.");
        }

        return $user;
    }

    /**
     * Get model by user
     * @param User $user
     * @return UserModel
     * @throws DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     */
    public function getModelByUser(User $user): UserModel
    {
        return $this->getModelByUsername($user->getUsername());
    }

    /**
     * Refresh user
     * @param UserInterface $user
     * @return User
     * @throws DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
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
    public function supportsClass(string $class): bool
    {
        return $class === 'Sebk\SmallUserBundle\Security\User';
    }

    /**
     * Create user
     * @param string $email
     * @param string $nickname
     * @param string $plainPassword
     * @param bool $enabled
     * @return $this
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     */
    public function createUser(string $email, string $nickname, string $plainPassword, bool $enabled = true): UserProvider
    {
        $user = new User;

        $user->setSalt(md5(time()));
        $user->setPassword($this->encoderFactory->hashPassword($user, $plainPassword));
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
     * @throws \Exception
     */
    public function updateUser(User $user, string $plainPassword = null): UserProvider
    {
        if($plainPassword !== null) {
            // Change password
            $user->setPassword($this->encoderFactory->hashPassword($user, $plainPassword));
        } else {
            // Or not persist for security
            $user->setPassword(Model::FIELD_NOT_PERSIST);
            $user->setSalt(Model::FIELD_NOT_PERSIST);
        }

        $model = $this->getModelById($user->getId());
        $model->setFromSecurityTokenUser($user);

        if ($model->getValidator()->validate()) {
            $model->persist();
        } else {
            throw new \Exception($model->getValidator()->getMessage());
        }

        return $this;
    }

    /**
     * Update a user from model
     * @param UserModel $model
     * @param string|null $plainPassword
     * @return UserProvider
     * @throws \Exception
     */
    public function updateUserFromModel(UserModel $model, string $plainPassword = null): UserProvider
    {
        $user = new User;
        $user->setFromModel($model);

        return $this->updateUser($user, $plainPassword);
    }

    /**
     * Check if password match user password
     * @param User $user
     * @param string $plainPassword
     * @return bool
     */
    public function checkPassword(User $user, string $plainPassword): bool
    {
        if (!$user->getEnabled()) {
            return false;
        }

        if($this->encoderFactory->isPasswordValid($user, $plainPassword)) {
            return true;
        }

        return false;
    }
}
