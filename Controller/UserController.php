<?php

/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Controller;

use Sebk\SmallUserBundle\Model\User;
use Sebk\SmallUserBundle\Model\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sebk\SmallOrmBundle\Dao\ModelException;

/**
 * @route("/users")
 */
class UserController extends Controller {

    /**
     * @Route("/login_check")
     * @method({"POST"})
     */
    public function loginCheckAction() {
        
    }

    /**
     * @Route("/myself")
     * @method({"GET"})
     */
    public function getMyself(Request $request) {
        return new Response(json_encode($this->get('security.token_storage')->getToken()->getUser()), 200);
    }

    /**
     * @route("")
     * @method({"POST"})
     */
    public function postMyself(Request $request) {
        // create model object for new user
        $userStdClass = json_decode($request->getContent());

        $userDao = $this->get("sebk_small_orm_dao")
                ->get("SebkSmallUserBundle", "User");

        $newUser = $userDao->makeModelFromStdClass($userStdClass);

        // check password change
        $passwordToEncode = null;
        if ($newUser->getPassword() != "") {
            try {
            if ($newUser->getPassword() != $newUser->getPasswordConfirm()) {
                return new Response(json_encode("Password and confirmation don't mach"), 400);
            }

            $passwordToEncode = $newUser->getPassword();
            } catch(ModelException $e) {
                return new Response(json_encode("You must type password confirmation"), 400);
            }
        }

        $this->get("sebk_small_users_provider")->updateUser(
                $this->get('security.token_storage')->getToken()->getUser(), 
                $newUser->getEmail(), 
                $newUser->getNickname(), 
                $passwordToEncode
        );

        return new Response(json_encode($newUser), 200);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("")
     * @method({"GET"})
     */
    public function query(Request $request) {
        $userDao = $this->get("sebk_small_orm_dao")
            ->get("SebkSmallUserBundle", "User");

        $query = $userDao->digestGet($request->query->all());

        if($this->get('security.token_storage')->getToken()->getUser()->hasRole(UserRole::ROLE_ADMIN)) {
            return new Response(json_encode($userDao->getResult($query)));
        }

        $result = array();
        foreach($userDao->getResult($query) as $user) {
            $result[] = array("id" => $user->getId(), "email" => $user->getEmail(), "nickname" => $user->getNickname());
        }

        return new Response(json_encode($result));
    }
}
