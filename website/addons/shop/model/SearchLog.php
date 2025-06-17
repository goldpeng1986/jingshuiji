<?php

namespace addons\shop\model;

use think\Db;
use think\Model;

/**
 * 搜索记录
 */
class SearchLog Extends Model
{

    // 表名
    protected $name = 'shop_search_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
    protected static $tagCount = 0;

    /**
     * 获取标签列表
     * @param $tag
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getSearchlogList($tag)
    {
        $config = get_addon_config('shop');
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $field = empty($tag['field']) ? '*' : $tag['field'];
        $row = empty($tag['row']) ? 10 : (int)$tag['row'];
        $orderby = empty($tag['orderby']) ? 'nums' : $tag['orderby'];
        $orderway = empty($tag['orderway']) ? 'desc' : strtolower($tag['orderway']);
        $limit = empty($tag['limit']) ? $row : $tag['limit'];
        $cache = !isset($tag['cache']) ? $config['cachelifetime'] === 'true' ? true : (int)$config['cachelifetime'] : (int)$tag['cache'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($tag['paginate']) ? false : $tag['paginate'];
        $cache = !$cache ? false : $cache;

        self::$tagCount++;

        $where = [];

        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");

        $tagModel = self::where($where)
            ->where('status', 'normal')
            ->where($condition)
            ->field($field)
            ->orderRaw($order);

        if ($paginate) {
            $paginateArr = explode(',', $paginate);
            $listRows = is_numeric($paginate) ? $paginate : (is_numeric($paginateArr[0]) ? $paginateArr[0] : $row);
            $config = [];
            $config['var_page'] = isset($paginateArr[2]) ? $paginateArr[2] : 'tpage' . self::$tagCount;
            $config['path'] = isset($paginateArr[3]) ? $paginateArr[3] : '';
            $config['fragment'] = isset($paginateArr[4]) ? $paginateArr[4] : '';
            $config['query'] = request()->get();
            $list = $tagModel->paginate($listRows, (isset($paginateArr[1]) ? $paginateArr[1] : false), $config);
        } else {
            $list = $tagModel->limit($limit)->cache($cache)->select();
        }

        return $list;
    }
}
