<?php
/**
 * 接口入口文件
 *
 * Created At 11/04/2018 5:08 PM.
 * User: kaiyanh
 */
define('ENV', 'TEST');   //环境标识，[PROD,TEST,DEV]
error_reporting(E_ALL);
if (ENV == 'PROD') {
    error_reporting(0);
}
//error_reporting(0);
define('BASE_ROOT', __DIR__ . "/../");
// 引入自动加载
require BASE_ROOT . "/vendor/autoload.php";
// 载入路由配置
require BASE_ROOT . "/Config/routers.php";

// 开始执行
\Bootstrap\App::instance()->run($routers);




