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
    public function getUserById(?int $id, Request $request)
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
    public function putUser(UserProvider $userProvider, Request $request)
    {
        // Decode body
        $userStdClass = json_decode($request->getContent());
        // Security : don't persist salt and password by default
        $userStdClass->salt = User::FIELD_NOT_PERSIST;
        $userStdClass->password = User::FIELD_NOT_PERSIST;
        // Convert dates to DateTime
        $userStdClass->createdAt = \DateTime::createFromFormat("Y-m-d H:i:s", $userStdClass->createdAt);
        $userStdClass->updatedAt = \DateTime::createFromFormat("Y-m-d H:i:s", $userStdClass->updatedAt);
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
    public function checkPassword(UserProvider $userProvider, Request $request)
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
}