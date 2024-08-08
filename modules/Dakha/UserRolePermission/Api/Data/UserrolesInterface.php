<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\UserRolePermission\Api\Data;

interface UserrolesInterface
{

    const ALLOWED = 'allowed';
    const USER_ID = 'user_id';
    const USERROLES_ID = 'userroles_id';

    /**
     * Get userroles_id
     * @return string|null
     */
    public function getUserrolesId();

    /**
     * Set userroles_id
     * @param string $userrolesId
     * @return \Dakha\UserRolePermission\Userroles\Api\Data\UserrolesInterface
     */
    public function setUserrolesId($userrolesId);

    /**
     * Get allowed
     * @return string|null
     */
    public function getAllowed();

    /**
     * Set allowed
     * @param string $allowed
     * @return \Dakha\UserRolePermission\Userroles\Api\Data\UserrolesInterface
     */
    public function setAllowed($allowed);

    /**
     * Get user_id
     * @return string|null
     */
    public function getUserId();

    /**
     * Set user_id
     * @param string $userId
     * @return \Dakha\UserRolePermission\Userroles\Api\Data\UserrolesInterface
     */
    public function setUserId($userId);
}

