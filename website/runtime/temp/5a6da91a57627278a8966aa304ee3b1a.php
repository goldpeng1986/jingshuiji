<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:95:"/www/wwwroot/jingshui.sddianfeng.cn/public/../application/admin/view/shop/sku_template/add.html";i:1709960820;s:78:"/www/wwwroot/jingshui.sddianfeng.cn/application/admin/view/layout/default.html";i:1710226298;s:75:"/www/wwwroot/jingshui.sddianfeng.cn/application/admin/view/common/meta.html";i:1710223706;s:77:"/www/wwwroot/jingshui.sddianfeng.cn/application/admin/view/common/script.html";i:1710223708;}*/ ?>
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
    .row .input-group {
        margin-bottom: 10px;
    }

    .row .input-group .btn-remove {
        color: #fff;
        background-color: #e74c3c;
        cursor: pointer;
    }

    .row .spec_name {
        display: flex;
        justify-content: space-between;
    }

    .row .form-group .right {
        flex: 1;
        padding-left: 15px;
    }
    .form-horizontal .form-group{
        margin-left: 0px !important;
        margin-right: 0px !important;
    }
    .panel .panel-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    [v-clock] {
        display: none;
    }
</style>

<div id="edit-form" class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Name'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" class="form-control" name="row[name]" type="text">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('规格属性'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div id="app" v-clock>
                <!-- 规格名称录入 -->
                <div class="row">
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group spec_name">
                            <label class="control-label">规格名称:</label>
                            <div class="right">
                                <div class="input-group">
                                    <input class="form-control" type="text" v-model="spec_name">
                                    <div class="input-group-btn">
                                        <button @click="addSpec" class="btn btn-success">
                                            <i class="fa fa-plus"></i>
                                            <?php echo __('添加规格'); ?>
                                        </button>
                                    </div>
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
                            <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" v-for="(val,index) in spec.value"
                                 :key="index">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder=""
                                           v-model="specList[key].value[index]">
                                    <div class=" input-group-btn">
                                        <span class="btn btn-remove" @click="removeSpecValue(key,index)">
                                            <i class="fa fa-close"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-3 col-xs-4">
                                <a href="javascript:;" class="btn btn-sm btn-success" @click="addSpecValue(key)"><i
                                        class="fa fa-plus"></i>
                                    <?php echo __('Append'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed btn-add-sku"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
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
