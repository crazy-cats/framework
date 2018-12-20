<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Timezone {

    static public function zones()
    {
        $zones = [];
        foreach ( ( new \DateTimeZone( 'UTC' ) )->listAbbreviations() as $zoneAreas ) {
            foreach ( $zoneAreas as $zone ) {
                if ( !$zone['timezone_id'] ) {
                    continue;
                }
                $zones[$zone['timezone_id']] = sprintf( '%s ( %s%s )', $zone['timezone_id'], ( $zone['offset'] >= 0 ? '+' : '-' ), date( 'G:i', $zone['offset'] ) );
            }
        }
        asort( $zones );

        return $zones;
    }

}
