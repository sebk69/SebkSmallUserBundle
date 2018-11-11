<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2018 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Controller;


use Sebk\SmallOrmBundle\Dao\AbstractDao;
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
            return new Response("User not found", 404);
        }

        try {
            $this->denyAccessUnlessGranted(UserVoter::READ, $user);
        } catch (\Exception $e) {
            return new Response("Not authorized", 401);
        }

        $response = new Response(json_encode($user));
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }
}