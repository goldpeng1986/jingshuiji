<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:91:"/www/wwwroot/jingshui.sddianfeng.cn/public/../application/admin/view/shop/report/index.html";i:1709960820;s:78:"/www/wwwroot/jingshui.sddianfeng.cn/application/admin/view/layout/default.html";i:1710226298;s:75:"/www/wwwroot/jingshui.sddianfeng.cn/application/admin/view/common/meta.html";i:1710223706;s:77:"/www/wwwroot/jingshui.sddianfeng.cn/application/admin/view/common/script.html";i:1710223708;}*/ ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">
<meta name="robots" content="noindex, nofollow">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config ?? ''); ?>
    };
</script>

    </head>
    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <style>
    .panel-statistics h4 {
        color: #666;
        font-weight: 400;
        font-size: 14px;
    }

    .panel-statistics h3 {
        font-weight: 500;
        font-size: 14px;
        color: #333;
    }

    .panel-statistics em {
        font-style: normal;
    }

    .panel-statistics .pull-right {
        padding-right: 10px;
    }

    .panel-statistics .table thead tr th {
        font-weight: normal;
    }

    .panel-statistics .table tbody tr td {
        font-weight: normal;
        vertical-align: middle;
    }

    .panel-statistics .table tbody tr td p {
        margin: 0;
    }

</style>
<div class="btn-refresh hidden" id="resetecharts"></div>
<div class="row">
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>总订单金额</h4>
                    <h3>
                        ￥
                        <span id="totalOrderAmount"><?php echo sprintf('%.2f',$totalOrderAmount); ?></span>
                    </h3>
                </div>
                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日订单金额</h4>
                    <h3>
                        ￥
                        <span id="todayOrderAmount"><?php echo sprintf('%.2f',$todayOrderAmount); ?></span>
                        <em id="todayOrderRatio" data-toggle="tooltip" data-title="昨日：￥<?php echo sprintf('%.2f',$yesterdayOrderAmount); ?>" class="text-<?php echo $todayOrderRatio>=0?'success':'danger'; ?>">
                            <?php echo $todayOrderRatio>=0?'+':''; ?><?php echo $todayOrderRatio; ?>%
                        </em>
                    </h3>
                </div>
                <div class="pull-right" style="color:#ffc8c8;">
                    <i class="fa fa-calendar fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>总用户数</h4>
                    <h3><?php echo $totalUser; ?></h3>
                </div>
                <div class="pull-right" style="color:#c8e3ff;">
                    <i class="fa fa-users fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日新增用户数</h4>
                    <h3>
                        <?php echo $todayUser; ?>
                        <em data-toggle="tooltip" data-title="昨日：<?php echo $yesterdayUser; ?>" class="text-<?php echo $todayUserRatio>=0?'success':'danger'; ?>"><?php echo $todayUserRatio>=0?'+':''; ?><?php echo $todayUserRatio; ?>%</em>
                    </h3>
                </div>
                <div class="pull-right" style="color:#ffe9c8;">
                    <i class="fa fa-user fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-top:15px;">
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>总利润</h4>
                    <h3>
                        ￥
                        <span id="totalProfitAmount"><?php echo sprintf('%.2f',$totalProfitAmount); ?></span>
                    </h3>
                </div>
                <div class="pull-right" style="color:#c8e3ff;">
                    <i class="fa fa-gift fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>总退款金额</h4>
                    <h3>
                        ￥
                        <span id="totalRefundAmount"><?php echo sprintf('%.2f',$totalRefundAmount); ?></span>
                    </h3>
                </div>
                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-reply-all fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日退款金额</h4>
                    <h3>
                        ￥
                        <span id="todayRefundAmount"><?php echo sprintf('%.2f',$todayRefundAmount); ?></span>
                    </h3>
                </div>
                <div class="pull-right" style="color:#ffc8c8;">
                    <i class="fa fa-calendar-times-o fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>昨天退款金额</h4>
                    <h3>
                        ￥
                        <span id="yesterdayRefundAmount"><?php echo sprintf('%.2f',$yesterdayRefundAmount); ?></span>
                    </h3>
                </div>
                <div class="pull-right" style="color:#c8e3ff;">
                    <i class="fa fa-calendar-minus-o fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 订单分散地图 -->
<div class="row" style="margin-top:15px;">
    <div class="col-xs-12">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <!-- <h4>订单趋势</h4> -->
                <div id="datefilter">
                    <form id="form3" action="" role="form" novalidate class="form-inline">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" data-chart="chart3">
                            <i class="fa fa-refresh"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Today'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Yesterday'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 7 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 30 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last month'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('This month'); ?></a>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <input type="text" class="form-control input-inline datetimerange" data-type="order" placeholder="指定日期" style="width:270px;">
                        </div>
                    </form>
                </div>
                <div id="echarts3" style="height:600px;width:100vw;margin-top:15px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- 订单金额 -->
<div class="row" style="margin-top:15px;">
    <div class="col-xs-12">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <!-- <h4>订单趋势</h4> -->
                <div id="datefilter">
                    <form id="form1" action="" role="form" novalidate class="form-inline">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" data-chart="chart1">
                            <i class="fa fa-refresh"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Today'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Yesterday'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 7 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 30 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last month'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('This month'); ?></a>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <input type="text" class="form-control input-inline datetimerange" data-type="order" placeholder="指定日期" style="width:270px;">
                        </div>
                    </form>
                </div>
                <div id="echarts1" style="height:400px;width:100%;margin-top:15px;"></div>
            </div>
        </div>
    </div>
</div>
<!-- 订单数量 -->
<div class="row" style="margin-top:15px;">
    <div class="col-xs-12">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <!-- <h4>订单趋势</h4> -->
                <div id="datefilter">
                    <form id="form2" action="" role="form" novalidate class="form-inline">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" data-chart="chart2">
                            <i class="fa fa-refresh"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Today'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Yesterday'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 7 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 30 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last month'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('This month'); ?></a>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <input type="text" class="form-control input-inline datetimerange" data-type="order" placeholder="指定日期" style="width:270px;">
                        </div>
                    </form>
                </div>
                <div id="echarts2" style="height:400px;width:100%;margin-top:15px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- -- -->
<div class="row" style="margin-top:15px;">
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>今日商品销售排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">标题</th>
                        <th width="20%" class="text-center">金额</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($todayPaidList) || $todayPaidList instanceof \think\Collection || $todayPaidList instanceof \think\Paginator): if( count($todayPaidList)==0 ) : echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;else: foreach($todayPaidList as $key=>$item): ?>
                    <tr>
                        <td>
                           <a href="<?php echo $item['url']; ?>" target="_blank"><p><?php echo $item['title']; ?></p></a>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['amount']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>本周商品销售排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">标题</th>
                        <th width="20%" class="text-center">金额</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($weekPaidList) || $weekPaidList instanceof \think\Collection || $weekPaidList instanceof \think\Paginator): if( count($weekPaidList)==0 ) : echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;else: foreach($weekPaidList as $key=>$item): ?>
                    <tr>
                        <td>
                            <a href="<?php echo $item['url']; ?>" target="_blank"><p><?php echo $item['title']; ?></p></a>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['amount']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>本月商品销售排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">标题</th>
                        <th width="20%" class="text-center">金额</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($monthPaidList) || $monthPaidList instanceof \think\Collection || $monthPaidList instanceof \think\Paginator): if( count($monthPaidList)==0 ) : echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;else: foreach($monthPaidList as $key=>$item): ?>
                    <tr>
                        <td>
                            <a href="<?php echo $item['url']; ?>" target="_blank"> <p><?php echo $item['title']; ?></p></a>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['amount']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- -- -->
<div class="row" style="margin-top:20px;">
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>热门搜索</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="80%">关键字</th>
                        <th class="text-center">搜索次数</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($hotSearchList) || $hotSearchList instanceof \think\Collection || $hotSearchList instanceof \think\Paginator): if( count($hotSearchList)==0 ) : echo "<tr><td colspan='2' class='text-center'>暂无数据</td></tr>" ;else: foreach($hotSearchList as $key=>$item): ?>
                    <tr>
                        <td>
                            <a href="<?php echo addon_url('shop/search/index', ['q'=>$item['keywords']]); ?>" target="_blank"> <p><?php echo $item['keywords']; ?></p></a>
                        </td>
                        <td>
                            <h5 class="mb-0 text-center"><?php echo $item['nums']; ?></h5>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='2' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>销量商品</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="80%">商品名称</th>
                        <th width="20%" class="text-center">销量</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($hotGoodsList) || $hotGoodsList instanceof \think\Collection || $hotGoodsList instanceof \think\Paginator): if( count($hotGoodsList)==0 ) : echo "<tr><td colspan='2' class='text-center'>暂无数据</td></tr>" ;else: foreach($hotGoodsList as $key=>$item): ?>
                    <tr>
                        <td>
                            <a href="<?php echo $item['url']; ?>" target="_blank"><p><?php echo $item['title']; ?></p></a>
                        </td>
                        <td>
                            <h5 class="mb-0 text-center"><?php echo $item['sales']; ?></h5>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='2' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>评价商品</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="80%">商品名称</th>
                        <th width="20%" class="text-center">评论数</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($comGoodsList) || $comGoodsList instanceof \think\Collection || $comGoodsList instanceof \think\Paginator): if( count($comGoodsList)==0 ) : echo "<tr><td colspan='2' class='text-center'>暂无数据</td></tr>" ;else: foreach($comGoodsList as $key=>$item): ?>
                    <tr>
                        <td>
                            <a href="<?php echo $item['url']; ?>" target="_blank"><p><?php echo $item['title']; ?></p></a>
                        </td>
                        <td>
                            <h5 class="mb-0 text-center"><?php echo $item['comments']; ?></h5>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='2' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<!-- 商品销售分类占比 -->
<div class="row" style="margin-top:15px;">
    <div class="col-xs-12">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div id="datefilter">
                    <form id="form4" action="" role="form" novalidate class="form-inline">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" data-chart="chart4">
                            <i class="fa fa-refresh"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Today'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Yesterday'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 7 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 30 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last month'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('This month'); ?></a>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <input type="text" class="form-control input-inline datetimerange" data-type="order" placeholder="指定日期" style="width:270px;">
                        </div>
                    </form>
                </div>
                <div id="echarts4" style="height:600px;width:95vw;margin-top:15px;"></div>
            </div>
        </div>
    </div>
</div>



                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version'] ?? ''); ?>"></script>


    </body>
</html>
