<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2017 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Controller;


use Sebk\SmallUserBundle\Form\ProfileType;
use Sebk\SmallUserBundle\Model\User;
use Sebk\SmallUserBundle\Security\UserProvider;
use Sebk\SmallUserBundle\Security\UserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class AbstractUserController
 * @package Sebk\SmallUserBundle\Controller
 */
abstract class AbstractUserController extends Controller
{
    /**
     * Create user form
     * @param User $user
     * @return Form
     */
    protected function createProfileForm(User $user): Form
    {
        $user->setPasswordConfirm("");
        $user->setSave(null);
        $form = $this->createForm(ProfileType::class, $user);

        return $form;
    }

    /**
     * Render user form
     * @return mixed
     */
    abstract protected function getProfileEdit(Request $request);

    /**
     * Return route to redirect if user has not authorisation for action
     * @return string
     */
    abstract protected function getAuthFailRoute() : string;

    /**
     * Return route
     * @return string
     */
    abstract protected function getProfileRoute() : string;

    /**
     * Get user model identified by id or logged user if id is null
     * @param mixed $id
     * @return User
     */
    protected function getUserModel($id = null): User
    {
        if($id == null) {
            return $this->get("sebk_small_orm_dao")->get("SebkSmallUserBundle", "User")->findOneBy(["id" => $this->getUser()->getId()]);
        } else {
            return $this->get("sebk_small_orm_dao")->get("SebkSmallUserBundle", "User")->findOneBy(["id" => $id]);
        }
    }

    /**
     * Manage post for edit personal user form
     * @param Request $request
     * @return Response
     */
    public function postProfileEdit(Request $request)
    {
        $userModel = $this->getUserModel();

        // Security
        try {
            $this->denyAccessUnlessGranted(UserVoter::PERSONAL_EDIT, $userModel);
        } catch (AccessDeniedException $e) {
            return $this->redirectToRoute($this->authFailRoute());
        }

        // Create form
        $form = $this->createProfileForm($userModel);

        /** @var UserProvider $userProvider */
        $userProvider = $this->get("sebk_small_user_provider");

        // Handle request
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userModel = $form->getData();
            /** @var \Sebk\SmallUserBundle\Security\User $user */
            $user = $this->getUser();

            // Password check
            $newPassword = null;
            if(!empty($userModel->getPassword()) || !empty($userModel->getPasswordConfirm())) {
                if($userModel->getPassword() != $userModel->getPasswordConfirm()) {
                    $this->addFlash("error", "Password and confirmation don't match");
                    return $this->redirectToRoute($this->getProfileRoute());
                }

                $newPassword = $userModel->getPassword();
            }

            // And persist if valid
            try {
                $user->setFromModel($userModel);
                $userProvider->updateUser($user, $newPassword);
            } catch (\Exception $e) {
                $this->addFlash("error", $e->getMessage());
            }
        }

        // Render
        return $this->redirectToRoute($this->getProfileRoute());
    }
}