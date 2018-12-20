<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Timezone {

    /**
     * @var \DateTime
     */
    private $datetime;

    public function __construct()
    {
        $this->datetime = new \DateTime;
    }

    /**
     * @param \DateTimeZone $timezone
     * @return $this
     */
    public function setTimezone( $timezone )
    {
        $this->datetime->setTimezone( $timezone );
        return $this;
    }

    /**
     * @param string|null $dateTime
     * @param string $format
     * @return string
     */
    public function getDateTime( $dateTime = null, $format = 'Y-m-d H:i:s' )
    {
        return $this->datetime->setTimestamp( $dateTime !== null ? strtotime( $dateTime ) : time() )->format( $format );
    }

    /**
     * @param string|null $dateTime
     * @param string $format
     * @return string
     */
    public function getUtcDateTime( $dateTime = null, $format = 'Y-m-d H:i:s' )
    {
        return date( $format, ( $dateTime !== null ? strtotime( $dateTime ) : time() ) );
    }

}
