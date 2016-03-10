<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class UserRole extends AbstractDao
{
    public function build()
    {
        $this->setDbTableName("user_role");
        $this->setModelName("UserRole");
        $this->addPrimaryKey("id_user", "idUser");
        $this->addField("role", "role");
        $this->addToOne("user", array("idUser" => "id"), "User");
    }
}