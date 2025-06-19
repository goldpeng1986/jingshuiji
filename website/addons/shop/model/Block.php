<?php

namespace addons\shop\model;

use think\Cache;
use think\Db;
use think\Model;
use think\View;

/**
 * 区块模型
 */
class Block extends Model
{
    protected $name = "shop_block";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    protected static $tagCount = 0;

    protected static function init()
    {
        $config = get_addon_config('shop');
        self::$config = $config;
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_block_img'];
        return cdnurl($value);
    }

    public function getContentAttr($value, $data)
    {
        if (isset($data['parsetpl']) && $data['parsetpl']) {
            $view = View::instance();
            $view->engine->layout(false);
            return $view->display($data['content']);
        }
        return $data['content'];
    }

    public function getHasimageAttr($value, $data)
    {
        return $this->getData("image") ? true : false;
    }

    /**
     * 通过名称获取区块列表
     * @param $name
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBlockListByName($name)
    {
        return self::getBlockList(['name' => $name]);
    }

    /**
     * 获取区块列表
     * @param $params
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBlockList($params)
    {
        $config = get_addon_config('shop');
        $name = empty($params['name']) ? '' : $params['name'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'id' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $cache = !isset($params['cache']) ? $config['cachelifetime'] === 'true' ? true : (int)$config['cachelifetime'] : (int)$params['cache'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];
        $cache = !$cache ? false : $cache;

        self::$tagCount++;

        $where = ['status' => 'normal'];
        if ($name !== '') {
            $where['name'] = $name;
        }
        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");
        $order = $orderby == 'weigh' ? $order . ',id DESC' : $order;

        $blockModel = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order);

        if ($paginate) {
            $paginateArr = explode(',', $paginate);
            $listRows = is_numeric($paginate) ? $paginate : (is_numeric($paginateArr[0]) ? $paginateArr[0] : $row);
            $config = [];
            $config['var_page'] = isset($paginateArr[2]) ? $paginateArr[2] : 'bpage' . self::$tagCount;
            $config['path'] = isset($paginateArr[3]) ? $paginateArr[3] : '';
            $config['fragment'] = isset($paginateArr[4]) ? $paginateArr[4] : '';
            $config['query'] = request()->get();
            $config['type'] = '\\addons\\shop\\library\\Bootstrap';
            $list = $blockModel->paginate($listRows, (isset($paginateArr[1]) ? $paginateArr[1] : false), $config);
        } else {
            $list = $blockModel->limit($limit)->cache($cache)->select();
        }

        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        $time = time();
        foreach ($list as $k => &$v) {
            if (($v['begintime'] && $time < $v['begintime']) || ($v['endtime'] && $time > $v['endtime'])) {
                unset($list[$k]);
                continue;
            }
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 获取区块内容
     * @param $params
     * @return string
     */
    public static function getBlockContent($params)
    {
        $fieldName = isset($params['id']) ? 'id' : 'name';
        $value = isset($params[$fieldName]) ? $params[$fieldName] : '';
        $field = isset($params['field']) ? $params['field'] : '';
        $cache = !isset($params['cache']) ? true : (int)$params['cache'];
        $cache = !$cache ? false : $cache;

        $row = self::where($fieldName, $value)
            ->where('status', 'normal')
            ->cache($cache)
            ->find();
        $result = '';
        if ($row) {
            $content = $row->getData('content');
            if ($field && isset($row[$field])) {
                $result = $row->getData($field);
            } else {
                if ($content) {
                    $result = $content;
                } elseif ($row['image']) {
                    $result = '<img src="' . $row['image'] . '" class="img-responsive"/>';
                } else {
                    $result = $row['title'];
                }
                if ($row['url'] && !$content) {
                    $result = $row['url'] ? '<a href="' . (preg_match("/^https?:\/\/(.*)/i", $row['url']) ? $row['url'] : url($row['url'])) . '" target="_blank">' . $result . '</a>' : $result;
                }
            }
            $row['begintime'] = (int)$row['begintime'];
            $row['endtime'] = (int)$row['endtime'];
            if (!$content) {
                return $result;
            } else {
                if (!$row['parsetpl']) {
                    $tagIdentify = "taglib_shop_block_content_" . $row['id'];
                    Cache::set($tagIdentify, $result);
                    $result = "{:cache('{$tagIdentify}')}";
                }
            }
            //未开始或过期处理
            $result = "{if (!{$row['begintime']} || time()>{$row['begintime']})&&(!{$row['endtime']} || time()<{$row['endtime']})}{$result}{/if}";
        }
        return $result;
    }

    /**
     * 根据类型获取区块/Banner列表 (供API使用)
     * @param string $type 区块类型标识
     * @return array 列表数据
     */
    public static function getBlockListByType($type)
    {
        $now = time(); // 当前时间戳

        $list = self::where('type', $type) // 根据类型筛选
            ->where('status', 'normal')      // 只获取状态正常的
            ->where(function ($query) use ($now) {
                // 生效时间小于等于当前时间
                $query->where('begintime', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                // (结束时间大于等于当前时间 或 结束时间为空/0 表示永不过期)
                $query->where('endtime', '>=', $now)
                      ->orWhereNull('endtime')
                      ->orWhere('endtime', '=', 0);
            })
            ->order('weigh', 'desc') // 按权重降序排序
            ->field('id, title, image, url, content') // 选择需要的字段
            ->select();

        // 结果处理: image 字段已通过模型的 getImageAttr 自动处理 cdnurl
        // 如果没有 getImageAttr 或者它不符合API需求 (例如API不需要默认图片)，则需要手动处理:
        // foreach ($list as $key => $item) {
        //     // 确保即使 getImageAttr 存在，API也只获取实际配置的图片，而不是默认图
        //     $originalImage = $item->getData('image'); // 获取原始图片路径
        //     $list[$key]['image'] = $originalImage ? cdnurl($originalImage, true) : '';
        // }
        // 考虑到现有的 getImageAttr 可能会设置默认图片，这里我们显式获取原始值并处理
        if ($list) {
            foreach ($list as $item) {
                $originalImage = $item->getRealAttr('image'); // 获取 image 字段的原始数据库值
                $item->image = $originalImage ? cdnurl($originalImage, true) : ''; // 如果有原始图片则加 cdnurl，否则为空字符串
            }
            return $list->toArray(); // 返回数组格式的数据
        }

        return []; // 如果没有记录，返回空数组
    }
}
