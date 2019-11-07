<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2018 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Controller;


use Sebk\SmallOrmBundle\Dao\AbstractDao;
use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallUserBundle\Model\User;
use Sebk\SmallUserBundle\Security\UserProvider;
use Sebk\SmallUserBundle\Security\UserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractUserApiController
 * @package Sebk\SmallUserBundle\Controller
 */
abstract class AbstractUserApiController extends Controller
{
    /**
     * Get user DAO
     * @return AbstractDao
     */
    abstract protected function getUserDao(): AbstractDao;

    /**
     * Get user
     * @param int|null $id
     * @param Request $request
     * @return Response
     */
    protected function getUserById(?int $id, Request $request)
    {
        try {
            if ($id === null) {
                $user = $this->getUserDao()->findOneBy(["id" => $this->getUser()->getId()]);
            } else {
                $user = $this->getUserDao()->findOneBy(["id" => $id]);
            }
        } catch (\Exception $e) {
            return new Response("User not found", Response::HTTP_NOT_FOUND);
        }

        try {
            $this->denyAccessUnlessGranted(UserVoter::READ, $user);
        } catch (\Exception $e) {
            return new Response("Not authorized", Response::HTTP_UNAUTHORIZED);
        }

        $response = new Response(json_encode($user));
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }

    /**
     * Update a user
     * @param UserProvider $userProvider
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    protected function putUser(UserProvider $userProvider, Request $request)
    {
        // Decode body
        $userStdClass = json_decode($request->getContent());
        // Security : don't persist salt and password by default
        $userStdClass->salt = User::FIELD_NOT_PERSIST;
        $userStdClass->password = User::FIELD_NOT_PERSIST;
        // Create model
        $userModel = $this->getUserDao()->makeModelFromStdClass($userStdClass);

        $plainPassword = null;
        if (!empty($userModel->getPlainPassword())) {
            if ($userModel->getPlainPassword() != $userModel->getPlainPasswordConfirm()) {
                return new Response("Password and confirmation don't match", Response::HTTP_BAD_REQUEST);
            }
            $plainPassword = $userModel->getPlainPassword();
        }

        try {
            $this->denyAccessUnlessGranted(UserVoter::PERSONAL_EDIT, $userModel);
        } catch (\Exception $e) {
            return new Response("Not authorized ".$e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        $userProvider->updateUserFromModel($userModel, $plainPassword);

        $response = new Response(json_encode($userModel));
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }

    /**
     * Check password valid for current user
     * @param UserProvider $userProvider
     * @param Request $request
     * @return Response
     */
    protected function checkPassword(UserProvider $userProvider, Request $request)
    {
        // decode body
        $password = json_decode($request->getContent())->password;

        // check password
        if($userProvider->checkPassword($this->getUser(), $password)) {
            // password ok
            return new Response("");
        } else {
            // wrong password
            return new Response("", Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * List users
     * @param Request $request
     * @return Response
     */
    protected function listUsers(Request $request) {
        // Get users
        $users = $this->getUserDao()->findBy([]);

        // Hide not allowed users
        foreach ($users as $i => $user) {
            try {
                $this->denyAccessUnlessGranted(UserVoter::READ, $user);
            } catch (\Exception $e) {
                unset($users[$i]);
            }
        }

        // Return response
        return new Response(json_encode(array_values($users)));
    }

    /**
     * Create a user
     * @param UserProvider $userProvider
     * @param Request $request
     * @return Response
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    protected function createUser(UserProvider $userProvider, Request $request) {
        // Check rigths
        /** @var User $myUser */
        $myUser = $this->getUser();
        if(!$myUser->hasRole("ROLE_ADMIN")) {
            return new Response("Access denied", 400);
        }

        // Get data
        $data = json_decode($request->getContent(), true);

        // Create user
        try {
            $userProvider->createUser($data["email"], $data["nickname"], $data["password"]);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 400);
        }

        // Return created user
        $user = $userProvider->getModelByUsername($data["nickname"]);

        return new Response(json_encode($user));
    }
}