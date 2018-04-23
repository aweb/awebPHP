<?php
/**
 * 异常处理类
 *
 * Created At 21/04/2018 8:54 PM.
 * User: kaiyanh <nzing@aweb.cc>
 */

namespace Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Exception extends \Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
        //  日志记录
        $config = \Config\Logs::$default;
        if ($config['log_level'] <= 400) {
            $dirName = $config['file']['path'].'/exception/'.date('Y-m-d').'.log';
            // 创建Logger实例
            $logger = new Logger('EXCEPTION');
            // 添加handler
            $logger->pushHandler(new StreamHandler($dirName, Logger::ERROR));
            $logger->addError($this->__toString());
        }
    }

    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction()
    {
        echo "A Custom function for this type of exception\n";
    }
}