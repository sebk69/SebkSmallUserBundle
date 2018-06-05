<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2017 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;
use \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilder;

class User extends AbstractDao
{
    public function build()
    {
        $this->setDbTableName("user");
        $this->setModelName("User");
        $this->addPrimaryKey("id", "id");
        $this->addField("email", "email");
        $this->addField("password", "password");
        $this->addField("nickname", "nickname");
        $this->addField("salt", "salt");
        $this->addField("enabled", "enabled", 0);
        $this->addField("created_at", "createdAt", (new \DateTime())->format("Y-m-d H:i:s"));
        $this->addField("updated_at", "updatedAt", null);
        $this->addField("roles", "roles", json_encode([]));
    }
}