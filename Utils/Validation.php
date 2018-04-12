<?php
/**
 * 工具类-验证及常用方法类
 *
 * Created At 09/04/2018 3:29 PM.
 * User: kaiyanh
 */

namespace Utils;

class Validation
{
    /**
     * 验证参数是否为空.
     *
     * @param string $validate 待验证的参数.
     *
     * @return boolean
     */
    public static function isEmpty($validate)
    {
        if (is_string($validate)) {
            $validate = trim($validate);
        }
        return empty($validate);
    }

    /**
     * 验证传入参数是否是非空的数组.
     *
     * @param array $validate 待验证的参数.
     *
     * @return boolean
     */
    public static function arrayNotEmpty($validate = array())
    {
        if (!is_array($validate) || empty($validate)) {
            return false;
        }
        return true;
    }

    /**
     * 检查是否为字符串.
     *
     * @param string $validate    待验证参数.
     * @param array  $lengthScope 字符串最大长度和最小长度.
     *
     * @return boolean
     */
    public static function checkStringInScope($validate, $lengthScope = array())
    {
        $validate = trim($validate);
        if (!is_string($validate) && !is_numeric($validate)) {
            return false;
        }

        if (isset($lengthScope['min']) && mb_strlen($validate) < $lengthScope['min']) {
            return false;
        }
        if (isset($lengthScope['max']) && mb_strlen($validate) > $lengthScope['max']) {
            return false;
        }
        return true;
    }

    /**
     * 验证参数是否是合乎规范的整数.可选：取值范围.
     *
     * @param integer $validate 待验证的参数.
     * @param array   $scope    可选：取值范围.
     *
     * @return boolean
     */
    public static function checkIntInScope($validate, $scope = array())
    {
        if (!ctype_digit((string)$validate)) {
            return false;
        }

        if (self::arrayNotEmpty($scope)) {
            if (isset($scope['max']) && (!is_int($scope['max']) || $validate > $scope['max'])) {
                return false;
            }
            if (isset($scope['min']) && (!is_int($scope['min']) || $validate < $scope['min'])) {
                return false;
            }
            // 不超过mysql int类型取值范围.
            if ($validate > 9223372036854775807) {
                return false;
            }
        }
        return true;
    }

    /**
     * 验证参数是否是合乎规范的 中文名字  .可选：取值范围.
     * 中文名字 包括： 中文 大小写英文 数字 下划线(_) 连字符(-)
     *
     * @param integer $validate 待验证的参数.
     * @param array   $scope    可选：取值范围.
     *
     * @return boolean
     */
    public static function checkChsNameScope($validate, $scope = array())
    {
        if (self::arrayNotEmpty($scope)) {
            if (isset($scope['max']) && (!is_int($scope['max']))) {
                return false;
            }

            if (isset($scope['min']) && (!is_int($scope['min']))) {
                return false;
            }

            $pregStr = "/^[\\\u4e00-\\\u9fa5_a-zA-Z0-9_-]{" . $scope['min'] . "," . $scope['max'] . "}/";
            if (preg_match($pregStr, $validate) <= 0) {
                return false;
            }

        }
        return true;
    }


    /**
     * 验证数组的每一个元素是不是非空.
     *
     * @param array  $validate 待验证的参数.
     * @param string $type     参数类型.
     * @param array  $scope    其他限制条件.
     *
     * @return boolean
     */
    public static function arrayEveryOneNotEmpty($validate = array(), $type = '', $scope = array('min' => 0))
    {
        if (self::arrayNotEmpty($validate)) {
            foreach ($validate as $str) {
                switch ($type) {
                    case 'integer':
                        if (!self::checkIntInScope($str, $scope)) {
                            return false;
                        }
                        break;
                    case 'string':
                        if (!self::checkStringInScope($str, $scope)) {
                            return false;
                        }
                        break;
                    default:
                        if (self::isEmpty($str)) {
                            return false;
                        }
                        break;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * 验证参数是否是合乎规范的整数.可选：取值范围.
     *
     * @param integer $validate 待验证的参数.
     * @param array   $scope    可选：取值范围.
     *
     * @return boolean
     */
    public static function inArray($validate, $scope = array())
    {
        return in_array($validate, $scope);
    }

    /**
     * 批量验证工厂方法（通过配置进行验证）.
     *
     * @param array $validateArr 待验证数组
     *                           [[
     *                           'type'=>'isEmpty|arrayNotEmpty|checkStringInScope|checkIntInScope|inArray', //验证类型
     *                           'value' => '', //验证值
     *                           'scope'=>[], //验证条件
     *                           'errKey'=>'', //错误放回码
     *                           'isEmptyCancel' => false // 如果数据为空值不进行验证
     *                           ]]
     *
     * @return
     */
    public static function validateFactory($validateArr)
    {
        if (empty($validateArr)) {
            return false;
        }
        foreach ($validateArr as $value) {
            // 如果为空则不验证
            if (empty($value['value']) && isset($value['isEmptyCancel']) && $value['isEmptyCancel'] === true) {
                continue;
            }
            // 执行验证
            switch ($value['type']) {
                case 'isEmpty':
                    if (!isset($value['value']) || self::isEmpty($value['value'])) {
                        return $value['errKey'];
                    }
                    break;
                case 'arrayNotEmpty':
                    if (!isset($value['value']) || !self::arrayNotEmpty($value['value'])) {
                        return $value['errKey'];
                    }
                    break;
                case 'checkStringInScope':
                    if (!isset($value['value']) || !isset($value['scope'])
                        || !self::checkStringInScope($value['value'], $value['scope'])
                    ) {
                        return $value['errKey'];
                    }
                    break;
                case 'checkIntInScope':
                    if (!isset($value['value']) || !isset($value['scope'])
                        || !self::checkIntInScope($value['value'], $value['scope'])
                    ) {
                        return $value['errKey'];
                    }
                    break;
                case 'inArray':
                    if (!isset($value['value']) || !isset($value['scope'])
                        || !self::inArray($value['value'], $value['scope'])
                    ) {
                        return $value['errKey'];
                    }
                    break;
                case 'isDatetime':
                    if (!isset($value['value']) || !self::isDatetime($value['value'])) {
                        return $value['errKey'];
                    }
                    break;
                case 'isMobile':
                    if (!isset($value['value']) || !preg_match("/^1[34578]{1}\d{9}$/", $value['value'])) {
                        return $value['errKey'];
                    }
                    break;
                case 'checkChsNameScope':
                    if (!isset($value['value']) || !self::checkChsNameScope($value['value'], $value['scope'])) {
                        return $value['errKey'];
                    }
                    break;
            }
        }
        return true;
    }

    /**
     * 判断时间格式是否正确
     *
     * @param string $param  输入的时间
     * @param string $format 指定的时间格式
     *
     * @return boolean
     */
    public static function isDatetime($param = '', $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($param)) === $param;
    }

}