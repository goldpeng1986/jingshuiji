<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:90:"D:\test\water-purifier\website\public/../application/admin\view\shop\reneworder\index.html";i:1710387664;s:73:"D:\test\water-purifier\website\application\admin\view\layout\default.html";i:1710226298;s:70:"D:\test\water-purifier\website\application\admin\view\common\meta.html";i:1710223706;s:80:"D:\test\water-purifier\website\application\admin\view\shop\order\invoicetpl.html";i:1709960820;s:72:"D:\test\water-purifier\website\application\admin\view\common\script.html";i:1710223708;}*/ ?>
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
                                <div class="panel panel-default panel-intro">

    <div class="panel-heading">
        <?php echo build_heading(null,FALSE); ?>
        <ul class="nav nav-tabs">
            <li class="active"><a href="#t-all" data-value='' data-toggle="tab"><?php echo __('All'); ?></a></li>
            <li class=""><a href="#t-1" data-value='{"orderstate":0,"paystate":1,"install_status":1}' data-toggle="tab">待确认</a></li>
            <li class=""><a href="#t-1" data-value='{"orderstate":0,"paystate":1,"install_status":2}' data-toggle="tab">待安装</a></li>
            <li class=""><a href="#t-1" data-value='{"orderstate":0,"paystate":0}' data-toggle="tab">待付款</a></li>

            <li class=""><a href="#t-1" data-value='{"orderstate":1}' data-toggle="tab">已取消</a></li>
            <li class=""><a href="#t-1" data-value='{"shippingstate":2,"orderstate":0}' data-toggle="tab">待评价</a></li>
            <li class=""><a href="#t-1" data-value='{"orderstate":3}' data-toggle="tab">已完成</a></li>
            <li class=""><a href="#t-1" data-value='{"orderstate":4}' data-toggle="tab">退货/退款中</a></li>

        </ul>
    </div>


    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" title="<?php echo __('Refresh'); ?>"><i class="fa fa-refresh"></i> </a>
                        <a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled <?php echo $auth->check('shop/order/del')?'':'hide'; ?>" title="<?php echo __('Delete'); ?>"><i class="fa fa-trash"></i> <?php echo __('Delete'); ?></a>

                        <a class="btn btn-success btn-recyclebin btn-dialog <?php echo $auth->check('shop/order/recyclebin')?'':'hide'; ?>" href="shop/order/recyclebin" title="<?php echo __('Recycle bin'); ?>"><i class="fa fa-recycle"></i> <?php echo __('Recycle bin'); ?></a>
                        <a class="btn btn-info btn-print-multiple-electronic" href="javascript:;" title="<?php echo __('批量打印快递电子面单'); ?>"><i class="fa fa-print"></i> <?php echo __('批量打印快递电子面单'); ?></a>
                        <a class="btn btn-primary btn-print-multiple-invoice" href="javascript:;" title="<?php echo __('批量打印发货单'); ?>"><i class="fa fa-print"></i> <?php echo __('批量打印发货单'); ?></a>

                    </div>
                    <table id="table" class="table table-striped table-bordered table-hover table-nowrap"
                           data-operate-edit="<?php echo $auth->check('shop/order/edit'); ?>"
                           data-operate-del="<?php echo $auth->check('shop/order/del'); ?>"
                           width="100%">
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script type="text/html" id="electronictpl">
    <div style="min-width:300px;">
        <input class="form-control selectpage" data-field="title" data-source="shop/electronics_order/index" id="electronics_id" placeholder="请选择电子面单模板">
    </div>
</script>

<script type="text/html" id="invoicetpl">
    <%for(var i=0;i < orderList.length;i++){%>
    <%var order=orderList[i];%>
    <div style="padding:0px;page-break-after: always;">
        <h3 style="padding: 8px 5px;">发货单</h3>
        <table style="width:100%;font-size:14px;" class="baseinfo">
            <tr>
                <td style="width:50%">
                    <div>订单号：<%=order.order_sn%></div>
                </td>
                <td>
                    <div>用户：<%=order.user && order.user.nickname%></div>
                </td>
            </tr>
            <tr>
                <td style="width:50%">地址：<%=order.address%></td>
                <td>总件数：<%=order.nums%></td>
            </tr>
        </table>
        <table style="width:100%;font-size:14px;" class="deliverytable">
            <thead>
            <tr>
                <th>商品ID</th>
                <th>
                    <div style="width:50px;">货号</div>
                </th>
                <th style="text-align: left;">
                    <div style="width:220px;">名称</div>
                </th>
                <th>
                    <div style="width:100px;">属性</div>
                </th>
                <th>市场价</th>
                <th>商城价</th>
                <th>数量</th>
            </tr>
            </thead>
            <tbody>
            <%for(var j=0;j< order.order_goods.length;j++){%>
            <%var goods=order.order_goods[j];%>
            <tr>
                <td><%=goods.goods_id%></td>
                <td>
                    <div style="width:50px;"><%=goods.goods_sn || '无'%></div>
                </td>
                <td style="text-align: left;">
                    <div style="width:220px;"><%=goods.title%></div>
                </td>
                <td>
                    <div style="width:100px;"><%=goods.attrdata || '无'%></div>
                </td>
                <td><%=goods.price%></td>
                <td><%=goods.marketprice%></td>
                <td><%=goods.nums%></td>
            </tr>
            <%}%>
            </tbody>
        </table>
    </div>
    <style>
        .baseinfo tr td {
            padding: 6px;
        }

        .baseinfo {
            margin-bottom: 15px;
        }

        .deliverytable tr th {
            background: #f7f7f7;
        }

        .deliverytable tr td {
            border-bottom: 1px solid #eee;
        }

        .deliverytable tr td, .deliverytable tr th {
            padding: 8px 5px;
            text-align: center;
        }
    </style>
    <%}%>
</script>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version'] ?? ''); ?>"></script>


    </body>
</html>
