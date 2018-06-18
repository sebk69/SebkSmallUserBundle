<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Security;

use App\Bundle\SmallKeyringModelBundle\Model\Password;
use Sebk\SmallUserBundle\Security\User;
use Sebk\SmallUserBundle\Model\User as UserModel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const PERSONAL_EDIT = "personal_edit";
    const CONTROL = "control";

    /**
     * Check if vote supported
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if(!in_array($attribute,  [static::CONTROL, static::PERSONAL_EDIT])) {
            return false;
        }

        if(!$subject instanceof UserModel) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $loggedUser */
        $loggedUser = $token->getUser();

        switch($attribute) {
            case static::CONTROL:
                if ($loggedUser->hasRole("ROLE_ADMIN")) {
                    return true;
                }
                break;

            case static::PERSONAL_EDIT:
                if ($loggedUser->getId() == $subject->getId()) {
                    return true;
                }
                break;

            default:
                throw new \LogicException("Security failure");
        }

        return false;
    }
}