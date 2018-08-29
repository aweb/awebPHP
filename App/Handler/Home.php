<?php
/**
 *
 * Created At 2018/8/29 上午11:27.
 * User: kaiyanh <nzing@aweb.cc>
 */

namespace Handler;

class  Home extends ViewBase
{
    function index()
    {
        echo $this->template->render('Home/index', ['title' => 'php native template', 'name' => 'aweb.cc']);
    }
}