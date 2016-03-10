<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Model;

use Sebk\SmallOrmBundle\Dao\Model;

class UserRole extends Model
{
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_LEADER = "ROLE_LEADER";
    const ROLE_DEVELOPER = "ROLE_DEVELOPER";
    const ROLE_USER = "ROLE_USER";
}