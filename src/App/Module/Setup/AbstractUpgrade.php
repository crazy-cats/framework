<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Model;

use CrazyCat\Framework\App\Db\Manager as DbManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractUpgrade {

    /**
     * @var \CrazyCat\Framework\App\Db\AbstractAdapter
     */
    protected $conn;

    public function __construct( DbManager $dbManager )
    {
        $this->conn = $dbManager->getConnection();
    }

    /**
     * @return void
     */
    abstract public function execute();
}
