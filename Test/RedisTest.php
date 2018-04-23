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
        $this->assertEquals($res, ['key1', 'key2']);
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
        $this->assertEquals($res, ['x', 'y', 'z', 't']);
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
        $this->assertEquals($res, ['a' => 'x', 'b' => 'y', 'c' => 'z', 'd' => 't']);
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
        $res = $redis->hGet('h', 'x');
        $this->assertEquals($res, 2);
        $redis->hIncrBy('h', 'x', 1);
        $res = $redis->hGet('h', 'x');
        $this->assertEquals($res, 3);
    }

    public function testHIncrByFloat()
    {
        $redis = $this->redis;
        $redis->del('h');
        $redis->hIncrByFloat('h', 'x', 1.5);
        $res = $redis->hGet('h', 'x');
        $this->assertEquals($res, 1.5);
        $redis->hIncrByFloat('h', 'x', 1.0);
        $res = $redis->hGet('h', 'x');
        $this->assertEquals($res, 2.5);
        $redis->hIncrByFloat('h', 'x', -1.1);
        $res = $redis->hGet('h', 'x');
        $this->assertEquals($res, 1.4);
    }

    public function testHMset()
    {
        $redis = $this->redis;
        $redis->del('user:1');
        $redis->hMSet('user:1', array('name' => 'Joe', 'salary' => 2000));
        $redis->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.
        $res = $redis->hget('user:1', 'salary');
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
        $this->assertEquals($res, ['field1' => 'value1', 'field2' => 'value2']);
    }

    /*********************队列操作命令************************/
    public function testBlPop()
    {
        $redis = $this->redis;
        $redis->del(['key1', 'key2']);
        $redis->lPush('key1', 'A');
        $redis->lPush('key1', 'B');
        $res = $redis->blPop('key1', 10);
        $this->assertEquals($res, ['key1', 'B']);

        $redis = $this->redis;
        $redis->del(['key1', 'key2']);
        $redis->lPush('key1', 'A');
        $redis->lPush('key1', 'B');
        $res = $redis->brPop('key1', 10);
        $this->assertEquals($res, ['key1', 'B']);
    }

    public function testLIndex()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $redis->lPush('key1', 'A');
        $redis->lPush('key1', 'B');
        $res = $redis->lIndex('key1', 0);
        $this->assertEquals($res, "B");
    }

    public function testLPop()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $redis->rPush('key1', 'A');
        $redis->rPush('key1', 'B');
        $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
        $res = $redis->lPop('key1', 0);/* key1 => [ 'B', 'C' ] */
        $this->assertEquals($res, "A");
    }

    public function testLPush()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $res = $redis->lPushx('key1', 'C'); // returns 0
        $this->assertEquals($res, 0);
        $redis->lPush('key1', 'C'); // returns 1
        $res = $redis->lPush('key1', 'B'); // returns 2
        $this->assertEquals($res, 2);
        $res = $redis->lPop("key1");
        $this->assertEquals($res, "B");
        $res = $redis->lLen("key1");
        $this->assertEquals($res, 1);
    }

    public function testLRange()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $redis->rPush('key1', 'A');
        $redis->rPush('key1', 'B');
        $redis->rPush('key1', 'C');
        $res = $redis->lRange('key1', 0, -1);/* key1 => [ 'A', 'B', 'C' ] */
        $this->assertEquals($res, ['A', 'B', 'C']);
        $res = $redis->lRange('key1', 0, 1);/* key1 => [ 'A', 'B' ] */
        $this->assertEquals($res, ['A', 'B']);
    }

    public function testLSet()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $redis->rPush('key1', 'A');
        $redis->rPush('key1', 'B');
        $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
        $redis->lIndex('key1', 0); /* 'A' */
        $redis->lSet('key1', 0, 'X');
        $res = $redis->lIndex('key1', 0); /* 'X' */
        $this->assertEquals($res, "X");
    }

    public function testLRem()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $redis->lPush('key1', 'A');
        $redis->lPush('key1', 'B');
        $redis->lPush('key1', 'C');
        $redis->lPush('key1', 'A');
        $redis->lPush('key1', 'A');

        $redis->lRange('key1', 0, -1); /* array('A', 'A', 'C', 'B', 'A') */
        $redis->lRem('key1', 'A', 2); /* 2 */
        $res = $redis->lRange('key1', 0, -1); /* array('C', 'B', 'A') */
        $this->assertEquals($res, ['C', 'B', 'A']);
    }

    public function testRPop()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $redis->rPush('key1', 'A');
        $redis->rPush('key1', 'B');
        $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
        $redis->rPop('key1'); /* key1 => [ 'A', 'B' ] */
        $res = $redis->lRange("key1", 0, -1);
        $this->assertEquals($res, ['A', 'B']);
    }

    public function testRPopLPush()
    {
        $redis = $this->redis;
        $redis->del(['x', 'y']);

        $redis->lPush('x', 'abc');
        $redis->lPush('x', 'def');
        $redis->lPush('y', '123');
        $redis->lPush('y', '456');

        $res = $redis->rPopLPush('x', 'y');
        $this->assertEquals($res, 'abc');
        $res = $redis->lRange('x', 0, -1);
        $this->assertEquals($res, ['def']);
        $res = $redis->lRange('y', 0, -1);
        $this->assertEquals($res, ["abc", "456", "123"]);
    }

    public function testRPush()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $res = $redis->rPushX('key1', 'A'); // returns 0
        $this->assertEquals($res, 0);

        $redis->rPush('key1', 'A'); // returns 1
        $redis->rPush('key1', 'B'); // returns 2
        $res = $redis->rPush('key1', 'C'); // returns 3
        $this->assertEquals($res, 3);
        /* key1 now points to the following list: [ 'A', 'B', 'C' ] */
    }

    /*************redis　无序集合操作命令*****************/

    public function testSAdd()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $redis->sAdd('key1', 'member1'); /* 1, 'key1' => {'member1'} */
        $redis->sAdd('key1', ['member2', 'member3']); /* 2, 'key1' => {'member1', 'member2', 'member3'}*/
        $redis->sAdd('key1', 'member2'); /* 0, 'key1' => {'member1', 'member2', 'member3'}*/
        $res = $redis->scard('key1');
        $this->assertEquals($res, 3);

    }

    public function testSDiff()
    {
        $redis = $this->redis;
        $redis->del(['key1', 'key2']);

        $redis->sAdd('key1', '1');
        $redis->sAdd('key1', '2');
        $redis->sAdd('key1', '3');
        $redis->sAdd('key1', '4');

        $redis->sAdd('key2', '1');

        $res = $redis->sDiff('key1', 'key2');
        $this->assertEquals($res, [2, 3, 4]);

    }

    public function testSInter()
    {
        $redis = $this->redis;
        $redis->del(['key1', 'key2', 'key3']);
        $redis->sAdd('key1', 'val1');
        $redis->sAdd('key1', 'val2');
        $redis->sAdd('key1', 'val3');
        $redis->sAdd('key1', 'val4');

        $redis->sAdd('key2', 'val3');
        $redis->sAdd('key2', 'val4');

        $redis->sAdd('key3', 'val3');
        $redis->sAdd('key3', 'val4');

        $res = $redis->sInter('key1', 'key2', 'key3');
        $this->assertNotEmpty($res);

    }

    public function testSIsMember()
    {
        $redis = $this->redis;
        $redis->del('key1');
        $redis->sAdd('key1', 'member1');
        $redis->sAdd('key1', 'member2');
        $redis->sAdd('key1', 'member3'); /* 'key1' => {'member1', 'member2', 'member3'}*/

        $res = $redis->sIsMember('key1', 'member1'); /* TRUE */
        $this->assertEquals($res, true);
        $res = $redis->sIsMember('key1', 'memberX'); /* FALSE */
        $this->assertEquals($res, false);

    }

    public function testSMember()
    {
        $redis = $this->redis;
        $redis->del('s');
        $redis->sAdd('s', 'a');
        $redis->sAdd('s', 'b');
        $res = $redis->sMembers('s');
        $this->assertNotEmpty($res);

    }

    public function testSMove()
    {
        $redis = $this->redis;
        $redis->del(['key1', 'key2']);
        $redis->sAdd('key1', 'member11');
        $redis->sAdd('key1', 'member12');
        $redis->sAdd('key1', 'member13'); /* 'key1' => {'member11', 'member12', 'member13'}*/
        $redis->sAdd('key2', 'member21');
        $redis->sAdd('key2', 'member22'); /* 'key2' => {'member21', 'member22'}*/
        $redis->sMove('key1', 'key2', 'member13'); /* 'key1' =>  {'member11', 'member12'} */
        $res = $redis->sMembers('key2');
        $this->assertNotEmpty($res);

    }

    public function testSRem()
    {
        $redis = $this->redis;
        $redis->del(['key1', 'key2']);
        $redis->sAdd('key1', 'member1');
        $redis->sAdd('key1', 'member2');
        $redis->sRem('key1', 'member2'); /*return 2. 'key1' => {'member1'} */
        $res = $redis->sMembers('key1');

        $this->assertEquals($res, ['member1']);

    }

    public function testSUnion()
    {
        $redis = $this->redis;
        $redis->del(['key1', 'key2']);
        $redis->sAdd('s0', '1');
        $redis->sAdd('s0', '2');
        $redis->sAdd('s1', '3');
        $redis->sAdd('s1', '1');
        $redis->sAdd('s2', '3');
        $redis->sAdd('s2', '4');

        $res = $redis->sUnion('s0', 's1', 's2');
        $this->assertEquals($res, ['1', '2', '3', '4']);

    }

    /*********************Lists有序集合操作*********************/
    public function testZAdd()
    {
        $redis = $this->redis;
        $redis->del('key');
        $redis->zAdd('key', 1, 'val1');
        $redis->zAdd('key', 0, 'val0');
        $redis->zAdd('key', 5, 'val5');
        $res = $redis->zRange('key', 0, -1); // array(val0, val1, val5)
        $this->assertEquals($res, ['val0', 'val1', 'val5']);
        // zCard
        $res = $redis->zCard("key");
        $this->assertEquals($res, 3);
        $redis->zAdd('key', 3, 'val2');
        $res = $redis->zCount("key", 1, 5);
        $this->assertEquals($res, 3);
    }

    public function testZIncrBy()
    {
        $redis = $this->redis;
        $redis->del('key');
        $redis->zIncrBy('key', 2.5, 'member1');
        $redis->zIncrBy('key', 1, 'member1');
        $res = $redis->zRange('key', 0, -1);
        $this->assertEquals($res, ['member1']);
        $res = $redis->zScore('key', 'member1');
        $this->assertEquals($res, 3.5);
    }

    public function testMulti()
    {
        $redis = $this->redis;
        $redis->del(['key1', 'key2']);
        $res = $redis->multi()
            ->set('key1', 'val1')
            ->get('key1')
            ->set('key2', 'val2')
            ->get('key2')
            ->exec();
        $this->assertEquals($res, [true, 'val1', true, 'val2']);
        $redis->del(['key1', 'key2']);
        $res = $redis->multi()
            ->set('key1', 'val1')
            ->get('key1')
            ->set('key2', 'val2')
            ->get('key2')
            ->discard();
        $this->assertEquals($res, true);
        $res = $redis->get('key1');
        $this->assertEquals($res, false);
    }

    /*******************************hash表操作函数*********************************/

    public static function tearDownAfterClass()
    {
        $redis = \Core\RedisBase::getInstance('default');
        $redis->del(['key', 'key1', 'key2', 'key3', 'key4', "x", "y", "h", 's', 's0', 's1', 's2']);
        $redis->close();
    }

}