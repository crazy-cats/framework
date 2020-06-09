<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Timezone
{
    public function zones()
    {
        $zones = [];
        foreach ((new \DateTimeZone('UTC'))->listAbbreviations() as $zoneAreas) {
            foreach ($zoneAreas as $zone) {
                if (!$zone['timezone_id']) {
                    continue;
                }
                $zones[$zone['timezone_id']] = sprintf(
                    '%s ( %s%s )',
                    $zone['timezone_id'],
                    ($zone['offset'] >= 0 ? '+' : '-'),
                    date('G:i', $zone['offset'])
                );
            }
        }
        asort($zones);

        return $zones;
    }
}
