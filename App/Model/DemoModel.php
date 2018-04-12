<?php
/**
 * Model数据交互层 - Demo演示
 * Created At 11/04/2018.
 * User: kaiyanh  <nzing@aweb.cc>
 */
namespace Model;

class DemoModel extends BaseModel
{
    /**
     * test
     *
     * @param string $userName 用户名
     *
     * @return string
     */
    function test($userName) {
        return 'Welcome '.$userName;
    }
}
