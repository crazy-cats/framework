<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Setup;

use CrazyCat\Framework\App\Db\Manager as DbManager;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractSetup
{
    /**
     * @var \CrazyCat\Framework\App\Db\AbstractAdapter
     */
    protected $conn;

    public function __construct(DbManager $dbManager)
    {
        $this->conn = $dbManager->getConnection();
    }

    /**
     * @return void
     */
    abstract public function execute();
}
