<?php if (!defined('THINK_PATH')) exit(); /*a:7:{s:89:"/www/wwwroot/cuijingshui.zwzdoac.cn/public/../application/admin/view/shop/goods/edit.html";i:1709979166;s:78:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/layout/default.html";i:1710226298;s:75:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/common/meta.html";i:1710223706;s:82:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/shop/goods/add_sku.html";i:1709960820;s:87:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/shop/goods/attributetpl.html";i:1709960820;s:83:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/shop/common/cardtpl.html";i:1709960820;s:77:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/common/script.html";i:1710223708;}*/ ?>
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
                                <form id="edit-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">


    <div class="panel panel-default panel-intro">
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-group">
                <li class="active"><a href="#basics" data-toggle="tab">基础信息</a></li>
                <li><a href="#skus" data-toggle="tab">规格信息</a></li>
            </ul>
        </div>
        <div class="panel-body">
            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="basics">
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Category'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-category_id" data-rule="required"
                                data-source="shop/category/index/noKeyField/100" class="form-control selectpage"
                                data-format-item="{spacer} {name}" name="row[category_id]" type="text"
                                value="<?php echo htmlentities($row['category_id'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('续费价格'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">

                            <div class="radio">
                                <?php if(is_array($typeList) || $typeList instanceof \think\Collection || $typeList instanceof \think\Paginator): if( count($typeList)==0 ) : echo "" ;else: foreach($typeList as $key=>$vo): ?>
                                <label for="row[goods_type]-<?php echo $key; ?>"><input id="row[goods_type]-<?php echo $key; ?>" name="row[goods_type]" type="radio" value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['goods_type'])?$row['goods_type']:explode(',',$row['goods_type']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                                <?php endforeach; endif; else: echo "" ;endif; ?>

                            </div>

                        </div>
                    </div>
                    <div class="form-group" id="renew" style="display: none;">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('续费价格'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <dl  class="fieldlist"  data-name="row[renew]">
                                <dd>
                                    <ins>年数</ins>
                                    <ins>价格</ins>
                                </dd>
                                <dd><a href="javascript:;" class="btn btn-sm btn-success btn-append">
                                    <i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>
                                <textarea name="row[renew]" cols="30" rows="5" class="hide"><?php echo htmlentities(json_encode($renew ?? '') ?? ''); ?></textarea>

                            </dl>
                        </div>
                    </div>
                    <div id="attributes"></div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Brand'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-brand_id" data-source="shop/brand/index" class="form-control selectpage"
                                name="row[brand_id]" type="text" value="<?php echo htmlentities($row['brand_id'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Title'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-title" data-rule="required" class="form-control" name="row[title]" type="text"
                                value="<?php echo htmlentities($row['title'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Goods_sn'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-goods_sn" class="form-control" name="row[goods_sn]" type="text"
                                value="<?php echo htmlentities($row['goods_sn'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Subtitle'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-subtitle" data-rule="" class="form-control" name="row[subtitle]" type="text"
                                value="<?php echo htmlentities($row['subtitle'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Keywords'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-keywords" class="form-control" name="row[keywords]" type="text"
                                value="<?php echo htmlentities($row['keywords'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Description'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <textarea id="c-description" class="form-control" cols="10" rows="5"
                                name="row[description]"><?php echo htmlentities($row['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Marketprice'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-marketprice" data-rule="required" class="form-control" step="0.01"
                                name="row[marketprice]" type="number" value="<?php echo htmlentities($row['marketprice'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Price'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-price" data-rule="required" class="form-control" step="0.01" name="row[price]"
                                type="number" value="<?php echo htmlentities($row['price'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Stocks'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-stocks" data-rule="required" class="form-control" <?php if($row['spectype']): ?>disabled<?php endif; ?> name="row[stocks]" type="number"
                                value="<?php echo htmlentities($row['stocks'] ?? ''); ?>">
                            <div class="alert alert-warning-light">
                                <i class="fa fa-info-circle"></i> 温馨提示<br>
                                多规格商品规格请在商品列表完成录入<br>
                                多规格商品库存值无需要修改，自动计算
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Image'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <div class="input-group">
                                <input id="c-image" data-rule="required" class="form-control" size="50"
                                    name="row[image]" type="text" value="<?php echo htmlentities($row['image'] ?? ''); ?>">
                                <div class="input-group-addon no-border no-padding">
                                    <span><button type="button" id="faupload-image" class="btn btn-danger faupload"
                                            data-input-id="c-image"
                                            data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp"
                                            data-multiple="false" data-preview-id="p-image"><i class="fa fa-upload"></i>
                                            <?php echo __('Upload'); ?></button></span>
                                    <span><button type="button" id="fachoose-image" class="btn btn-primary fachoose"
                                            data-input-id="c-image" data-mimetype="image/*" data-multiple="false"><i
                                                class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                                </div>
                                <span class="msg-box n-right" for="c-image"></span>
                            </div>
                            <ul class="row list-inline faupload-preview" id="p-image"></ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Images'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <div class="input-group">
                                <input id="c-images" data-rule="required" class="form-control" size="50"
                                    name="row[images]" type="text" value="<?php echo htmlentities($row['images'] ?? ''); ?>">
                                <div class="input-group-addon no-border no-padding">
                                    <span><button type="button" id="faupload-images" class="btn btn-danger faupload"
                                            data-input-id="c-images"
                                            data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp"
                                            data-multiple="true" data-preview-id="p-images"><i class="fa fa-upload"></i>
                                            <?php echo __('Upload'); ?></button></span>
                                    <span><button type="button" id="fachoose-images" class="btn btn-primary fachoose"
                                            data-input-id="c-images" data-mimetype="image/*" data-multiple="true"><i
                                                class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                                </div>
                                <span class="msg-box n-right" for="c-images"></span>
                            </div>
                            <ul class="row list-inline faupload-preview" id="p-images"></ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Content'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <textarea id="c-content" class="form-control editor" rows="5" name="row[content]"
                                cols="50"><?php echo htmlentities($row['content'] ?? ''); ?></textarea>
                            <div style="margin-top:5px;">
                                <a href="javascript:" class="btn btn-xs btn-success btn-card"><?php echo __('插入卡片'); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Corner'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-corner" class="form-control" name="row[corner]" type="text"
                                value="<?php echo htmlentities($row['corner'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Flag'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">

                            <select id="c-flag" class="form-control selectpicker" multiple="" name="row[flag][]">
                                <?php if(is_array($flagList) || $flagList instanceof \think\Collection || $flagList instanceof \think\Paginator): if( count($flagList)==0 ) : echo "" ;else: foreach($flagList as $key=>$vo): ?>
                                <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['flag'])?$row['flag']:explode(',',$row['flag']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Weight'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-weight" class="form-control" step="0.01" name="row[weight]" type="number"
                                value="<?php echo htmlentities($row['weight'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Freight'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-freight_id" data-rule="required" data-source="shop/freight/index"
                                class="form-control selectpage" name="row[freight_id]" type="text"
                                value="<?php echo htmlentities($row['freight_id'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Guarantee'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-guarantee_ids" data-source="shop/guarantee/index" data-multiple="true"
                                class="form-control selectpage" name="row[guarantee_ids]" type="text"
                                value="<?php echo htmlentities($row['guarantee_ids'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Weigh'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">
                            <input id="c-weigh" data-rule="required" class="form-control" name="row[weigh]"
                                type="number" value="<?php echo htmlentities($row['weigh'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Status'); ?>:</label>
                        <div class="col-xs-12 col-sm-8">

                            <div class="radio">
                                <?php if(is_array($statusList) || $statusList instanceof \think\Collection || $statusList instanceof \think\Paginator): if( count($statusList)==0 ) : echo "" ;else: foreach($statusList as $key=>$vo): ?>
                                <label for="row[status]-<?php echo $key; ?>"><input id="row[status]-<?php echo $key; ?>" name="row[status]"
                                        type="radio" value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['status'])?$row['status']:explode(',',$row['status']))): ?>checked<?php endif; ?> />
                                    <?php echo $vo; ?></label>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="skus">
                    <style>
    #vue-app .row .input-group {
        margin-bottom: 10px;
    }

    #vue-app .row .input-group .btn-remove {
        color: #fff;
        background-color: #e74c3c;
        cursor: pointer;
    }

    #vue-app .row .form-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #vue-app .row .form-group .right {
        flex: 1;
        padding-left: 15px;
    }

    #vue-app .row .form-group .control-label {
        width: 68px;
    }

    #vue-app .panel .panel-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table th {
        text-align: center;
    }

    .table .td-img {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .td-img .image-thumb {
        margin-right: 5px;
        width: 32px;
        height: 32px;
    }

    .td-img .image-thumb img {
        width: 100%;
        height: 100%;
    }

    .goods-sku-table {
        overflow-x: auto;
    }

    .goods-sku-table .multiple-edit {
        color: #18bc9c;
        cursor: pointer;
    }

    [v-clock] {
        display: none;
    }
</style>


<div id="vue-app" v-clock>
    <div class="row">
        <div class="col-xs-12 col-sm-4">
            <label class="">规格模板:</label>
            <div class="right spec-template input-group">
                <input data-source="shop/sku_template/index" id="template-id" data-primary-key="id" class="form-control selectpage" type="text">
                <div class="input-group-btn">
                    <a class="btn btn-success btn-dialog" data-area='["80%","80%"]' data-url="<?php echo url('shop/sku_template/add'); ?>" title="<?php echo __('添加规格模板'); ?>" style="width:120px">
                        <i class="fa fa-plus"></i> 添加规格模板
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- 规格名称录入 -->
    <div class="row">
        <div class="col-xs-12 col-sm-4">
            <label class="">规格名称:</label>
            <div class="right">
                <div class="input-group">
                    <input id="spec_names" class="form-control" type="text" v-model="spec_name">
                    <div class="input-group-btn">
                        <a @click="addSpec" class="btn btn-success" style="width:120px">
                            <i class="fa fa-plus"></i><?php echo __('添加规格'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default" v-for="(spec,key) in specList" :key="key">
        <div class="panel-heading" style="padding:5px 15px;">
            <span>{{spec.name}}</span>
            <span class="btn btn-remove" @click="removeSpec(key)">
                <i class="fa fa-close"></i>
            </span>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6" v-for="(val,index) in spec.value" :key="index">
                    <div class="input-group">
                        <input id="a" :id="'spec'+key+index" type="text" class="form-control" placeholder="" v-model="specList[key].value[index]">
                        <div class=" input-group-btn">
                            <span class="btn btn-remove" @click="removeSpecValue(key,index)">
                                <i class="fa fa-close"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                    <a href="javascript:;" class="btn btn-sm btn-success" @click="addSpecValue(key)"><i class="fa fa-plus"></i><?php echo __('Append'); ?></a>
                </div>
            </div>
        </div>
    </div>
    <!-- 规格表格展示 -->
    <div class="goods-sku-table">
        <table class="table table-striped table-bordered table-hover table-nowrap">
            <thead>
            <tr>
                <th>序号</th>
                <th v-for="(item,index) in specList" :key="index" style="text-align: center;">
                    {{item.name}}
                </th>
                <th>规格封面</th>
                <th>
                    货号
                    <span class="multiple-edit" :data-content="contentHtml('goods_sn')"><i class="fa fa-edit"></i>批量编辑</span>
                </th>
                <th>
                    市场价
                    <span class="multiple-edit" :data-content="contentHtml('marketprice')"><i class="fa fa-edit"></i>批量编辑</span>
                </th>
                <th>
                    价格
                    <span class="multiple-edit" :data-content="contentHtml('price')"><i class="fa fa-edit"></i>批量编辑</span>
                </th>
                <th>
                    库存数量
                    <span class="multiple-edit" :data-content="contentHtml('stocks')"><i class="fa fa-edit"></i>批量编辑</span>
                </th>
                <th>
                    销量
                </th>
                <th>
                    操作
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item,index) in tableData" :key="index">
                <th scope="row">{{index+1}}</th>
                <td v-for="(res,ik) in specList" :key="ik" style="text-align: center;">
                    {{specValueText(item.skus,ik)}}
                </td>
                <td class="td-img">
                    <div class="image-thumb" :id="'p-image-'+index" data-template="p-image-tpl">
                        <img src=""/>
                    </div>
                    <div class="input-group">
                        <input type="hidden" id="a" :id="'c-image-'+index" class="form-control sku-images" :data-index="index" :value="item.image?item.image:''"/>
                        <div class="input-group-addon no-border no-padding">
                                <span>
                                    <button :disabled="tableData[index].is_del" type="button" :id="'faupload-image-'+index" class="btn btn-danger faupload"
                                            :data-input-id="'c-image-'+index" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp"
                                            data-multiple="false" :data-preview-id="'p-image-'+index">
                                    <i class="fa fa-upload"></i>
                                        <?php echo __('Upload'); ?>
                                    </button>
                                </span>
                            <span>
                                    <button :disabled="tableData[index].is_del" type="button" :id="'fachoose-image-'+index" class="btn btn-primary fachoose"
                                            :data-input-id="'c-image-'+index" data-mimetype="image/*" data-multiple="false">
                                        <i class="fa fa-list"></i>
                                        <?php echo __('Choose'); ?>
                                    </button>
                                </span>
                        </div>
                    </div>
                </td>
                <td>
                    <input class="form-control" id="a" :id="'goods_sn'+index" :disabled="tableData[index].is_del" type="text" v-model="tableData[index].goods_sn">
                </td>
                <td>
                    <input class="form-control" id="a" :id="'marketprice'+index" :disabled="tableData[index].is_del" type="text" v-model="tableData[index].marketprice">
                </td>
                <td>
                    <input class="form-control" id="a" :id="'price'+index" :disabled="tableData[index].is_del" type="text" v-model="tableData[index].price">
                </td>
                <td>
                    <input class="form-control" id="a" :id="'stocks'+index" :disabled="tableData[index].is_del" type="text" v-model="tableData[index].stocks">
                </td>
                <td>
                    <div style="text-align: center;">
                        <span v-text="item.sales || 0"></span>
                    </div>
                </td>
                <td>
                    <a href="javascript:;" class="btn btn-xs btn-danger" v-if="!tableData[index].is_del" @click="del(index)"><i class="fa fa-trash"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-success" v-else @click="restore(index)"><i class="fa fa-mail-reply"></i></a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<script id="p-image-tpl" type="text/html">
    <a href="<%=fullurl%>" data-url="<%=url%>" target="_blank" class="">
        <img src="<%=fullurl%>"/>
    </a>
</script>


                </div>

            </div>
        </div>
    </div>

    <div id="goods-sku"></div>

    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-primary btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>

<script type="text/html" id="attributetpl">
    <% for(let item of row){ %>
        <% if (item.type == 'radio') { %>
        <div class="form-group">
            <label class="control-label col-xs-12 col-sm-2"><%=item.name%>:</label>
            <div class="col-xs-12 col-sm-8">
                <div class="radio">
                    <% for (let res of item.attribute_value) { %>
                        <label>
                            <input id="row[attribute_ids][<%=item.id%>][]-<%=res.id%>" data-rule="<%=item.is_must>0?'checked':''%>"
                            name="row[attribute_ids][<%=item.id%>][]" type="radio" value="<%=res.id%>" <%=attribute_ids.includes(res.id+'')?'checked':''%> />
                            <%=res.name%>
                        </label>
                    <% } %>
                </div>
            </div>
        </div>
        <% }else{ %>
        <div class="form-group">
            <label class="control-label col-xs-12 col-sm-2"><%=item.name%>:</label>
            <div class="col-xs-12 col-sm-8">
                <div class="checkbox">
                    <%  for (let [key, res] of item.attribute_value.entries()) { %>
                        <label>
                            <input id="row[attribute_ids][<%=item.id%>][]-<%=res.id%>" data-rule="<%=item.is_must>0?'checked':''%>"
                            name="row[attribute_ids][<%=item.id%>][]" type="checkbox" value="<%=res.id%>" <%=attribute_ids.includes(res.id+'')?'checked':''%> />
                            <%=res.name%>
                        </label>
                    <% } %>
                </div>
            </div>
        </div>
        <% } %>
    <% } %>
</script>

<style>
    .sp_result_area{
        z-index: 999 !important;
    }
</style>
<script type="text/html" id="tplselect">
    <form id="select-form" class="form-horizontal" style="padding: 15px 15px 0 15px;overflow: hidden;">
        <div class="form-group">
            <label for="c-template" class="control-label col-xs-12 col-sm-2"><?php echo __('选择模板'); ?>:</label>
            <div class="col-xs-12 col-sm-8 select-template">
                <input id="c-template" data-rule="required" data-field="title" data-source="shop/card/index" class="form-control selectpage" name="row[template]" type="text" value="">
            </div>
        </div>
        <div class="form-group">
            <label for="c-source_id" class="control-label col-xs-12 col-sm-2"><?php echo __('选择资源'); ?>:</label>
            <div class="col-xs-12 col-sm-8 c-select-goods">
                <input id="c-source_id" data-rule="required" data-field="title" data-source="shop/goods/index" class="form-control selectpage" name="row[source_id]" type="text" value="">
            </div>
        </div>
    </form>
</script>

<script type="text/html" id="goodstemplte">
    <input id="c-source_id" data-rule="required" data-field="title" data-source="shop/goods/index" class="form-control selectpage" name="row[source_id]" type="text" value="">
</script>

<script type="text/html" id="coupontemplte">
    <input id="c-source_id" data-rule="required" data-source="shop/coupon/index" class="form-control selectpage" name="row[source_id]" type="text" value="">
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
