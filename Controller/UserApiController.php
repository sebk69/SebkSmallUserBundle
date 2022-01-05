<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Controller;


use Sebk\SmallOrmCore\Dao\Model;
use Sebk\SmallOrmCore\Factory\Dao;
use Sebk\SmallOrmForms\Form\FormModel;
use Sebk\SmallOrmForms\Message\Message;
use Sebk\SmallOrmForms\Message\MessageCollection;
use Sebk\SmallUserBundle\Security\User;
use Sebk\SmallUserBundle\Security\UserProvider;
use Sebk\SmallUserBundle\Security\UserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserApiController
 * @package Sebk\SmallUserBundle\Controller
 */
class UserApiController extends AbstractController
{

    /**
     * @Route("/api/login_check", methods={"POST"})
     * @return void
     */
    protected function loginCheck() {}

    /**
     * @Route("/api/users/myself", methods={"GET"})
     * @param Dao $daoFactory
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmCore\Dao\DaoEmptyException
     * @throws \Sebk\SmallOrmCore\Dao\DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     */
    public function getMyself(Dao $daoFactory): Response
    {
        try {
            $model = $daoFactory->get('SebkSmallUserBundle', 'User')->findOneBy(['id' => $this->getUser()->getId()]);
        } catch(\Exception $e) {
            return new Response('Forbidden !', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse($model);
    }

    /**
     * @Route("/api/users/{id}", methods={"GET"})
     * @param int $id
     * @param Dao $daoFactory
     * @return Response|JsonResponse
     */
    public function getUserById(int $id, Dao $daoFactory): Response
    {
        try {
            $user = $daoFactory->get('SebkSmallUserBundle', 'User')->findOneBy(['id' => $id]);
        } catch (\Exception $e) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $this->denyAccessUnlessGranted(UserVoter::READ, $user);
        } catch (\Exception $e) {
            return new Response('Not authorized', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse($user);
    }

    /**
     * @Route("/api/users/{id}", methods={"PATCH"})
     * @param int $id
     * @param UserProvider $userProvider
     * @param Request $request
     * @return Response
     * @throws \Sebk\SmallOrmCore\Dao\DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     * @throws \Sebk\SmallOrmForms\Form\FieldException
     * @throws \Sebk\SmallOrmForms\Form\FieldNotFoundException
     * @throws \Sebk\SmallOrmForms\Type\TypeNotFoundException
     */
    public function patchUser(int $id, UserProvider $userProvider, Request $request): Response
    {
        // Get data
        $data = json_decode($request->getContent(), true);

        // Don't touch to password and salt
        $data['password'] = Model::FIELD_NOT_PERSIST;
        $data['salt'] = Model::FIELD_NOT_PERSIST;

        // Load user
        $model = $userProvider->getModelById($id);

        // Check rights
        try {
            $this->denyAccessUnlessGranted(UserVoter::PERSONAL_EDIT, $model);
        } catch (\Exception $e) {
            return new Response('Not authorized '.$e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        // Fill form
        $form = (new FormModel())
            ->fillFromModel($model)
            ->setFieldMandatory('email')
            ->setFieldMandatory('nickname')
            ->fillFromArray($data);

        // Get updated model
        /** @var \Sebk\SmallUserBundle\Model\User $model */
        $model = $form->fillModel();

        // Check form
        $messages = $form->validate();
        if (count($messages) > 0) {
            return new JsonResponse($messages, Response::HTTP_BAD_REQUEST);
        }

        // Don't persist administratives fields
        $model->setCreatedAt(Model::FIELD_NOT_PERSIST);
        $model->setPassword(Model::FIELD_NOT_PERSIST);
        $model->setSalt(Model::FIELD_NOT_PERSIST);
        $model->setRoles(Model::FIELD_NOT_PERSIST);
        $model->setEnabled(Model::FIELD_NOT_PERSIST);

        // Update user
        $model->persist();

        // Return updated model
        return new JsonResponse($userProvider->getModelById($id));
    }

    /**
     * @Route("/api/users/{id}/password", methods={"PATCH"})
     * @param int $id
     * @param UserProvider $userProvider
     * @param Request $request
     * @return Response
     * @throws \Sebk\SmallOrmCore\Dao\DaoException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     */
    public function editUserPassword(int $id, UserProvider $userProvider, Request $request): Response
    {
        // Get data
        $data = json_decode($request->getContent(), true);

        // Check rights
        $user = $userProvider->getModelById($id);
        try {
            $this->denyAccessUnlessGranted(UserVoter::PERSONAL_EDIT, $user);
        } catch (\Exception $e) {
            return new Response('Not authorized '.$e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        // Init messages
        $messages = new MessageCollection();

        // Check old password
        if (!$userProvider->checkPassword($this->getUser(), $data['oldPassword'])) {
            $messages[] = new Message(Message::BLANK_TEMPLATE, ['The old password is wrong']);
        }

        // Check password length
        if (strlen($data["newPassword"]) < 8) {
            $messages[] = new Message(Message::BLANK_TEMPLATE, ['The password length must more 8 chars or more']);
        }

        if (count($messages) > 0) {
            return new JsonResponse($messages, Response::HTTP_BAD_REQUEST);
        }

        // Update user
        $userProvider->updateUser($this->getUser(), $data['newPassword']);

        return new JsonResponse(true);
    }

    /**
     * @route("/api/users", methods={"GET"})
     * @param Dao $daoFactory
     * @param Request $request
     * @return Response
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmCore\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmCore\Factory\DaoNotFoundException
     */
    public function listUsers(Dao $daoFactory, Request $request) {
        // Get users
        $users = $daoFactory->get('SebkSmallUserBundle', 'User')->findBy([]);

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
     * @Route("/api/users/{id}/enabled/{enabled}", methods={"PATCH"})
     * @param UserProvider $userProvider
     * @return Response
     */
    public function toggleEnabled(int $id, int $enabled, Dao $daoFactory): Response
    {
        /** @var \Sebk\SmallUserBundle\Model\User $user */
        $user = $daoFactory->get("SebkSmallUserBundle", "User")->findOneBy(["id" => $id]);
        $user->setEnabled($enabled == 1);
        $user->persist();

        return new JsonResponse($user);
    }

}