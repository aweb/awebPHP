<?php
/**
 * Database Config
 *
 * Created At 12/04/2018.
 * User: kaiyanh <nzing@aweb.cc>
 */

namespace Config;

class Database
{
    /**
     * read config
     */
    public static $read = [
        'default' => [
            // required
            'database_type' => 'mysql',
            'database_name' => 'apage',
            'server'        => '10.12.100.53',
            'username'      => 'root',
            'password'      => '123456',
            // [optional]
            'charset'       => 'utf8',
            'port'          => 3306,
            'prefix'        => '',
            'logging'       => true,
        ],
    ];

    /**
     * write config
     */
    public static $write = [
        'default' => [
            // required
            'database_type' => 'mysql',
            'database_name' => 'apage',
            'server'        => '10.12.100.53',
            'username'      => 'root',
            'password'      => '123456',
            // [optional]
            'charset'       => 'utf8',
            'port'          => 3306,
            'prefix'        => '',
            'logging'       => true,
        ],
    ];
}