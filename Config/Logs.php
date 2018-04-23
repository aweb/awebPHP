<?php
/**
 * 日志配置文件
 *
 * Created At 21/04/2018 5:09 PM.
 * User: kaiyanh <nzing@aweb.cc>
 */

namespace Config;

class Logs
{
    static public $default = [
        'active'    => 'file',
        'log_level' => 100,
        'file'      => [
            'max_files' => 15,
            'path'      => BASE_ROOT.'/Logs',
        ],
    ];
}