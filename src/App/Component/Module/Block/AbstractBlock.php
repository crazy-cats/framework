<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Block;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractBlock extends \CrazyCat\Framework\App\Component\Theme\Block
{
    public function toHtml()
    {
        $profileName = 'Render block: ' . $this->getData('name');

        profile_start($profileName);
        $html = parent::toHtml();
        profile_end($profileName);

        return $html;
    }

    public function __toString()
    {
        return $this->toHtml();
    }
}
