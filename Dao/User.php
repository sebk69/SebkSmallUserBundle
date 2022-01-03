<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2017 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Dao;

use Sebk\SmallOrmCore\Dao\AbstractDao;
use Sebk\SmallOrmCore\Dao\Field;

class User extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("user");
        $this->setModelName("User");
        $this->addPrimaryKey("id", "id");
        $this->addField("email", "email", null, Field::TYPE_PHP_FILTER, FILTER_VALIDATE_EMAIL);
        $this->addField("password", "password");
        $this->addField("nickname", "nickname");
        $this->addField("salt", "salt");
        $this->addField("enabled", "enabled", false, Field::TYPE_BOOLEAN);
        $this->addField("created_at", "createdAt", null, Field::TYPE_DATETIME);
        $this->addField("updated_at", "updatedAt", null, Field::TYPE_DATETIME);
        $this->addField("roles", "roles", [], Field::TYPE_JSON);
    }
}