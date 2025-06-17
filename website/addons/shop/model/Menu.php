<?php

namespace addons\shop\model;

use fast\Tree;
use think\Cache;
use think\Db;
use think\Model;

/**
 * 模型
 */
class Menu extends Model
{

    // 表名
    protected $name = 'shop_menu';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];

    protected static $tagCount = 0;

    protected static $parentIds = null;

    protected static $outlinkParentIds = null;

    protected static $navParentIds = null;

    /**
     * 获取分类列表
     * @param $tag
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getMenuList($tag)
    {
        $config = get_addon_config('shop');
        $type = empty($tag['type']) ? '' : $tag['type'];
        $typeid = !isset($tag['typeid']) ? '' : $tag['typeid'];
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $field = empty($tag['field']) ? '*' : $tag['field'];
        $row = empty($tag['row']) ? 10 : (int)$tag['row'];
        $flag = empty($tag['flag']) ? '' : $tag['flag'];
        $orderby = empty($tag['orderby']) ? 'weigh' : $tag['orderby'];
        $orderway = empty($tag['orderway']) ? 'desc' : strtolower($tag['orderway']);
        $limit = empty($tag['limit']) ? $row : $tag['limit'];
        $cache = !isset($tag['cache']) ? $config['cachelifetime'] === 'true' ? true : (int)$config['cachelifetime'] : (int)$tag['cache'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($tag['paginate']) ? false : $tag['paginate'];
        $cache = !$cache ? false : $cache;
        $where = ['status' => 'normal'];

        self::$tagCount++;

        if ($type === 'top') {
            //顶级分类
            $where['pid'] = 0;
        } elseif ($type === 'brother') {
            $subQuery = self::where('id', 'in', $typeid)->field('pid')->buildSql();
            //同级
            $where['pid'] = ['exp', Db::raw(' IN ' . '(' . $subQuery . ')')];
        } elseif ($type === 'son') {
            $subQuery = self::where('pid', 'in', $typeid)->field('id')->buildSql();
            //子级
            $where['id'] = ['exp', Db::raw(' IN ' . '(' . $subQuery . ')')];
        } elseif ($type === 'sons') {
            //所有子级
            $where['id'] = ['in', self::getMenuChildrenIds($typeid)];
        } else {
            if ($typeid !== '') {
                $where['id'] = ['in', $typeid];
            }
        }

        //如果有设置标志,则拆分标志信息并构造condition条件
        if ($flag !== '') {
            if (stripos($flag, '&') !== false) {
                $arr = [];
                foreach (explode('&', $flag) as $k => $v) {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr) {
                    $condition .= "(" . implode(' AND ', $arr) . ")";
                }
            } else {
                $condition .= ($condition ? ' AND ' : '');
                $arr = [];
                foreach (explode(',', str_replace('|', ',', $flag)) as $k => $v) {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr) {
                    $condition .= "(" . implode(' OR ', $arr) . ")";
                }
            }
        }

        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");
        $order = $orderby == 'weigh' ? $order . ',id DESC' : $order;

        $MenuModel = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order);
        if ($paginate) {
            $paginateArr = explode(',', $paginate);
            $listRows = is_numeric($paginate) ? $paginate : (is_numeric($paginateArr[0]) ? $paginateArr[0] : $row);
            $config = [];
            $config['var_page'] = isset($paginateArr[2]) ? $paginateArr[2] : 'cpage' . self::$tagCount;
            $config['path'] = isset($paginateArr[3]) ? $paginateArr[3] : '';
            $config['fragment'] = isset($paginateArr[4]) ? $paginateArr[4] : '';
            $config['query'] = request()->get();
            $list = $MenuModel->paginate($listRows, (isset($paginateArr[1]) ? $paginateArr[1] : false), $config);
        } else {
            $list = $MenuModel->limit($limit)->cache($cache)->select();
        }

        return $list;
    }

    /**
     * 获取菜单列表HTML
     * @param array $tag
     * @return mixed|string
     */
    public static function getMenu($tag = [])
    {
        $config = get_addon_config('shop');
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $cache = !isset($tag['cache']) ? $config['cachelifetime'] === 'true' ? true : (int)$config['cachelifetime'] : (int)$tag['cache'];
        $maxLevel = !isset($tag['maxlevel']) ? 0 : $tag['maxlevel'];
        $cache = !$cache ? false : $cache;

        $menuList = Menu::where($condition)
            ->where('status', 'normal')
            ->field('id,pid,name,url')
            ->order('weigh desc,id desc')
            ->cache($cache)
            ->select();
        $tree = \fast\Tree::instance();
        $tree->init(collection($menuList)->toArray(), 'pid');
        $result = self::getTreeUl($tree, 0, '', '', 1, $maxLevel);

        return $result;
    }

    public static function getTreeUl($tree, $myid, $selectedids = '', $disabledids = '', $level = 1, $maxlevel = 0)
    {
        $str = '';
        $childs = $tree->getChild($myid);
        if ($childs) {
            foreach ($childs as $value) {
                $id = $value['id'];
                unset($value['child']);
                $selected = $selectedids && in_array($id, (is_array($selectedids) ? $selectedids : explode(',', $selectedids))) ? 'active' : '';
                $disabled = $disabledids && in_array($id, (is_array($disabledids) ? $disabledids : explode(',', $disabledids))) ? 'disabled' : '';
                if (!$selected && request()->url(substr($value['url'], 0, 1) !== '/' ? true : null) == $value['url']) {
                    $selected = 'active';
                }
                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled));
                $value = array_combine(array_map(function ($k) {
                    return '@' . $k;
                }, array_keys($value)), $value);
                $itemtpl = '<li class="@dropdown @selected" value=@id @disabled><a data-toggle="@toggle" data-target="#" href="@url">@name@caret</a> @childlist</li>';
                $nstr = strtr($itemtpl, $value);
                $childlist = '';
                if (!$maxlevel || $level < $maxlevel) {
                    $childdata = self::getTreeUl($tree, $id, $selectedids, $disabledids, $level + 1, $maxlevel);
                    $childlist = $childdata ? '<ul class="dropdown-menu" role="menu">' . $childdata . '</ul>' : "";
                }
                $str .= strtr($nstr, [
                    '@childlist' => $childlist,
                    '@caret'     => $childlist ? ($level == 1 ? '<span class="indicator"><i class="fa fa-angle-down"></i></span>' : '<span class="indicator"><i class="fa fa-angle-right"></i></span>') : '',
                    '@dropdown'  => $childlist ? ($level == 1 ? 'dropdown' : 'dropdown-submenu') : '',
                    '@toggle'    => $childlist ? 'dropdown' : ''
                ]);
            }
        }
        return $str;
    }
}
