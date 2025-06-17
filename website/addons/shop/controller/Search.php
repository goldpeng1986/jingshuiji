<?php

namespace addons\shop\controller;

use addons\shop\model\SearchLog;
use think\Config;
use think\Session;

/**
 * 搜索控制器
 * Class Search
 * @package addons\shop\controller
 */
class Search extends Base
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 搜索首页
     */
    public function index()
    {
        $config = get_addon_config('shop');

        $search = $this->request->request("search", $this->request->request("q", ""));
        $search = mb_substr($search, 0, 100);

        if (!$search) {
            $this->error("关键字不能为空");
        }
        //搜索入库
        $token = $this->request->request("__searchtoken__");
        if ($search && $token && $token == Session::get("__searchtoken__")) {
            $log = SearchLog::getByKeywords($search);
            if ($log) {
                $log->setInc("nums");
            } else {
                SearchLog::create(['keywords' => $search, 'nums' => 1, 'status' => 'hidden']);
            }
        }

        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '');
        $orderway = $orderway && in_array(strtolower($orderway), ['asc', 'desc']) ? $orderway : 'desc';
        $params = ['q' => $search];
        if ($orderby) {
            $params['orderby'] = $orderby;
        }
        if ($orderway) {
            $params['orderway'] = $orderway;
        }

        //默认排序字段
        $orderList = [
            'default'    => ['title' => '默认排序', 'value' => 'weigh'],
            'sales'      => ['title' => '销量', 'value' => 'sales'],
            'comments'   => ['title' => '评价次数', 'value' => 'comments'],
            'createtime' => ['title' => '发布时间', 'value' => 'createtime'],
        ];

        $orderby = $this->request->get('orderby', 'default');
        $orderway = $this->request->get('orderway', 'desc');
        $order = isset($orderList[$orderby]) ? $orderList[$orderby] : $orderList['default'];
        $orderby = $order['value'];
        $orderway = $orderway == 'asc' ? 'asc' : 'desc';

        $searchList = \addons\shop\model\Goods::
        where('title|keywords', 'like', "%{$search}%")
            ->order($orderby, $orderway)
            ->paginate(20, false, [
                'query' => $this->request->get()
            ]);

        $url = $this->request->url();
        foreach ($orderList as $key => &$item) {
            $item['url'] = $url . (stripos($url, '?') !== false ? '&' : '?') . "orderby={$key}&orderway=" .
                ($orderby == $item['value'] ? ($orderway == 'desc' ? 'asc' : 'desc') : 'desc');
        }
        $this->view->assign('orderby', $orderby);
        $this->view->assign('orderway', $orderway);
        $this->view->assign('orderList', $orderList);
        $this->view->assign('searchList', $searchList);
        $this->view->assign('keywords', $search);

        Config::set('shop.title', "搜索 " . $search);
        return $this->view->fetch('/search');
    }

}
