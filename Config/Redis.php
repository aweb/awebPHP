<?php
/**
 * Redis Config
 *
 * Created At 12/04/2018.
 * User: kaiyanh <nzing@aweb.cc>
 */

namespace Config;

class Redis
{
    /**
     * default redis
     */
    public static $default = [
        'default' => [
            'host'           => '127.0.0.1',
            'port'           => 6379,
            'db'             => 0,
            'password'       => '',
            'timeout'        => 10, // float, value in seconds
            'retry_interval' => 100, // integer, value in milliseconds
        ],
    ];
}