<?php

namespace addons\shop\controller\api;

use addons\shop\model\TemplateMsg;
use addons\shop\model\SubscribeLog;

/**
 * 小程序订阅
 */
class Subscribe extends Base
{
    protected $noNeedLogin = [];

    //用户订阅的记录
    public function index()
    {
        $tpl_ids = $this->request->post('tpl_ids/a');
        $order_sn = $this->request->post('order_sn');
        if (!$order_sn || empty($tpl_ids)) {
            $this->error('参数错误，订阅失败');
        }
        $data = [];
        foreach ($tpl_ids as $key => $item) {
            if($key!='errMsg'){
                $data[] = [
                    'tpl_id'   => $key,
                    'user_id'  => $this->auth->id,
                    'order_sn' => $order_sn,
                    'status'   => 0
                ];
            }
        }
        (new SubscribeLog())->saveAll($data);
        $this->success('订阅消息成功！');
    }
}
