<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:85:"D:\test\water-purifier\website\public/../application/admin\view\shop\theme\index.html";i:1709960820;s:73:"D:\test\water-purifier\website\application\admin\view\layout\default.html";i:1710226298;s:70:"D:\test\water-purifier\website\application\admin\view\common\meta.html";i:1710223706;s:72:"D:\test\water-purifier\website\application\admin\view\common\script.html";i:1710223708;}*/ ?>
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
    .mockup-bg {
        background: url("/assets/addons/shop/img/statusbar.png") center 20px no-repeat;
        width: 400px;
        min-width: 400px;
        height: 800px;
        display: block;
        float: left;
        box-shadow: 0 4px 25px 0 rgba(4, 40, 60, .15);
        border-radius: 25px;
        padding: 44px 10px 10px 10px;
        margin-right: 30px;
    }

    .mockup-bg iframe {
        height: 100%;
        width: 100%;
    }

    #configbody .form-group {
        width: 50%;
        float: left;
    }

    #configbody .control-label {
        font-weight: normal;
        line-height: 31px;
    }

    #configbody h2 {
        clear: both;
        width: 100%;
        font-size: 14px;
        border-bottom: 1px solid #eee;
        padding: 15px 10px;
        margin-bottom: 15px;
    }

    #configbody h2:before {
        content: ' ';
        display: block;
        width: 3px;
        height: 15px;
        background: #0a8cd2;
        float: left;
        margin-right: 10px;
    }

    .tabbarlist {
        display: block;
        width: 100%;
        clear: both;
        margin-top: 10px;
    }

    .tabbarlist table {
        margin-bottom: 0px;
    }

    .tabbarlist table tr td img {
        cursor: pointer;
    }
</style>
<div class="panel panel-default panel-intro">
    <?php echo build_heading(); ?>

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="row">
                <div class="col-xs-12 col-sm-12 tab-content">
                    <div style="padding:20px;position:relative;min-height:820px;display:flex;">
                        <div class="mockup-bg">
                            <iframe scrolling="auto" frameborder="0" src="<?php echo (\think\Config::get('shop.mobileurl') ?: '/assets/addons/shop/preview.html'); ?>" class="iframe" id="previewiframe"></iframe>
                        </div>

                        <div class="" style="flex:1;">
                            <form id="config-form" action="" role="form" method="post">
                                <input type="hidden" name="preview" value="0" />
                                <div class="" id="configbody">
                                    <h2>主题配色</h2>
                                    <div class="" id="color">
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">常规颜色(文字):</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-theme-color" class="form-control" name="theme[color]" type="text" value="<?php echo $themeConfig['theme']['color']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">背景颜色(按钮、我的):</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-theme-bgColor" class="form-control" name="theme[bgColor]" type="text" value="<?php echo $themeConfig['theme']['bgColor']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h2>顶部导航栏</h2>
                                    <div class="" id="navbar">

                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">标题颜色:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-navbar-titleColor" class="form-control" name="navbar[titleColor]" type="text" value="<?php echo $themeConfig['navbar']['titleColor']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">背景颜色:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-navbar-bgColor-background" class="form-control" name="navbar[bgColor][background]" type="text" value="<?php echo $themeConfig['navbar']['bgColor']['background']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">返回按钮颜色:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-navbar-backIconColor" class="form-control" name="navbar[backIconColor]" type="text" value="<?php echo $themeConfig['navbar']['backIconColor']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">返回文字颜色:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-navbar-backTextStyle-color" class="form-control" name="navbar[backTextStyle][color]" type="text" value="<?php echo $themeConfig['navbar']['backTextStyle']['color']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">标题文字大小:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <input class="form-control" name="navbar[titleSize]" type="number" value="<?php echo $themeConfig['navbar']['titleSize']; ?>" style="width:120px;">
                                            </div>
                                        </div>
                                    </div>

                                    <h2>底部导航栏</h2>
                                    <div class="" id="tabbar">
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">文字颜色:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-tabbar-color" class="form-control" name="tabbar[color]" type="text" value="<?php echo $themeConfig['tabbar']['color']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">文字颜色(选中):</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-tabbar-selectColor" class="form-control" name="tabbar[selectColor]" type="text" value="<?php echo $themeConfig['tabbar']['selectColor']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">背景颜色:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <div class="input-group" style="width:120px;">
                                                    <input id="c-tabbar-bgColor" class="form-control" name="tabbar[bgColor]" type="text" value="<?php echo $themeConfig['tabbar']['bgColor']; ?>">
                                                    <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-color colorpicker" style="padding:0;margin-left:1px;"><img src="/assets/addons/shop/img/colorful.png" height="29" alt=""></button>
                                                <span class="msg-box n-right" for="c-title"></span>
                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">高度:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <input class="form-control" name="tabbar[height]" type="number" value="<?php echo $themeConfig['tabbar']['height']; ?>" style="width:120px;">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">顶部横线:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <input id="c-tabbar-borderTop" name="tabbar[borderTop]" type="hidden" value="<?php echo $themeConfig['tabbar']['borderTop']; ?>">
                                                <a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-tabbar-borderTop" data-yes="1" data-no="0">
                                                    <i class="fa fa-toggle-on text-success <?php if($themeConfig['tabbar']['borderTop'] == '0'): ?>fa-flip-horizontal text-gray<?php endif; ?> fa-2x"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">按钮大小:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <input class="form-control" name="tabbar[iconSize]" type="number" value="<?php echo $themeConfig['tabbar']['iconSize']; ?>" style="width:120px;">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">凸起中间按钮:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <input id="c-tabbar-midButton" name="tabbar[midButton]" type="hidden" value="<?php echo $themeConfig['tabbar']['midButton']; ?>">
                                                <a href="javascript:;" data-toggle="switcher" class="btn-switcher" data-input-id="c-tabbar-midButton" data-yes="1" data-no="0">
                                                    <i class="fa fa-toggle-on text-success <?php if($themeConfig['tabbar']['midButton'] == '0'): ?>fa-flip-horizontal text-gray<?php endif; ?> fa-2x"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4">中间按钮大小:</label>
                                            <div class="col-xs-12 col-sm-8">
                                                <input class="form-control" name="tabbar[midButtonSize]" type="number" value="<?php echo $themeConfig['tabbar']['midButtonSize']; ?>" style="width:120px;">
                                            </div>
                                        </div>
                                        <div class="form-group" style="width:100%;clear:both;">
                                            <label class="control-label col-xs-12 col-sm-2">按钮配置:</label>
                                            <div class="col-xs-12 col-sm-12">
                                                <div class="tabbarlist">
                                                    <table class="fieldlist table table-no-bordered" data-name="tabbar[list]" data-template="tabbarlisttpl" data-tag="tr">
                                                        <tr>
                                                            <td>图片</td>
                                                            <td>图片(选中)</td>
                                                            <td class="text-center">底部文字</td>
                                                            <td class="text-center">中间按钮</td>
                                                            <td>页面路径</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6"><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></td>
                                                        </tr>
                                                        <textarea name="tabbar[list]" class="form-control hide" cols="30" rows="5"><?php echo json_encode($themeConfig['tabbar']['list'] ?? ''); ?></textarea>
                                                    </table>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div style="display: block;width:100%;border-top:1px solid #eee;padding-top:15px;">
                                    <div class="form-group">
                                        <div class="col-xs-12 col-sm-12">
                                            <button type="button" class="btn btn-primary btn-preview" data-preview="1"><i class="fa fa-eye"></i> 预览</button>
                                            <button type="button" class="btn btn-success btn-save" data-preview="0"><i class="fa fa-check"></i> 保存</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!--@formatter:off-->
<script type="text/html" id="tabbarlisttpl">
    <tr class="tabbarlist-item">
        <td>
            <input type="hidden" name="<%=name%>[<%=index%>][image]" class="form-control tabbar-img-value" value="<%=row.image%>" id="c-<%=index%>-image" placeholder="" size="10"/>
            <img src="<%=row.image?row.image:'/assets/addons/shop/img/plus.png'%>" title="点击选择" class="fachoose tabbar-img-preview" width="32" alt="" id="fachoose-<%=index%>-image" data-input-id="c-<%=index%>-image" data-mimetype="image/*" data-multiple="false">
        </td>
        <td>
            <input type="hidden" name="<%=name%>[<%=index%>][selectedImage]" class="form-control tabbar-img-value" value="<%=row.selectedImage%>" id="c-<%=index%>-selectedImage" placeholder="名称"/>
            <img src="<%=row.selectedImage?row.selectedImage:'/assets/addons/shop/img/plus.png'%>" title="点击选择" class="fachoose tabbar-img-preview" width="32" alt="" id="fachoose-<%=index%>-selectedImage" data-input-id="c-<%=index%>-selectedImage" data-mimetype="image/*" data-multiple="false">
        </td>
        <td class="text-center"><input type="text" name="<%=name%>[<%=index%>][text]" class="form-control text-center" value="<%=row.text%>" placeholder="" style="width:80px;"/></td>
        <td class="text-center">
            <input id="c-<%=index%>-switch" class="c-tabbar-list-midbutton" name="<%=name%>[<%=index%>][midButton]" type="radio" <%=row.midButton?"checked":""%> value="1">
        </td>
        <td>
            <div class="input-group">
                <input type="text" name="<%=name%>[<%=index%>][path]" class="form-control" value="<%=row.path%>" placeholder="页面路径"/>
                <div class="input-group-btn">
                    <input type="button" value="选择" class="btn btn-info btn-select-page" />
                </div>
            </div>
        </td>
        <td>
            <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span>
            <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span>
        </td>
    </tr>
</script>
<!--@formatter:on-->

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version'] ?? ''); ?>"></script>


    </body>
</html>
