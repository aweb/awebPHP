<?php
/**
 *
 * Created At 23/04/2018 9:33 PM.
 * User: kaiyanh <nzing@aweb.cc>
 */
namespace Core;


use voku\helper\AntiXSS;

class XssClean
{
    protected static $xss_clean;

    public static function getXssClean()
    {
        if (self::$xss_clean == null) {
            self::$xss_clean = new AntiXSS();
        }
        return self::$xss_clean;
    }
}