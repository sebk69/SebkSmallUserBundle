<?php

namespace Sebk\SmallUserBundle\Model;

use Sebk\SmallOrmCore\Dao\Model;
use Sebk\SmallUserBundle\Security\User as SecurityTokenUser;

/**
 * @method getId()
 * @method setId($value)
 * @method getEmail()
 * @method setEmail($value)
 * @method getPassword()
 * @method setPassword($value)
 * @method getNickname()
 * @method setNickname($value)
 * @method getSalt()
 * @method setSalt($value)
 * @method getEnabled()
 * @method setEnabled($value)
 * @method getCreatedAt()
 * @method setCreatedAt($value)
 * @method getUpdatedAt()
 * @method setUpdatedAt($value)
 * @method getRoles()
 * @method setRoles($value)
 */
class User extends Model
{
    /**
     * Action before saving model
     */
    public function beforeSave(): void
    {
        if($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime);
        }
        $this->setUpdatedAt(new \DateTime);
    }

    /**
     * Check if user has role
     * @param $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        foreach($this->getRoles() as $userRole) {
            if($role == $userRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set user from security token user
     * @param SecurityTokenUser $securityTokenUser
     * @return User
     */
    public function setFromSecurityTokenUser(SecurityTokenUser $securityTokenUser): User
    {
        $this->setEmail($securityTokenUser->getEmail());
        $this->setPassword($securityTokenUser->getPassword());
        $this->setSalt($securityTokenUser->getSalt());
        $this->setNickname($securityTokenUser->getNickname());
        $this->setEnabled($securityTokenUser->getEnabled());
        $this->setCreatedAt($securityTokenUser->getCreatedAt());
        $this->setUpdatedAt($securityTokenUser->getUpdatedAt());
        $this->setRoles($securityTokenUser->getRoles());

        return $this;
    }

    /**
     * Custom json serialize to convert dates and unset password
     * @return array
     */
    public function jsonSerialize(): array
    {
        $password = $this->getPassword();
        $salt = $this->getSalt();
        $this->setPassword(Model::FIELD_NOT_PERSIST);
        $this->setSalt(Model::FIELD_NOT_PERSIST);
        $result = parent::jsonSerialize();
        $this->setPassword($password);
        $this->setSalt($salt);

        return $result;
    }
}