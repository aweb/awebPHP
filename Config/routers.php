<?php
/**
 *
 * Created At 11/04/2018 5:09 PM.
 * User: kaiyanh <nzing@aweb.cc>
 */
return $routers = [
    'v1' => [
        ['GET', '/demo/list', 'Demo@getList'],
        ['GET', '/demo/info/{id}', 'Demo@info'],
        ['POST', '/demo/create', 'Demo@create'],
        ['PUT', '/demo/update', 'Demo@update'],
        ['DELETE', '/demo/delete', 'Demo@delete'],
    ]
];