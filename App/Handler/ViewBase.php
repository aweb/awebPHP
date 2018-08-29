<?php
/**
 *
 * Created At 2018/8/29 上午11:27.
 * User: kaiyanh <nzing@aweb.cc>
 */

namespace Handler;

use Core\ChildProxy;
use Core\Child;

class  ViewBase extends Child
{
    protected $template = null;

    /**
     * Controller constructor.
     *
     * @param string $proxy
     */
    public function __construct($proxy = ChildProxy::class)
    {
        parent::__construct($proxy);
        $this->template = new \League\Plates\Engine(VIEW_ROOT);
    }
}