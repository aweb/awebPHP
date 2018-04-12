<?php
/**
 * @SWG\Info(
 *     title="aweb-framework API文档",
 *     description="aweb-framework API文档",
 *     version="1.0.0",
 *   )
 * @SWG\Definition(
 *         definition="SuccessModel",
 *         type="object",
 *         required={"code", "msg", "data"},
 *         @SWG\Property(property="code",type="integer",format="int32"),
 *         @SWG\Property(property="msg",type="string"),
 *         @SWG\Property(property="data",type="object")
 *     )
 * @SWG\Definition(
 *         definition="ErrorModel",
 *         type="object",
 *         required={"code", "msg", "data"},
 *         @SWG\Property(property="code",type="integer",format="int32"),
 *         @SWG\Property(property="msg",type="string"),
 *         @SWG\Property(property="data",type="string")
 *     )
 *
 * Created At 11/04/2018.
 * User: kaiyanh
 */

namespace Handler;

abstract class Base
{

    /**
     * 通过定义返回体的方式返回数据
     *
     * @param array $array array('code' => 0, 'msg' => '成功')
     * @param mixed $data
     *
     * @return json
     *
     */
    public function response(array $array, $data = '')
    {
        $res = array();
        $res['code'] = isset($array['code']) ? $array['code'] : '-2';
        $res['msg'] = isset($array['msg']) ? $array['msg'] : '系统异常，请稍后重试';
        $res['data'] = isset($array['data']) ? $array['data'] : '';
        if (!empty($data)) {
            $res['data'] = $data;
        }
        header('Content-Type: application/json');
        exit(json_encode($res));
    }

}

