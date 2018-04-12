<?php
/**
 * Framework - Database
 *
 * Created At 12/04/2018.
 * User: kaiyanh <nzing@aweb.cc>
 */

namespace Core;

use Db\Exception;
use Medoo\Medoo;

class Database
{
    // Database Instance
    protected static $instance = array();
    // Write Connects
    protected static $writeConnections = array();
    // Read Connects
    protected static $readConnections = array();

    /**
     * Get instance of the derived class.
     *
     * @return \Core\Database
     */
    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Connection  Database
     *
     * return obj
     */
    public function connection($dbName = 'default', $master = false)
    {
        if ($master) {
            if (!isset(\Config\Database::$write[$dbName])) {
                $this->throwException('Write config of "'.$dbName .'" is not found');
            }
            $config = \Config\Database::$write[$dbName];
        } else {
            if (!isset(\Config\Database::$read[$dbName])) {
                $this->throwException('Read config of "'.$dbName.'" is not found');
            }
            $config = \Config\Database::$read[$dbName];
        }
        $dbObj = new Medoo($config);
        if (!$dbObj) {
            $this->throwException("Connection database of $dbName is failed");
        }
        if ($master) {
            self::$writeConnections[$dbName] = $dbObj;
        } else {
            self::$readConnections[$dbName] = $dbObj;
        }

        return $dbObj;
    }

    /**
     * get read database obj
     *
     * @param string $dbName Database Name
     */
    public function read($dbName = 'default')
    {
        if (empty(self::$readConnections[$dbName]) && !$this->connection($dbName)) {
            $this->throwException('No available read connections');
        }
        return self::$readConnections[$dbName];

    }

    /**
     * get read database obj
     *
     * @param string $dbName Database Name
     */
    public function write($dbName = 'default')
    {
        if (empty(self::$writeConnections[$dbName]) && !$this->connection($dbName, true)) {
            $this->throwException('No available write connections');
        }
        return self::$writeConnections[$dbName];
    }

    /**
     * get read database obj
     *
     * @param string $dbName Database Name
     */
    public function throwException($message = '')
    {
        throw new Exception($message);
    }

}