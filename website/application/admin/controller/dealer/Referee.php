<?php

namespace app\admin\controller\dealer;

use app\common\controller\Backend;

/**
 * 分销商推荐关系管理
 *
 * @icon fa fa-circle-o
 */
class Referee extends Backend
{

    /**
     * Referee模型对象
     * @var \app\admin\model\dealer\Referee
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\dealer\Referee;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $dealer_id = $this->request->get('dealer_id','');
            $list = $this->model
                ->where($where)
                ->where('dealer_id',$dealer_id)
                ->withSum(['order' => function ($query) {
                    $query->where('is_settled', 1);
                }], true, 'order_price')
                ->withCount(['order' => function ($query) {
                    $query->where('is_settled', 1);
                }], false)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $row) {
                $row->visible(['id', 'user_id', 'level', 'create_time','order_sum','order_count']);

            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
