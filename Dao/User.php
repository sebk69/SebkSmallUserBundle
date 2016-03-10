<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015 - Sébastien Kus
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
        $this->addField("enabled", "enabled", false, static::TYPE_BOOLEAN);
        $this->addField("created_at", "createdAt", null, static::TYPE_DATETIME);
        $this->addField("updated_at", "updatedAt", null, static::TYPE_DATETIME);
        $this->addToMany("roles", array("id" => "idUser"), "UserRole");
    }

    /**
     * @return QueryBuilder
     */
    public function baseQuery() {
        $query = $this->createQueryBuilder("user");

        $query->where()
            ->firstCondition(1, "=", 1);

        return $query;
    }

    /**
     * @param $get
     * @param QueryBuilder|null $query
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilderException
     */
    public function digestGet($get, QueryBuilder $query = null) {
        if($query === null) {
            $query = $this->baseQuery();
        }

        foreach($get as $key => $param) {
            switch($key) {
                case "role":
                    $query->innerJoin("user", "roles");
                    $query->getWhere()
                        ->andCondition($query->getFieldForCondition("role", "roles"), "=", $param);
                    break;
            }
        }

        return $query;
    }
}