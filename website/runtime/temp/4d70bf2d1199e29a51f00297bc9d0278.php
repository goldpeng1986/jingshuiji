<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:91:"/www/wwwroot/cuijingshui.zwzdoac.cn/public/../application/admin/view/shop/order/detail.html";i:1709960820;s:78:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/layout/default.html";i:1710226298;s:75:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/common/meta.html";i:1710223706;s:85:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/shop/order/invoicetpl.html";i:1709960820;s:77:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/common/script.html";i:1710223708;}*/ ?>
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
    .panel-fieldlist .fieldlist dd ins {
        width: 20%;
    }

    .panel-fieldlist .fieldlist dd input, .panel-fieldlist .fieldlist dd select {
        width: 100%;
    }

    .list-group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .groups {
        display: flex;
        align-items: center;
    }

    .title {
        font-weight: bold;
        font-size: 16px;
    }

    .list-group .list-group-item .label {
        padding: 5px 10px;
    }

    .table-goods > thead > tr > th, .table-goods > tbody > tr > th, .table-goods > thead > tr > td, .table-goods > tbody > tr > td {
        vertical-align: middle;
    }

    .order-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .print-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @media print {
        .electronics, .btn-print-page, .memo-operate {
            display: none;
        }
    }
</style>
<div id="app">
    <div class="panel panel-default">
        <div class="panel-heading">
            订单信息
            <a href="javascript:;" onclick="window.print();" class="btn btn-info btn-xs pull-right btn-print-page">
                <i class="fa fa-print"></i>
                打印
            </a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-6">
                    <ul class="list-group">
                        <button class="btn-refresh hidden"></button>
                        <input type="hidden" id="order_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" id="now-status" value="<?php echo $row['status']; ?>">
                        <input type="hidden" id="amount" value="<?php echo $row['amount']; ?>">
                        <input type="hidden" id="shippingfee" value="<?php echo $row['shippingfee']; ?>">
                        <li class="list-group-item">订单号：<?php echo $row['order_sn']; ?></li>
                        <!--@formatter:off-->
                        <li class="list-group-item order-status">
                            <div>
                                订单状态：
                                <?php if($row['orderstate']==0): if($row['paystate'] == 0): ?>
                                        <span class="label label-danger">未付款</span>
                                    <?php elseif($row['paystate'] == 1): if($row['shippingstate'] == 0): ?>
                                            <span class="label label-danger">待发货</span>
                                        <?php elseif($row['shippingstate'] == 1): ?>
                                            <span class="label label-danger">已发货</span>
                                        <?php elseif($row['shippingstate'] == 2): ?>
                                            <span class="label label-danger">待评价</span>
                                        <?php endif; endif; elseif($row['orderstate'] == 1): ?>
                                    <span class="label label-danger">已取消</span>
                                <?php elseif($row['orderstate'] == 2): ?>
                                    <span class="label label-danger">已失效</span>
                                <?php elseif($row['orderstate'] == 3): ?>
                                    <span class="label label-danger">已完成</span>
                                <?php elseif($row['orderstate'] == 4): ?>
                                    <span class="label label-danger">退货/退款中</span>
                                    <?php if($row['shippingstate'] == 0): ?>
                                        <span class="label label-danger">待发货</span>
                                    <?php elseif($row['shippingstate'] == 1): ?>
                                        <span class="label label-danger">已发货</span>
                                    <?php elseif($row['shippingstate'] == 2): ?>
                                        <span class="label label-danger">待评价</span>
                                    <?php endif; endif; ?>
                            </div>
                            <div>
                                <?php if($row['orderstate']==0 || $row['orderstate']==4): if($row['paystate'] == 0): ?>
                                        <button type="button" class="btn btn-success btn-status-click" data-order='{"orderstate":1}'>取消</button>
                                        <button type="button" class="btn btn-success btn-status-click" data-order='{"paystate":1}'>设为已支付</button>
                                    <?php elseif($row['paystate'] == 1): if($row['shippingstate'] == 0): ?>
                                            <button type="button" class="btn btn-success btn-deliver" data-expressname="<?php echo $row['expressname']; ?>" data-expressno="<?php echo $row['expressno']; ?>" data-type="0">发货</button>
                                        <?php elseif($row['shippingstate'] == 1): ?>
                                            <button type="button" class="btn btn-success btn-deliver" data-expressname="<?php echo $row['expressname']; ?>" data-expressno="<?php echo $row['expressno']; ?>" data-type="1">修改快递</button>
                                            <button type="button" class="btn btn-success btn-logistics" data-expressno="<?php echo $row['expressno']; ?>">查询物流</button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-success btn-status-click" data-order='{"orderstate":3}'>设为已完成</button>
                                    <?php endif; endif; ?>
                            </div>
                        </li>
                        <!--@formatter:on-->
                        <li class="list-group-item">下单用户：<?php echo $row['user']['nickname']; ?>（用户ID：<?php echo $row['user_id']; ?>）</li>
                        <li class="list-group-item">商品费用：￥<?php echo $row['goodsprice']; ?></li>
                        <li class="list-group-item">快递费用：￥<?php echo $row['shippingfee']; ?></li>
                        <li class="list-group-item">折扣金额：￥<?php echo $row['discount']; ?></li>
                        <li class="list-group-item">订单金额：￥<?php echo $row['amount']; ?></li>
                        <li class="list-group-item">应付金额：￥<?php echo $row['saleamount']; ?></li>
                        <li class="list-group-item">实付金额：￥<?php echo $row['payamount']; ?></li>
                        <li class="list-group-item">
                            <span style="width: 140px;">退款金额：￥<?php if(!empty($row['refund'])): ?> <?php echo $row['refund']; else: ?>0.00<?php endif; ?></span>
                            <span></span>
                        </li>
                    </ul>
                </div>
                <div class="col-xs-6">
                    <ul class="list-group">
                        <li class="list-group-item">下单时间：<?php echo date("Y-m-d H:i:s",$row['createtime']); ?></li>
                        <li class="list-group-item">支付方法：<?php echo $row['method']; ?></li>
                        <li class="list-group-item">支付类型：<?php echo $row['paytype']; ?></li>
                        <li class="list-group-item">交易流水号：<?php echo $row['transactionid']; ?></li>
                        <li class="list-group-item">支付时间：<?php if(!(empty($row['paytime']) || (($row['paytime'] instanceof \think\Collection || $row['paytime'] instanceof \think\Paginator ) && $row['paytime']->isEmpty()))): ?><?php echo date("Y-m-d H:i:s",$row['paytime']); endif; ?></li>
                        <li class="list-group-item">退货时间：<?php if(!(empty($row['refundtime']) || (($row['refundtime'] instanceof \think\Collection || $row['refundtime'] instanceof \think\Paginator ) && $row['refundtime']->isEmpty()))): ?><?php echo date("Y-m-d H:i:s",$row['refundtime']); endif; ?></li>
                        <li class="list-group-item">发货时间：<?php if(!(empty($row['shippingtime']) || (($row['shippingtime'] instanceof \think\Collection || $row['shippingtime'] instanceof \think\Paginator ) && $row['shippingtime']->isEmpty()))): ?><?php echo date("Y-m-d H:i:s",$row['shippingtime']); endif; ?></li>
                        <li class="list-group-item">收货时间：<?php if(!(empty($row['receivetime']) || (($row['receivetime'] instanceof \think\Collection || $row['receivetime'] instanceof \think\Paginator ) && $row['receivetime']->isEmpty()))): ?><?php echo date("Y-m-d H:i:s",$row['receivetime']); endif; ?></li>
                        <li class="list-group-item">取消时间：<?php if(!(empty($row['canceltime']) || (($row['canceltime'] instanceof \think\Collection || $row['canceltime'] instanceof \think\Paginator ) && $row['canceltime']->isEmpty()))): ?><?php echo date("Y-m-d H:i:s",$row['canceltime']); endif; ?></li>
                        <li class="list-group-item">
                            <div class="groups">
                                <span style="min-width: 75px;">备注信息：</span>
                                <div><?php echo $row['memo']; ?></div>
                                <input class="form-control hide" type="text" value="<?php echo $row['memo']; ?>">
                            </div>
                            <div class="memo-operate">
                                <a class="btn btn-success btn-edit" data-status="edit" href="javascript:;">编辑</a>
                                <a class="btn btn-success hide btn-cancel" data-status="cancel" href="javascript:;">取消</a>
                                <a class="btn btn-success hide btn-save" data-status="save" data-field="memo" data-id="<?php echo $row['id']; ?>" href="javascript:;">保存</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--  -->
    <div class="panel panel-default">
        <div class="panel-heading print-flex">
            <span>收货(快递)信息</span>
            <div class="electronics print-flex">
                <input class="form-control selectpage" data-field="title" data-source="shop/electronics_order/index" id="electronics_id" placeholder="请选择电子面单模板">
                <a href="javascript:;" class="btn btn-info btn-print-electronic" style="flex: 1;margin-left: 10px;" data-id="<?php echo $row['id']; ?>">
                    <i class="fa fa-print"></i>
                    打印快递单
                </a>
                <a href="javascript:;" class="btn btn-primary btn-print-invoice" style="flex: 1;margin-left: 10px;" data-id="<?php echo $row['id']; ?>">
                    <i class="fa fa-print"></i>
                    打印发货单
                </a>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-6">
                    <p>收货人的姓名：<?php echo $row['receiver']; ?></p>
                    <p>收货人的手机：<?php echo $row['mobile']; ?></p>
                    <p>收货人的地址：<?php echo $row['address']; ?></p>
                </div>
                <div class="col-xs-6">
                    <p>发货快递名称：<?php echo $row['expressname']; ?></p>
                    <p>发货快递单号：<?php echo $row['expressno']; ?></p>
                </div>
            </div>
        </div>
    </div>
    <!-- 商品信息 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <span>商品信息</span>
        </div>
        <div class="panel-body">
            <div class="goods">
                <table class="table table-bordered table-goods">
                    <thead>
                    <tr>
                        <th class="text-center">商品ID</th>
                        <th class="text-center">货号</th>
                        <th>名称</th>
                        <th class="text-center">图片</th>
                        <th class="text-center">属性</th>
                        <th class="text-center">市场价</th>
                        <th class="text-center">商城价</th>
                        <th class="text-center">数量</th>
                        <th class="text-center">实际价</th>
                        <th class="text-center">售后状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($row['order_goods']) || $row['order_goods'] instanceof \think\Collection || $row['order_goods'] instanceof \think\Paginator): if( count($row['order_goods'])==0 ) : echo "" ;else: foreach($row['order_goods'] as $key=>$item): ?>
                    <tr>
                        <th class="text-center" scope="row"><?php echo $item['goods_id']; ?></th>
                        <th class="text-center"><?php echo $item['goods_sn']; ?></th>
                        <td><?php echo $item['title']; ?></td>
                        <td class="text-center">
                            <img src="<?php echo $item['image']; ?>" class="img-sm" alt="">
                        </td>
                        <td class="text-center"><?php echo $item['attrdata']; ?></td>
                        <td class="text-center"><?php echo $item['marketprice']; ?></td>
                        <td class="text-center"><?php echo $item['price']; ?></td>
                        <td class="text-center"><?php echo $item['nums']; ?></td>
                        <td class="text-center"><?php echo $item['saleprice']; ?></td>
                        <td class="text-center">
                            <div style="display: flex;justify-content: center;align-items: center;">
                                <div>
                                    <?php echo $item['salestate_text']; ?>
                                </div>
                                <?php if($item['salestate'] == 1 || $item['salestate'] == 2 || $item['salestate'] == 3): ?>
                                <div style="padding-left: 10px;">
                                    <a class="btn btn-success btn-dialog <?php echo $auth->check('shop/order_aftersales/index')?'':'hide'; ?>" href="javascript:;" data-url="shop/order_aftersales/edit/order_goods_id/<?php echo $item['id']; ?>" title="审核售后">审核售后</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <span>订单操作日志</span>
    </div>
    <div class="panel-body">
        <table class="table table-bordered table-goods">
            <thead>
            <tr>
                <th>ID</th>
                <th>操作人</th>
                <th>操作记录</th>
                <th>操作时间</th>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($row['order_action']) || $row['order_action'] instanceof \think\Collection || $row['order_action'] instanceof \think\Paginator): if( count($row['order_action'])==0 ) : echo "" ;else: foreach($row['order_action'] as $key=>$item): ?>
            <tr>
                <th scope="row"><?php echo $key+1; ?></th>
                <td><?php echo $item['operator']; ?></td>
                <td><?php echo $item['memo']; ?></td>
                <td><?php echo date("Y-m-d H:i:s",$item['createtime']); ?></td>
            </tr>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/html" id="expresstpl">
    <div style="padding:15px 15px 0 15px;">
        <ul class="list-group">
            <li class="list-group-item shipper">
                <label class="control-label" style="width:73px;">快递名称：</label>
                <input class="form-control selectpage" data-primary-key="name" data-source="shop/shipper/index" style="flex:1;" id="expressname" value="<%=expressname%>"/>
            </li>
            <li class="list-group-item">
                <label class="control-label" style="width:73px;">快递单号：</label>
                <input class="form-control" style="flex:1;" id="expressno" value="<%=expressno%>"/>
            </li>
        </ul>
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
