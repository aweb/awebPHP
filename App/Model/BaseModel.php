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
     * @return \Service\BaseModel
     */
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className;
        }

        return self::$instances[$className];
    }

    /**
     * get read database obj
     *
     * @param string $dbName Database Name
     */
    public function read($dbName = 'default')
    {
        return \Core\Database::instance()->read($dbName);
    }

    /**
     * get read database obj
     *
     * @param string $dbName Database Name
     */
    public function write($dbName = 'default')
    {
        return \Core\Database::instance()->write($dbName);
    }

}