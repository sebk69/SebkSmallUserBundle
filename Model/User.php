<?php

namespace Sebk\SmallUserBundle\Model;

use phpDocumentor\Reflection\Types\Void_;
use Sebk\SmallOrmBundle\Dao\ModelException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Sebk\SmallOrmBundle\Dao\Model;
use Symfony\Component\Validator\Constraints\DateTime;
use Sebk\SmallUserBundle\Security\User as SecurityTokenUser;

/**
 * @method getId()
 * @method setId($value)
 * @method getEmail()
 * @method setEmail($value)
 * @method setPassword($value)
 * @method getNickname()
 * @method setNickname($value)
 * @method setSalt($value)
 * @method getEnabled()
 * @method setEnabled($value)
 * @method getCreatedAt()
 * @method setCreatedAt($value)
 * @method getUpdatedAt()
 * @method setUpdatedAt($value)
 * @method setRoles($value)
 */
class User extends Model implements UserInterface
{
    /**
     * Action after loading model
     */
    public function onLoad(): void
    {
        // Convert database to model fields types
        $this->setRoles(json_decode($this->getRoles()));
    }

    /**
     * Action before saving model
     */
    public function beforeSave(): void
    {
        // Convert model to database fields types
        $this->setRoles(json_encode($this->getRoles()));
        if($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime);
        }
        $this->setUpdatedAt(new \DateTime);
    }

    /**
     * Action after saving model
     */
    public function afterSave(): void
    {
        // Convert database to model fields types
        $this->setRoles(json_decode($this->getRoles()));
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

    public function getRoles()
    {
        return parent::getRoles();
    }

    public function getPassword(): string
    {
        return parent::getPassword();
    }

    public function getSalt(): string
    {
        return parent::getSalt();
    }

    public function getUsername(): string
    {
        return parent::getNickname();
    }

    public function eraseCredentials(): void
    {
        $this->setPassword("");
        $this->setSalt("");
    }

    /**
     * Custom json serialize to convert dates and unset password
     * @return array
     */
    public function jsonSerialize(): array
    {
        $password = $this->getPassword();
        $this->setPassword(Model::FIELD_NOT_PERSIST);
        $this->setSalt(Model::FIELD_NOT_PERSIST);
        $result = parent::jsonSerialize();
        $this->setPassword($password);
        $this->setSalt($password);

        return $result;
    }
}