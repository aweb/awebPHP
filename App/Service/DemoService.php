<?php
/**
 * Service 业务处理层 -- Demo演示类
 *
 * Created At 11/04/2018 10:24 PM.
 * User: kaiyanh <nzing@aweb.cc>
 */

namespace Service;

class DemoService extends BaseService
{
    /**
     * test
     *
     * @param string $userName 用户名
     *
     * @return string
     */
    function test($userName = '')
    {
        return \Model\DemoModel::instance()->test($userName);
    }

}