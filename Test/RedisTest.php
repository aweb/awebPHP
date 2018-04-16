<?php
/**
 *
 * Created At 14/04/2018 6:18 PM.
 * User: kaiyanh <nzing@aweb.cc>
 */
// 引入自动加载
require "../Vendor/autoload.php";
use PHPUnit\Framework\TestCase;

class RedisTest extends TestCase
{
    private $redis = null;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->redis = \Core\RedisBase::getInstance('default');
    }

    public function testPing()
    {
        $res = $this->redis->ping();
        $this->assertEquals($res, '+PONG');
    }

    public function testSelect()
    {
        $res = $this->redis->select(1);
        $this->assertEquals($res, true);
    }

    public function testSave()
    {
        $res = $this->redis->save();
        $this->assertEquals($res, true);
    }

    public function testBgSave()
    {
        $res = $this->redis->bgSave();
        $this->assertEquals($res, true);
    }

    public function testLastSave()
    {
        $res = $this->redis->lastSave();
        $this->assertLessThan($res, 1);
    }

    public function testBgRewriteAOF()
    {
        $res = $this->redis->bgRewriteAOF();
        $this->assertEquals($res, true);
    }

    /****************************** string && keys *********************************************/
    public function testSet()
    {
        $res = $this->redis->set('key', 'value1');
        $this->assertEquals($res, true);
    }

    public function testGet()
    {
        $this->redis->set('key', 'value1');
        $res = $this->redis->get('key');
        $this->assertEquals($res, 'value1');
    }

    public function testSetEx()
    {
        $res = $this->redis->setEx('key', 10, 'value1');
        $this->assertEquals($res, true);
    }

    public function testPSetEx()
    {
        $res = $this->redis->pSetEx('key', 1000, 'value1');
        $this->assertEquals($res, true);
    }

    public function testSetNx()
    {
        $this->redis->del('key');
        $res = $this->redis->setNx('key', 12);
        $this->assertEquals($res, true);
        $res = $this->redis->setNx('key', 12);
        $this->assertEquals($res, false);
    }

    public function testDel()
    {
        $redis = $this->redis;
        $redis->set('key1', 'val1');
        $redis->set('key2', 'val2');
        $redis->set('key3', 'val3');

        $res = $redis->del(['key1', 'key2']);
        $this->assertEquals($res, 2);
        $res = $redis->del('key3');
        $this->assertEquals($res, 1);
    }

    public function testExists()
    {
        $redis = $this->redis;
        $redis->set('key', 'value');
        $res = $redis->exists('key');
        $this->assertEquals($res, true);
        $res = $redis->exists('NonExistingKey');
        $this->assertEquals($res, false);
    }

    public function testIncr()
    {
        $redis = $this->redis;
        $redis->del('key');
        $res = $redis->incr('key');
        $this->assertEquals($res, 1);
        $res = $redis->incr('key');
        $this->assertEquals($res, 2);
        $res = $redis->incrBy('key', 10);
        $this->assertEquals($res, 12);
        $res = $redis->decr('key');
        $this->assertEquals($res, 11);
        $res = $redis->decrBy('key', 10);
        $this->assertEquals($res, 1);
    }

    public function testIncrByFloat()
    {
        $redis = $this->redis;
        $redis->del('key');
        $res = $redis->incrByFloat('key', 1.5);
        $this->assertEquals($res, 1.5);
        $res = $redis->incrByFloat('key', 1.5);
        $this->assertEquals($res, 3);
    }

    public function testMGet()
    {
        $redis = $this->redis;
        $redis->set('key1', 'value1');
        $redis->set('key2', 'value2');
        $redis->set('key3', 'value3');
        $res = $redis->mGet(array('key1', 'key2', 'key3'));
        $res1 = $redis->mGet(array('key0', 'key1', 'key5'));
        $this->assertEquals($res, array("value1", "value2", "value3"));
        $this->assertEquals($res1, array(false, "value1", false));
    }

    public function testMSet()
    {
        $redis = $this->redis;
        $redis->del(['key1', 'key2']);
        $arr = [
            'key1' => "value1",
            "key2" => "value2",
        ];
        $res = $redis->mSet($arr);
        $this->assertEquals($res, true);
        $res = $redis->mGet(['key1', 'key2']);
        $this->assertEquals($res, array("value1", "value2"));
    }

    public function testGetSet()
    {
        $this->redis->set('key', 'hello');
        $res = $this->redis->getSet('key', 'world');
        $res1 = $this->redis->get('key');
        $this->assertEquals($res, "hello");
        $this->assertEquals($res1, "world");
    }

    public function testRandomKey()
    {
        $key = $this->redis->randomKey();
        $this->redis->set($key, "hello");
        $res1 = $this->redis->get($key);
        $this->assertNotEmpty($key);
        $this->assertNotEmpty($res1);
        $this->redis->del($key);
    }

    public function testMove()
    {
        $redis = $this->redis;
        $redis->del('key');
        $redis->select(0);    // switch to DB 0
        $res = $redis->set('key', 42);    // write 42 to x
        $redis->move('key', 1);    // move to DB 1
        $redis->select(1);    // switch to DB 1
        $res1 = $redis->get('key');    // will return 42
        $this->assertEquals($res, 42);
        $this->assertEquals($res1, 42);
    }

    public function testRename()
    {
        $redis = $this->redis;
        $redis->set('x', '42');
        $redis->rename('x', 'y');
        $res = $redis->get('y');    // → 42
        $res1 = $redis->get('x');    // → `FALSE`
        $this->assertEquals($res, 42);
        $this->assertEquals($res1, false);
    }

    public function testExpire()
    {
        $redis = $this->redis;
        $redis->set('x', '42');
        $now = time(); // current timestamp
        $res = $redis->expireAt('x', $now + 1);    // x will disappear in 3 seconds.
        sleep(1.5);                // wait 5 seconds
        $res1 = $redis->get('x');        // will return `FALSE`, as 'x' has expired.
        $this->assertEquals($res, true);
        $this->assertEquals($res1, false);

        $redis->set('x', '42');
        $res = $redis->expire('x', 0.4);
        sleep(0.5);
        $res1 = $redis->get('x');
        $this->assertEquals($res, true);
        $this->assertEquals($res1, false);
    }

    public function testType()
    {
        /**
         * const REDIS_NOT_FOUND       = 0;
         * const REDIS_STRING          = 1;
         * const REDIS_SET             = 2;
         * const REDIS_LIST            = 3;
         * const REDIS_ZSET            = 4;
         * const REDIS_HASH            = 5;
         */
        $redis = $this->redis;
        $redis->set('key', 'hello');
        $res = $redis->type('key');
        $this->assertEquals($res, 1);
    }


    public function testAppend()
    {
        $this->redis->set('key', 'value1');
        $this->redis->append('key', 'value2');
        $res = $this->redis->get('key');
        $this->assertEquals($res, "value1value2");
    }

    public function testGetRange()
    {
        $this->redis->set('key', 'string value');
        $res = $this->redis->getRange('key', 0, 5);
        $this->assertEquals($res, "string");
    }

    public function testSetRange()
    {
        $this->redis->set('key', 'hello world ben');
        $this->redis->setRange('key', 6, "redis");
        $res = $this->redis->get('key');
        $this->assertEquals($res, "hello redis ben");
    }

    public function testStrLen()
    {
        $this->redis->set('key', 'hello');
        $res = $this->redis->strLen('key');
        $this->assertEquals($res, 5);
    }

    public function testSort()
    {
        $redis = $this->redis;
        $redis->del('s');
        $redis->sAdd('s', 5);
        $redis->sAdd('s', 4);
        $redis->sAdd('s', 2);
        $redis->sAdd('s', 1);
        $redis->sAdd('s', 3);

//    'by' => 'some_pattern_*',
//    'limit' => array(0, 1),
//    'get' => 'some_other_pattern_*' or an array of patterns,
//    'sort' => 'asc' or 'desc',
//    'alpha' => TRUE,
//    'store' => 'external-key'
        $res = $redis->sort('s');
        $this->assertEquals($res, ['1', '2', '3', '4', '5']);
        $res = $redis->sort('s', ['sort' => 'desc']);
        $this->assertEquals($res, ['5', '4', '3', '2', '1']);
    }

    public function testTtl()
    {
        $this->redis->setEx('key', 300, 'hello');
        $res = $this->redis->ttl('key');
        $this->assertLessThan($res, 1);
    }

    public function testPttl()
    {
        $this->redis->setEx('key', 300, 'hello');
        $res = $this->redis->pttl('key');
        $this->assertLessThan($res, 1);
    }

    public function testPersist()
    {
        $this->redis->setEx('key', 300, 'hello');
        $this->redis->persist('key');
        $res = $this->redis->ttl('key');
        $this->assertEquals($res, -1);
    }

    public function testHSet()
    {
        $redis = $this->redis;
        $redis->del('h');
        $res = $redis->hSet('h', 'key1', 'hello');
        $this->assertEquals($res, 1);
        $res = $redis->hSet('h', 'key1', 'plop');
        $this->assertEquals($res, 0);
    }

    public function testHSetNx()
    {
        $redis = $this->redis;
        $redis->del('h');
        $res = $redis->hSetNx('h', 'key1', 'hello');
        $this->assertEquals($res, 1);
        $res = $redis->hSetNx('h', 'key1', 'plop');
        $this->assertEquals($res, false);
    }

    public function testHGet()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hSet('h', 'key1', 'hello');
        $res = $redis->hGet('h', 'key1');
        $this->assertEquals($res, "hello");
    }

    public function testHLen()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hSet('h', 'key1', 'hello');
        $res = $redis->hLen('h', 'key1');
        $this->assertEquals($res, 1);
    }

    public function testHDel()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hSet('h', 'key1', 'hello');
        $redis->hSet('h', 'key2', 'hello2');
        $res = $redis->hDel('h', 'key1,key2');
        $this->assertEquals($res, 2);
    }

    public function testHKeys()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hSet('h', 'key1', 'hello');
        $redis->hSet('h', 'key2', 'hello2');
        $res = $redis->hKeys('h');
        $this->assertEquals($res, ['key1','key2']);
    }

    public function testHVals()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hSet('h', 'a', 'x');
        $redis->hSet('h', 'b', 'y');
        $redis->hSet('h', 'c', 'z');
        $redis->hSet('h', 'd', 't');
        $res = $redis->hVals('h');
        $this->assertEquals($res, ['x','y','z','t']);
    }
    public function testHGetAll()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hSet('h', 'a', 'x');
        $redis->hSet('h', 'b', 'y');
        $redis->hSet('h', 'c', 'z');
        $redis->hSet('h', 'd', 't');
        $res = $redis->hGetAll('h');
        $this->assertEquals($res, ['a'=>'x','b'=>'y','c'=>'z','d'=>'t']);
    }

    public function testHExists()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hSet('h', 'a', 'x');
        $res = $redis->hExists('h', 'a');
        $this->assertEquals($res, true);
        $res = $redis->hExists('h', 'NonExistingKey');
        $this->assertEquals($res, false);
    }
    public function testHIncrBy()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hIncrBy('h', 'x', 2);
        $res = $redis->hGet('h','x');
        $this->assertEquals($res, 2);
        $redis->hIncrBy('h', 'x', 1);
        $res = $redis->hGet('h','x');
        $this->assertEquals($res, 3);
    }
    public function testHIncrByFloat()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hIncrByFloat('h', 'x', 1.5);
        $res = $redis->hGet('h','x');
        $this->assertEquals($res, 1.5);
        $redis->hIncrByFloat('h', 'x', 1.0);
        $res = $redis->hGet('h','x');
        $this->assertEquals($res, 2.5);
        $redis->hIncrByFloat('h', 'x', -1.1);
        $res = $redis->hGet('h','x');
        $this->assertEquals($res, 1.4);
    }

    public function testHMset()
    {
        $redis = $this->redis;
        $redis->del('user:1');
        $redis->hMSet('user:1', array('name' => 'Joe', 'salary' => 2000));
        $redis->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.
        $res =  $redis->hget('user:1', 'salary');
        $this->assertEquals($res, 2100);
        $this->redis->del('user:1');
    }

    public function testHMget()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hSet('h', 'field1', 'value1');
        $redis->hSet('h', 'field2', 'value2');
        $res = $redis->hMGet('h', array('field1', 'field2'));
        $this->assertEquals($res, ['field1'=>'value1','field2'=>'value2']);
    }

    /*******************************hash表操作函数*********************************/

    public static function tearDownAfterClass()
    {
        $redis = \Core\RedisBase::getInstance('default');
        $redis->del(['key', 'key1', 'key2', 'key3', 'key4', "x", "y","h"]);
        $redis->close();
    }

}