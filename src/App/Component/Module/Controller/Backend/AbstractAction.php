<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller\Backend;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Component\Module\Controller\AbstractViewAction
{
    public function __construct(
        Context $context,
        \CrazyCat\Framework\App\Io\AbstractRequest $request
    ) {
        parent::__construct($context, $request);
    }
}
