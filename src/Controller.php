<?php

namespace zcswoole;


class Controller
{
    /** @var string 动作 */
    public $actionID;

    /**
     * 在action之前运行,例如可以处理一些统一认证
     */
    public function beforeAction()
    {
    }

    /**
     * 在action之后运行,例如可以处理一些日志类操作
     */
    public function afterAction()
    {
    }
}