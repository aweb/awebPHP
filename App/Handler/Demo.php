<?php
/**
 * Api演示
 * Created At 11/04/2018.
 * User: kaiyanh
 */

namespace Handler;

class Demo extends Base
{
    // 返回体定义
    public static $resBody
        = array(
            'EXCEPTION' => array('code' => -2, 'msg' => '系统异常，稍后重试'),
            'FAILED' => array('code' => -1, 'msg' => '系统繁忙，稍后重试'),
            'SUCCESS' => array('code' => 0, 'msg' => '成功'),
            'ILLEGAL_ID' => array('code' => 10001, 'msg' => 'ID必须为正整数')
        );

    /**
     * @SWG\GET(path="/v1/demo/list",
     *   tags={"AppDemo 接口演示类"},
     *   summary="分页获取模版列表",
     *   description="get list by page",
     *   operationId="get_list",
     *   produces={"application/json"},
     *   @SWG\Parameter(in="query",name="page",type="integer",description="当前页",required=false),
     *   @SWG\Parameter(in="query",name="pageSize",type="integer",description="每页大小",required=false),
     *   @SWG\Parameter(in="query",name="title",type="string",description="标题名称",required=false),
     *   @SWG\Response(
     *         response="success",
     *         description="无异常返回查询列表数据",
     *         @SWG\Schema(ref="#/definitions/SuccessModel")
     *        ),
     * 	 @SWG\Response(
     *        response="failed",
     *        description="可能返回的错误码, [-2,-1,10001]",
     * 		@SWG\Schema(ref="#/definitions/ErrorModel"),
     *        ),
     * )
     */
    public function getList()
    {
        $userName = isset($_GET['username']) ? $_GET['username'] : 'php';
        $testInfo = \Service\DemoService::getInstance()->test($userName);

        $this->response(self::$resBody['SUCCESS'], $testInfo);
        //var_dump($testInfo, $_REQUEST, $_SESSION);
    }

    /**
     * @SWG\GET(path="/v1/demo/info/{id}",
     *   tags={"AppDemo 接口演示类"},
     *   summary="根据ID获取详情",
     *   description="get info by id",
     *   operationId="info",
     *   produces={"application/json"},
     *   @SWG\Parameter(in="query",name="id",type="integer",description="ID",required=true),
     *   @SWG\Response(
     *         response="success",
     *         description="无异常返回查询详情数据",
     *         @SWG\Schema(ref="#/definitions/SuccessModel")
     *        ),
     * 	 @SWG\Response(
     *        response="failed",
     *        description="可能返回的错误码, [-2,-1,10001]",
     * 		@SWG\Schema(ref="#/definitions/ErrorModel"),
     *        ),
     * )
     */
    function info()
    {
        $this->response(self::$resBody['SUCCESS'], "");
    }

    /**
     * @SWG\POST(path="/v1/demo/create",
     *   tags={"AppDemo 接口演示类"},
     *   summary="创建",
     *   description="create",
     *   operationId="create",
     *   produces={"application/json"},
     *   @SWG\Parameter(in="query",name="name",type="string",description="Name",required=true),
     *   @SWG\Response(
     *         response="success",
     *         description="无异常返回查询详情数据",
     *         @SWG\Schema(ref="#/definitions/SuccessModel")
     *        ),
     * 	 @SWG\Response(
     *        response="failed",
     *        description="可能返回的错误码, [-2,-1,10001]",
     * 		@SWG\Schema(ref="#/definitions/ErrorModel"),
     *        ),
     * )
     */
    function create()
    {
        $this->response(self::$resBody['SUCCESS'], "");
    }

    /**
     * @SWG\PUT(path="/v1/demo/update",
     *   tags={"AppDemo 接口演示类"},
     *   summary="创建",
     *   description="update",
     *   operationId="update",
     *   produces={"application/json"},
     *   @SWG\Parameter(in="query",name="id",type="integer",description="ID",required=true),
     *   @SWG\Parameter(in="query",name="name",type="string",description="Name",required=true),
     *   @SWG\Response(
     *         response="success",
     *         description="无异常返回查询详情数据",
     *         @SWG\Schema(ref="#/definitions/SuccessModel")
     *        ),
     * 	 @SWG\Response(
     *        response="failed",
     *        description="可能返回的错误码, [-2,-1,10001]",
     * 		@SWG\Schema(ref="#/definitions/ErrorModel"),
     *        ),
     * )
     */
    function update()
    {
        $this->response(self::$resBody['SUCCESS'], "");
    }

    /**
     * @SWG\DELETE(path="/v1/demo/delete",
     *   tags={"AppDemo 接口演示类"},
     *   summary="删除",
     *   description="delete",
     *   operationId="delete",
     *   produces={"application/json"},
     *   @SWG\Parameter(in="query",name="id",type="integer",description="ID",required=true),
     *   @SWG\Response(
     *         response="success",
     *         description="无异常返回查询详情数据",
     *         @SWG\Schema(ref="#/definitions/SuccessModel")
     *        ),
     * 	 @SWG\Response(
     *        response="failed",
     *        description="可能返回的错误码, [-2,-1,10001]",
     * 		@SWG\Schema(ref="#/definitions/ErrorModel"),
     *        ),
     * )
     */
    function delete()
    {
        $this->response(self::$resBody['SUCCESS'], "");
    }

}