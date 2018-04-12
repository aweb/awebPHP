<?php
/**
 * Model数据交互层基类
 *
 * Created At 11/04/2018
 * User: kaiyanh  <nzing@aweb.cc>
 */

namespace Model;

/**
 * ModuleBase.
 */
abstract class BaseModel
{
    /**
     *
     * Instances of the derived classes.
     *
     * @var array
     */
    protected static $instances = array();

    /**
     * Get instance of the derived class.
     *
     * @return \Service\BaseService
     */
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className;
        }
        return self::$instances[$className];
    }
}