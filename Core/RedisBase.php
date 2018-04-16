<?php
/**
 * Redis Operation Class
 *
 * Created At 13/04/2018.
 * User: kaiyanh <nzing@aweb.cc>
 * base on： https://github.com/phpredis/phpredis
 */

namespace Core;

/**
 * Redis Operation Class
 * 已经屏蔽的函数【flushDb,flushAll,info, keys】
 */
class RedisBase
{
    // redis
    private $redis = null;

    // Expire Time create a new connection
    protected static $expireTime = 0;

    private $dbId = '';

    private $host = '';

    private $port = '';

    // obj instance
    protected static $instance = [];

    private function __construct($dbName = 'default')
    {
        $this->dbName = $dbName;
        // check
        if (!isset(\Config\Redis::$default[$dbName])) {
            $this->throwException("Redis Config $dbName is not exists");
        }
        $config = \Config\Redis::$default[$dbName];
        if (!isset($config['host'])) {
            $this->throwException("Redis Config host is not exists");
        }
        $this->host = $config['host'];
        if (!isset($config['port'])) {
            $this->throwException("Redis Config port is not exists");
        }
        $this->port = $config['port'];
        $config['timeout'] = isset($config['timeout']) ? $config['timeout'] : 10;
        $config['db'] = isset($config['db']) ? $config['db'] : 0;
        $this->dbId = $config['db'];
        $config['retry_interval'] = isset($config['retry_interval']) ? $config['retry_interval'] : 100;
        self::$expireTime = time() + $config['timeout'];
        $this->redis = new \Redis();
        $this->redis->connect($this->host, $this->port, $config['timeout'], null, $config['retry_interval']);
        if (!$this->redis) {
            $this->throwException("connect {$config['host']}:{$config['port']} is failed");
        }
        if (!empty($config['auth'])) {
            $this->redis->auth($config['auth']);
        }
        if ($config['db'] != 0) {
            $this->redis->select($config['db']);
        }
    }

    private function __clone()
    {
        // nothing
    }

    /**
     * Get instance of the derived class.
     *
     * @param string $dbName Config Node
     *
     * @return \Core\Database
     */
    public static function getInstance($dbName = 'default')
    {
        if (isset(self::$instance[$dbName]) && time() < self::$expireTime) {
            return self::$instance[$dbName];
        }

        return self::$instance[$dbName] = new self($dbName);
    }

    /**
     * 执行原生的redis操作[为了安全暂时不开放]
     *
     * @return \Redis
     */
    public function getRedis()
    {
        return false;

        return $this->redis;
    }

    /*************redis管理操作命令*****************/

    /**
     * 测试当前链接是不是已经失效
     * 没有失效返回+PONG
     * 失效返回false
     */
    public function ping()
    {
        return $this->redis->ping();
    }

    /**
     * 选择数据库
     *
     * @param int $dbId 数据库ID号
     *
     * @return bool
     */
    public function select($dbId)
    {
        $this->dbId = $dbId;

        return $this->redis->select($dbId);
    }

    /**
     * bgRewriteAOF
     *
     * @return bool
     */
    public function bgRewriteAOF()
    {
        return $this->redis->bgRewriteAOF();
    }

    /**
     * 同步保存数据到磁盘
     *
     * @return bool
     */
    public function save()
    {
        return $this->redis->save();
    }

    /**
     * 异步保存数据到磁盘
     */
    public function bgSave()
    {
        return $this->redis->bgSave();
    }

    /**
     * 返回最后保存到磁盘的时间
     */
    public function lastSave()
    {
        return $this->redis->lastSave();
    }

    /** 这里不关闭连接，因为session写入会在所有对象销毁之后。
     * public function __destruct()
     * {
     * return $this->redis->close();
     * }
     **/
    /**
     * 返回当前数据库key数量
     */
    public function dbSize()
    {
        return $this->redis->dbSize();
    }

    /**
     * 关闭服务器链接
     *
     * @return bool
     */
    public function close()
    {
        return $this->redis->close();
    }

    /**
     * 关闭所有连接
     */
    public static function closeAll()
    {
        foreach (static::$instance as $o) {
            if ($o instanceof self) {
                $o->close();
            }
        }
    }

    /**
     * 得到当前数据库ID
     *
     * @return int
     */
    public function getDbId()
    {
        return $this->dbId;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    /************* Strings  key  命令*****************/

    /**
     * 设置一个key
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        return $this->redis->set($key, $value);
    }

    /**
     * 得到一个key
     *
     * @param string $key
     *
     * @return string
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * 设置一个有过期时间的key
     *
     * @param string  $key
     * @param integer $expire 单位秒
     * @param mixed   $value
     *
     * @return bool
     */
    public function setEx($key, $expire, $value)
    {
        return $this->redis->setEx($key, $expire, $value);
    }

    /**
     * 设置一个有过期时间的key
     *
     * @param string  $key
     * @param integer $expire 单位-毫秒
     * @param mixed   $value
     *
     * @return bool
     */
    public function pSetEx($key, $expire, $value)
    {
        return $this->redis->pSetEx($key, $expire, $value);
    }


    /**
     * 设置一个key,如果key存在,不做任何操作.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function setNx($key, $value)
    {
        return $this->redis->setNx($key, $value);
    }

    /**
     * 删除指定key
     *
     * @param string|array $key
     *
     * @return integer
     */
    public function del($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 判断一个key值是不是存在
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * 值 +1
     *
     * @param string $key
     *
     * @return bool
     */
    public function incr($key)
    {
        return $this->redis->incr($key);
    }

    /**
     * 值 +n
     *
     * @param string  $key
     * @param integer $n
     *
     * @return bool
     */
    public function incrBy($key, $n)
    {
        return $this->redis->incrBy($key, $n);
    }

    /**
     * 值 -1
     *
     * @param string $key
     *
     * @return bool
     */
    public function decr($key)
    {
        return $this->redis->decr($key);
    }

    /**
     * 值 -n
     *
     * @param string  $key
     * @param integer $n
     *
     * @return bool
     */
    public function decrBy($key, $n)
    {
        return $this->redis->decrBy($key, $n);
    }

    /**
     * 值 浮点类型 +n
     *
     * @param string $key
     * @param float  $n
     *
     * @return bool
     */
    public function incrByFloat($key, $n)
    {
        return $this->redis->incrByFloat($key, $n);
    }

    /**
     * 获取多个key值
     *
     * @param string $keys
     *
     * @return array
     */
    public function mGet($keys)
    {
        return $this->redis->mGet($keys);
    }

    /**
     * 设置多个key->value值
     *
     * @param array $kv
     *
     * @return bool
     */
    public function mSet($kv)
    {
        return $this->redis->mSet($kv);
    }

    /**
     * 获取多个key值
     *
     * @param string $keys
     * @param mixed  $value
     *
     * @return array
     */
    public function getSet($keys, $value)
    {
        return $this->redis->getSet($keys, $value);
    }

    /**
     * 返回一个随机key
     *
     * @return string
     */
    public function randomKey()
    {
        return $this->redis->randomKey();
    }

    /**
     * 移动到其他db
     *
     * @param string  $key
     * @param integer $db
     *
     * @return string
     */
    public function move($key, $db)
    {
        return $this->redis->move($key, $db);
    }

    /**
     * 重命名key
     *
     * @param string  $key
     * @param integer $value
     *
     * @return bools
     */
    public function rename($key, $value)
    {
        return $this->redis->rename($key, $value);
    }

    /**
     * 为一个key设定过期时间 单位为秒
     *
     * @param string  $key
     * @param integer $expire
     *
     * @return bool
     */
    public function expire($key, $expire)
    {
        return $this->redis->expire($key, $expire);
    }


    /**
     * 设定一个key什么时候过期，time为一个时间戳
     *
     * @param string  $key
     * @param integer $timestamp 时间戳
     *
     * @return bool
     */
    public function expireAt($key, $time)
    {
        return $this->redis->expireAt($key, $time);
    }

    /**
     * 设定一个key什么时候过期，time为一个时间戳
     *
     * @param string $key
     *
     * @return mixed
     * string: Redis::REDIS_STRING
     * set: Redis::REDIS_SET
     * list: Redis::REDIS_LIST
     * zset: Redis::REDIS_ZSET
     * hash: Redis::REDIS_HASH
     * other: Redis::REDIS_NOT_FOUND
     */
    public function type($key)
    {
        return $this->redis->type($key);
    }


    /**
     * 字符串追加
     *
     * @param string $key   Key.
     * @param string $value Value.
     *
     * @return string
     */
    public function append($key, $value)
    {
        return $this->redis->append($key, $value);
    }

    /**
     * 获取一定范围的字符串
     *
     * @param string  $key   Key.
     * @param integer $start .
     * @param integer $end
     *
     * @return string
     */
    public function getRange($key, $start, $end)
    {
        return $this->redis->getRange($key, $start, $end);
    }

    /**
     * 指定位置替换的字符串
     *
     * @param string  $key    Key.
     * @param integer $offset key offset value
     * @param string  $string
     *
     * @return string
     */
    public function setRange($key, $offset, $string)
    {
        return $this->redis->setRange($key, $offset, $string);
    }

    /**
     * 指定位置替换的字符串
     *
     * @param string $key Key.
     *
     * @return string
     */
    public function strLen($key)
    {
        return $this->redis->strLen($key);
    }

    /**
     * 对列表排序
     *
     * @param string $key Key.
     * @param array  $sortCond
     *
     * @return string
     */
    public function sort($key, $sortCond = [])
    {
        return $this->redis->sort($key, $sortCond);
    }

    /**
     * 返回一个key还有多久过期，单位秒
     *
     * @param string $key
     *
     * @return integer
     */
    public function ttl($key)
    {
        return $this->redis->ttl($key);
    }

    /**
     * 返回一个key还有多久过期，单位毫秒
     *
     * @param string $key
     *
     * @return integer
     */
    public function pttl($key)
    {
        return $this->redis->pttl($key);
    }

    /**
     * 删除过期时间
     *
     * @param string $key
     *
     * @return bool
     */
    public function persist($key)
    {
        return $this->redis->persist($key);
    }


    /*****************hash表操作函数*******************/


    /**
     * 设置hash表中一个字段的值
     *
     * @param string $key   缓存key
     * @param string $field 字段
     *
     * @return string|false
     */
    public function hGet($key, $field)
    {
        return $this->redis->hGet($key, $field);
    }

    /**
     * 为hash表设定一个字段的值
     *
     * @param string $key   缓存key
     * @param string $field 字段
     * @param string $value 值。
     *
     * @return bool
     */
    public function hSet($key, $field, $value)
    {
        return $this->redis->hSet($key, $field, $value);
    }

    /**
     * 为hash表设定一个字段的值,如果字段存在，返回false
     *
     * @param string $key   缓存key
     * @param string $field 字段
     * @param string $value 值。
     *
     * @return bool
     */
    public function hSetNx($key, $field, $value)
    {
        return $this->redis->hSetNx($key, $field, $value);
    }

    /**
     * 返回hash表元素个数
     *
     * @param string $key 缓存key
     *
     * @return int|bool
     */
    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    /**
     * 删除hash表中指定字段 ,支持批量删除
     *
     * @param string $key   缓存key
     * @param string $field 字段
     *
     * @return int
     */
    public function hDel($key, $field)
    {
        $fieldArr = explode(',', $field);
        $delNum = 0;

        foreach ($fieldArr as $row) {
            $row = trim($row);
            $delNum += $this->redis->hDel($key, $row);
        }

        return $delNum;
    }

    /**
     * 返回所有hash表的所有字段
     *
     * @param string $key
     *
     * @return array|bool
     */
    public function hKeys($key)
    {
        return $this->redis->hKeys($key);
    }

    /**
     * 返回所有hash表的字段值，为一个索引数组
     *
     * @param string $key
     *
     * @return array|bool
     */
    public function hVals($key)
    {
        return $this->redis->hVals($key);
    }

    /**
     * 返回所有hash表的字段值，为一个关联数组
     *
     * @param string $key
     *
     * @return array|bool
     */
    public function hGetAll($key)
    {
        return $this->redis->hGetAll($key);
    }

    /**
     * 判断hash表中，指定field是不是存在
     *
     * @param string $key   缓存key
     * @param string $field 字段
     *
     * @return bool
     */
    public function hExists($key, $field)
    {
        return $this->redis->hExists($key, $field);
    }

    /**
     * 为hash表设置累加，可以负数
     *
     * @param string $key
     * @param int    $field
     * @param string $value
     *
     * @return bool
     */
    public function hIncrBy($key, $field, $value)
    {
        $value = intval($value);

        return $this->redis->hIncrBy($key, $field, $value);
    }

    /**
     * 为hash表float设置累加，可以负数
     *
     * @param string $key
     * @param int    $field
     * @param string $value
     *
     * @return bool
     */
    public function hIncrByFloat($key, $field, $value)
    {
        return $this->redis->hIncrByFloat($key, $field, $value);
    }

    /**
     * 为hash表多个字段设定值。
     *
     * @param string $key
     * @param array  $value
     *
     * @return array|bool
     */
    public function hMset($key, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        return $this->redis->hMset($key, $value);
    }

    /**
     * 为hash表多个字段设定值。
     *
     * @param string       $key
     * @param array|string $value string以','号分隔字段
     *
     * @return array|bool
     */
    public function hMget($key, $field)
    {
        if (!is_array($field)) {
            $field = explode(',', $field);
        }

        return $this->redis->hMget($key, $field);
    }

    /*********************Lists有序集合操作*********************/

    /**
     * 给当前集合添加一个元素
     * 如果value已经存在，会更新order的值。
     *
     * @param string $key
     * @param string $order 序号
     * @param string $value 值
     *
     * @return bool
     */
    public function zAdd($key, $order, $value)
    {
        return $this->redis->zAdd($key, $order, $value);
    }

    /**
     * 给$value成员的order值，增加$num,可以为负数
     *
     * @param string $key
     * @param string $num   序号
     * @param string $value 值
     *
     * @return 返回新的order
     */
    public function zinCry($key, $num, $value)
    {
        return $this->redis->zinCry($key, $num, $value);
    }

    /**
     * 删除值为value的元素
     *
     * @param string $key
     * @param stirng $value
     *
     * @return bool
     */
    public function zRem($key, $value)
    {
        return $this->redis->zRem($key, $value);
    }

    /**
     * 集合以order递增排列后，0表示第一个元素，-1表示最后一个元素
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @return array|bool
     */
    public function zRange($key, $start, $end)
    {
        return $this->redis->zRange($key, $start, $end);
    }

    /**
     * 集合以order递减排列后，0表示第一个元素，-1表示最后一个元素
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @return array|bool
     */
    public function zRevRange($key, $start, $end)
    {
        return $this->redis->zRevRange($key, $start, $end);
    }

    /**
     * 集合以order递增排列后，返回指定order之间的元素。
     * min和max可以是-inf和+inf　表示最大值，最小值
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @package array $option 参数
     *     withscores=>true，表示数组下标为Order值，默认返回索引数组
     *     limit=>array(0,1) 表示从0开始，取一条记录。
     * @return array|bool
     */
    public function zRangeByScore($key, $start = '-inf', $end = "+inf", $option = array())
    {
        return $this->redis->zRangeByScore($key, $start, $end, $option);
    }

    /**
     * 集合以order递减排列后，返回指定order之间的元素。
     * min和max可以是-inf和+inf　表示最大值，最小值
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @package array $option 参数
     *     withscores=>true，表示数组下标为Order值，默认返回索引数组
     *     limit=>array(0,1) 表示从0开始，取一条记录。
     * @return array|bool
     */
    public function zRevRangeByScore($key, $start = '-inf', $end = "+inf", $option = array())
    {
        return $this->redis->zRevRangeByScore($key, $start, $end, $option);
    }

    /**
     * 返回order值在start end之间的数量
     *
     * @param unknown $key
     * @param unknown $start
     * @param unknown $end
     */
    public function zCount($key, $start, $end)
    {
        return $this->redis->zCount($key, $start, $end);
    }

    /**
     * 返回值为value的order值
     *
     * @param unknown $key
     * @param unknown $value
     */
    public function zScore($key, $value)
    {
        return $this->redis->zScore($key, $value);
    }

    /**
     * 返回集合以score递增加排序后，指定成员的排序号，从0开始。
     *
     * @param unknown $key
     * @param unknown $value
     */
    public function zRank($key, $value)
    {
        return $this->redis->zRank($key, $value);
    }

    /**
     * 返回集合以score递增加排序后，指定成员的排序号，从0开始。
     *
     * @param unknown $key
     * @param unknown $value
     */
    public function zRevRank($key, $value)
    {
        return $this->redis->zRevRank($key, $value);
    }

    /**
     * 删除集合中，score值在start end之间的元素　包括start end
     * min和max可以是-inf和+inf　表示最大值，最小值
     *
     * @param unknown $key
     * @param unknown $start
     * @param unknown $end
     *
     * @return 删除成员的数量。
     */
    public function zRemRangeByScore($key, $start, $end)
    {
        return $this->redis->zRemRangeByScore($key, $start, $end);
    }

    /**
     * 返回集合元素个数。
     *
     * @param unknown $key
     */
    public function zCard($key)
    {
        return $this->redis->zCard($key);
    }
    /*********************队列操作命令************************/

    /**
     * 在队列尾部插入一个元素
     *
     * @param unknown $key
     * @param unknown $value
     * 返回队列长度
     */
    public function rPush($key, $value)
    {
        return $this->redis->rPush($key, $value);
    }

    /**
     * 在队列尾部插入一个元素 如果key不存在，什么也不做
     *
     * @param unknown $key
     * @param unknown $value
     * 返回队列长度
     */
    public function rPushx($key, $value)
    {
        return $this->redis->rPushx($key, $value);
    }

    /**
     * 在队列头部插入一个元素
     *
     * @param unknown $key
     * @param unknown $value
     * 返回队列长度
     */
    public function lPush($key, $value)
    {
        return $this->redis->lPush($key, $value);
    }

    /**
     * 在队列头插入一个元素 如果key不存在，什么也不做
     *
     * @param unknown $key
     * @param unknown $value
     * 返回队列长度
     */
    public function lPushx($key, $value)
    {
        return $this->redis->lPushx($key, $value);
    }

    /**
     * 返回队列长度
     *
     * @param unknown $key
     */
    public function lLen($key)
    {
        return $this->redis->lLen($key);
    }

    /**
     * 返回队列指定区间的元素
     *
     * @param unknown $key
     * @param unknown $start
     * @param unknown $end
     */
    public function lRange($key, $start, $end)
    {
        return $this->redis->lrange($key, $start, $end);
    }

    /**
     * 返回队列中指定索引的元素
     *
     * @param unknown $key
     * @param unknown $index
     */
    public function lIndex($key, $index)
    {
        return $this->redis->lIndex($key, $index);
    }

    /**
     * 设定队列中指定index的值。
     *
     * @param unknown $key
     * @param unknown $index
     * @param unknown $value
     */
    public function lSet($key, $index, $value)
    {
        return $this->redis->lSet($key, $index, $value);
    }

    /**
     * 删除值为vaule的count个元素
     * PHP-REDIS扩展的数据顺序与命令的顺序不太一样，不知道是不是bug
     * count>0 从尾部开始
     *  >0　从头部开始
     *  =0　删除全部
     *
     * @param unknown $key
     * @param unknown $count
     * @param unknown $value
     */
    public function lRem($key, $count, $value)
    {
        return $this->redis->lRem($key, $value, $count);
    }

    /**
     * 删除并返回队列中的头元素。
     *
     * @param unknown $key
     */
    public function lPop($key)
    {
        return $this->redis->lPop($key);
    }

    /**
     * 删除并返回队列中的尾元素
     *
     * @param unknown $key
     */
    public function rPop($key)
    {
        return $this->redis->rPop($key);
    }

    /*************redis　无序集合操作命令*****************/

    /**
     * 返回集合中所有元素
     *
     * @param unknown $key
     */
    public function sMembers($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * 求2个集合的差集
     *
     * @param unknown $key1
     * @param unknown $key2
     */
    public function sDiff($key1, $key2)
    {
        return $this->redis->sDiff($key1, $key2);
    }

    /**
     * 添加集合。由于版本问题，扩展不支持批量添加。这里做了封装
     *
     * @param unknown      $key
     * @param string|array $value
     */
    public function sAdd($key, $value)
    {
        if (!is_array($value)) {
            $arr = array($value);
        } else {
            $arr = $value;
        }
        foreach ($arr as $row) {
            $this->redis->sAdd($key, $row);
        }
    }

    /**
     * 返回无序集合的元素个数
     *
     * @param unknown $key
     */
    public function scard($key)
    {
        return $this->redis->scard($key);
    }

    /**
     * 从集合中删除一个元素
     *
     * @param unknown $key
     * @param unknown $value
     */
    public function srem($key, $value)
    {
        return $this->redis->srem($key, $value);
    }


    /*********************事务的相关方法************************/

    /**
     * 监控key,就是一个或多个key添加一个乐观锁
     * 在此期间如果key的值如果发生的改变，刚不能为key设定值
     * 可以重新取得Key的值。
     *
     * @param unknown $key
     */
    public function watch($key)
    {
        return $this->redis->watch($key);
    }

    /**
     * 取消当前链接对所有key的watch
     *  EXEC 命令或 DISCARD 命令先被执行了的话，那么就不需要再执行 UNWATCH 了
     */
    public function unwatch()
    {
        return $this->redis->unwatch();
    }

    /**
     * 开启一个事务
     * 事务的调用有两种模式Redis::MULTI和Redis::PIPELINE，
     * 默认是Redis::MULTI模式，
     * Redis::PIPELINE管道模式速度更快，但没有任何保证原子性有可能造成数据的丢失
     */
    public function multi($type = \Redis::MULTI)
    {
        return $this->redis->multi($type);
    }

    /**
     * 执行一个事务
     * 收到 EXEC 命令后进入事务执行，事务中任意命令执行失败，其余的命令依然被执行
     */
    public function exec()
    {
        return $this->redis->exec();
    }

    /**
     * 回滚一个事务
     */
    public function discard()
    {
        return $this->redis->discard();
    }

    public function auth($auth)
    {
        return $this->redis->auth($auth);
    }
    /*********************自定义的方法,用于简化操作************************/

    /**
     * 得到一组的ID号
     *
     * @param unknown $prefix
     * @param unknown $ids
     */
    public function hashAll($prefix, $ids)
    {
        if ($ids == false) {
            return false;
        }
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $arr = array();
        foreach ($ids as $id) {
            $key = $prefix.'.'.$id;
            $res = $this->hGetAll($key);
            if ($res != false) {
                $arr[] = $res;
            }
        }

        return $arr;
    }

    /**
     * 生成一条消息，放在redis数据库中。使用0号库。
     *
     * @param string|array $msg
     */
    public function pushMessage($lkey, $msg)
    {
        if (is_array($msg)) {
            $msg = json_encode($msg);
        }
        $key = md5($msg);

        //如果消息已经存在，删除旧消息，已当前消息为准
        //echo $n=$this->lRem($lkey, 0, $key)."\n";
        //重新设置新消息
        $this->lPush($lkey, $key);
        $this->setex($key, 3600, $msg);

        return $key;
    }


    /**
     * 得到条批量删除key的命令
     *
     * @param unknown $keys
     * @param unknown $dbId
     */
    public function delKeys($keys, $dbId)
    {
        $redisInfo = $this->getConnInfo();
        $cmdArr = array(
            'redis-cli',
            '-a',
            $redisInfo['auth'],
            '-h',
            $redisInfo['host'],
            '-p',
            $redisInfo['port'],
            '-n',
            $dbId,
        );
        $redisStr = implode(' ', $cmdArr);
        $cmd = "{$redisStr} KEYS \"{$keys}\" | xargs {$redisStr} del";

        return $cmd;
    }

    /**
     * throws Exception
     *
     * @param string $message Database Name
     *
     * @throws \RedisException
     */
    public function throwException($message = '')
    {
        throw new \RedisException($message);
    }

}