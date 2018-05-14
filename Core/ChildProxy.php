<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-10-12
 * Time: 下午7:17
 */

namespace Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Core\AOP\Proxy;

class ChildProxy extends Proxy
{
    protected $class_name;


    public function __construct($own)
    {
        parent::__construct($own);
        $this->class_name = get_class($own);
    }

    /**
     * 设置上下文
     *
     * @param $context
     */
    public function setContext(&$context)
    {
        $this->own->setContext($context);
    }

    /**
     * 获取上下文
     *
     * @param $context
     */
    public function getContext(&$context)
    {
        $this->own->setContext($context);
    }

    public function beforeCall($name, $arguments = null)
    {
        $obj = new \stdClass;
        $obj->requestId = $this->getMillisecond(true);
        $obj->server = $_SERVER;
        $obj->get = $_GET;
        $obj->post = $_POST;
        $obj->cookie = $_COOKIE;
        $obj->file = $_FILES;
        $obj->Controller = $this->class_name;
        $obj->action = $name;
        $this->setContext($obj);
        unset($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

    }

    public function afterCall($name, $arguments = null, $response = null)
    {
        // Request Log
        $config = \Config\Logs::$default;
        switch ($config['active']) {
            case 'file':
                if ($config['log_level'] <= 200) {
                    $dirName = $config['file']['path'].'/request/'.date('Y-m-d').'.log';
                    // 创建Logger实例
                    $logger = new Logger('REQUEST');
                    // 添加handler
                    $logger->pushHandler(new StreamHandler($dirName, Logger::INFO));
                    $content = (array)$this->own->getContext();
                    $ip = isset($content['server']['REMOTE_ADDR']) ? $content['server']['REMOTE_ADDR'] : '';
                    $method = isset($content['server']['REQUEST_METHOD']) ? $content['server']['REQUEST_METHOD'] : '';
                    // 开始使用
                    $result = [
                        'request'  => [
                            'get' => '',
                            'post' => '',
                            'file' => ''
                        ],
                        'response' => '',
                    ];
                    if (!empty($content['gets'])) {
                        $result['request']['get'] = $content['gets'];
                    }
                    if (!empty($content['post'])) {
                        $result['request']['post'] = $content['post'];
                    }
                    if (!empty($content['file'])) {
                        $result['request']['file'] = $content['file'];
                    }
                    $result['response'] = $response;
                    $spent = sprintf('%.4f', ($this->getMillisecond() - $content['requestId']) / 1000);
                    $msg = $content['requestId'].'|'.$spent.'|'.$ip.'|'.$method;
                    $logger->addInfo($msg, $result);
                }
                break;
        }
    }

    public function getMillisecond($getRequest = false)
    {
        if ($getRequest && isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            return (float)sprintf('%.0f', $_SERVER['REQUEST_TIME_FLOAT'] * 1000);
        } else {
            list($t1, $t2) = explode(' ', microtime());

            return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
        }

    }

}