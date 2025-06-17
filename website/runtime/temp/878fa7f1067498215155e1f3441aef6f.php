<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:96:"/www/wwwroot/cuijingshui.zwzdoac.cn/public/../application/admin/view/shop/template_msg/edit.html";i:1710489642;s:78:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/layout/default.html";i:1710226298;s:75:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/common/meta.html";i:1710223706;s:77:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/common/script.html";i:1710223708;}*/ ?>
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
    <div class="alert alert-warning-light">
        <b>温馨提示：</b><br>
        1、必须开启在站点配置中开启通知推送和启用相关服务后才会生效<br>
        2、公众号推送只支持公众号用户且用户必须关注公众号以后才能进行消息推送<br>
        3、小程序只支持小程序用户消息推送，且必须小程序用户主动订阅后才会进行推送<br>
        4、邮件推送必须在常规管理->系统配置中配置邮件的相关参数信息<br>
        5、短信通知推送只支持阿里云短信插件和华为云短信插件
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Type'); ?>:</label>
        <div class="col-xs-12 col-sm-8">

            <select  id="c-type" class="form-control selectpicker" name="row[type]">
                <?php if(is_array($typeList) || $typeList instanceof \think\Collection || $typeList instanceof \think\Paginator): if( count($typeList)==0 ) : echo "" ;else: foreach($typeList as $key=>$vo): ?>
                    <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($row['type'])?$row['type']:explode(',',$row['type']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('类型'); ?>:</label>
        <div class="col-xs-12 col-sm-8">

            <select  id="c-category" data-rule="required" class="form-control selectpicker" name="row[category]">
                <?php if(is_array($category) || $category instanceof \think\Collection || $category instanceof \think\Paginator): if( count($category)==0 ) : echo "" ;else: foreach($category as $key=>$vo): ?>
                <option value="<?php echo $key; ?>" <?php if(in_array(($key), explode(',',"goods_order"))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>

        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('事件名称'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-event" class="form-control" name="row[event]" type="text" value="<?php echo htmlentities($row['event'] ?? ''); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Title'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-title" class="form-control" name="row[title]" type="text" value="<?php echo htmlentities($row['title'] ?? ''); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Tpl_id'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-tpl_id" class="form-control" name="row[tpl_id]" type="text" value="<?php echo htmlentities($row['tpl_id'] ?? ''); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Content'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <dl class="fieldlist" data-template="content-tpl" data-name="row[content]" >
                <dd>
                    <ins><?php echo __('参数'); ?></ins>
                    <ins><?php echo __('默认值'); ?></ins>
                </dd>
                <dd><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>
                <!--请注意 dd和textarea间不能存在其它任何元素，实际开发中textarea应该添加个hidden进行隐藏-->
                <textarea name="row[content]" data-rule="required" class="form-control hide" cols="30" rows="5"><?php echo htmlentities($row['content'] ?? ''); ?></textarea>
            </dl>
            <script id="content-tpl" type="text/html">
                <dd class="form-inline">
                    <ins><input type="text" name="<%=name%>[<%=index%>][value]" class="form-control" value="<%=row.value%>" placeholder="内容"/></ins>
                    <ins><input type="text" name="<%=name%>[<%=index%>][def_val]" class="form-control" value="<%=row.def_val%>" placeholder="默认值"/></ins>
                    <!--下面的两个按钮务必保留-->
                    <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span>
                    <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span>
                </dd>
            </script>
        </div>
    </div>
    <div class="form-group template-extend <?php if(in_array($row['type'],[1,2,4])): ?>hide<?php endif; ?>">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Extend'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea name="row[extend]" class="form-control editor" id="c-extend" cols="30" rows="5"><?php echo htmlentities($row['extend'] ?? ''); ?></textarea>
        </div>
    </div>

    <div class="form-group page_path <?php echo in_array($row['type'],[3,4])?'hide':''; ?>">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Page'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-page" class="form-control" name="row[page]" type="text" placeholder="/pages/index/index" value="<?php echo htmlentities($row['page'] ?? ''); ?>">
                <div class="input-group-btn">
                    <a href="javascript:" class="btn btn-info btn-select-page">选择</a>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Switch'); ?>:</label>
        <div class="col-xs-12 col-sm-8">

            <input  id="c-switch" name="row[switch]" type="hidden" value="<?php echo $row['switch']; ?>">
            <a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-switch" data-yes="1" data-no="0" >
                <i class="fa fa-toggle-on text-success <?php if($row['switch'] == '0'): ?>fa-flip-horizontal text-gray<?php endif; ?> fa-2x"></i>
            </a>
        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-primary btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version'] ?? ''); ?>"></script>


    </body>
</html>
