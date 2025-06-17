<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:59:"/www/wwwroot/cuijingshui.zwzdoac.cn/addons/shop/config.html";i:1710742421;s:78:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/layout/default.html";i:1710226298;s:75:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/common/meta.html";i:1710223706;s:77:"/www/wwwroot/cuijingshui.zwzdoac.cn/application/admin/view/common/script.html";i:1710223708;}*/ ?>
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
    #config-form table tr td {
        border: none;
        padding: 10px 8px;
    }

    #config-form div a.btn {
        color: #fff;
        text-decoration: none;
    }

    #config-form table.table-notice {
        margin: 0 -8px;
        width: 100%;
    }

    #config-form table.table-notice tr td {
        padding: 5px 8px;
        width: 50%;
    }
    #config-form table.table-notice tr td input:first-child {
        width: 100%;
    }

</style>
<!--@formatter:off-->
<?php 
$groupList = [
    'config'=>'sitelogo,notice,sitename,title,keywords,description,theme,domain,audittype,nlptype,aip_appid,aip_apikey,aip_secretkey,apikey,autolinks,cachelifetime,flagtype,freightitemfee',
    'order'=>'order_timeout,order_refund_sync,auto_delivery_time,sendnoticemode',
    'wechat'=>'qrcode,wxapp,wx_appid,wx_app_secret,mp_appid,mp_app_secret,app_id,app_secret',
    'express'=>'EBusinessID,kdNiaoApiKey,logisticstype,api_mode',
    'rewrite'=>'domain,rewrite,urlsuffix,moduleurlsuffix',
    'dealer'=>'is_dealer,poster,first_money,second_money,dealer_rule,withdraw_min,withdraw_rate',
    'other'=>'category_mode,phone,coupon_key,renew_time',
];
$group = [];
foreach($groupList as $k=>$v){
   $item = explode(',', $v);
   $item = array_flip($item);
   $group = array_merge($group, array_map(function($value) use($k){return $k;}, $item));
}
$epayInfo = get_addon_info('epay');
$thirdInfo = get_addon_info('third');
$areaInfo = \app\admin\model\shop\Area::where('id', '>', 0)->find();
 ?>


<form id="config-form" class="edit-form form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <div class="alert <?php echo (isset($addon['tips']['extend']) && ($addon['tips']['extend'] !== '')?$addon['tips']['extend']:'alert-info-light'); ?>" style="margin-bottom:10px;">
        <a  class="btn btn-info" target="_blank"><i class="fa fa-user"></i>配置管理</a>
    </div>


    <div class="panel panel-default panel-intro">
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-group">
                <li class="active"><a href="#all" data-toggle="tab">全部</a></li>
                <li><a href="#config" data-toggle="tab">基础配置</a></li>
                <li><a href="#order" data-toggle="tab">订单配置</a></li>
                <li><a href="#wechat" data-toggle="tab">微信配置</a></li>
                <li><a href="#express" data-toggle="tab">快递配置</a></li>
<!--                <li><a href="#rewrite" data-toggle="tab">伪静态</a></li>-->
                <li><a href="#dealer" data-toggle="tab">分销配置</a></li>
                <li><a href="#other" data-toggle="tab">其它</a></li>
<!--                <li class="pull-right"><a href="<?php echo url('shop/ajax/config?name=signin'); ?>" title="签到配置" class="dialogit">签到配置</a></li>-->
<!--                <li class="pull-right"><a href="<?php echo url('shop/ajax/config?name=sms'); ?>" title="短信配置" class="dialogit">短信配置</a></li>-->
<!--                <li class="pull-right"><a href="<?php echo url('shop/ajax/config?name=third'); ?>" title="登录配置" class="dialogit">登录配置</a></li>-->
<!--                <li class="pull-right"><a href="<?php echo url('shop/ajax/config?name=oss'); ?>" title="云存储配置" class="dialogit">云存储配置</a></li>-->
<!--                <li class="pull-right"><a href="<?php echo url('shop/ajax/config?name=epay'); ?>" title="支付配置" class="dialogit">支付配置</a></li>-->
            </ul>
        </div>

            <div class="panel-body">
                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade active in" id="one">
                        <!--@formatter:off-->
                        <table class="table table-config">
                            <tbody>
                            <?php foreach($addon['config'] as $item): ?>
                            <tr data-type="<?php echo isset($group[$item['name']])?$group[$item['name']]:'other'; ?>">
                                <td width="15%">
                                    <?php echo $item['title']; ?>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-sm-8 col-xs-12">
                                            <?php switch($item['type']): case "string": ?>
                                            <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value'] ?? ''); ?>" class="form-control" data-rule="<?php echo $item['rule']; ?>" data-tip="<?php echo $item['tip']; ?>"/>
                                            <?php break; case "text": ?>
                                            <textarea <?php echo $item['extend']; ?> name="row[<?php echo $item['name']; ?>]" class="form-control" data-rule="<?php echo $item['rule']; ?>" rows="5" data-tip="<?php echo $item['tip']; ?>"><?php echo htmlentities($item['value'] ?? ''); ?></textarea>
                                            <?php break; case "array": if($item['name'] == 'notice'): ?>
                                                <table class="fieldlist table-notice" data-name="row[<?php echo $item['name']; ?>]" data-template="<?php echo $item['name']; ?>tpl" data-tag="tr">
                                                    <tr data-type="<?php echo isset($group[$item['name']])?$group[$item['name']]:'other'; ?>">
                                                        <td>标题</td>
                                                        <td>路径</td>
                                                    </tr>
                                                    <tr data-type="<?php echo isset($group[$item['name']])?$group[$item['name']]:'other'; ?>">
                                                        <td colspan="2">
                                                            <a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a>
                                                            <textarea name="row[<?php echo $item['name']; ?>]" cols="30" rows="5" class="hide"><?php echo htmlentities(json_encode($item['value'] ?? '') ?? ''); ?></textarea>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <script type="text/html" id="<?php echo $item['name']; ?>tpl">
                                                    <tr class="form-inline" data-type="<?php echo isset($group[$item['name']])?$group[$item['name']]:'other'; ?>">
                                                        <td>
                                                        <input type="text" name="<%=name%>[<%=index%>][title]" class="form-control" value="<%=row?row['title']:''%>" size="60" />
                                                        </td>
                                                        <td>
                                                        <div class="input-group">
                                                            <input type="text" name="<%=name%>[<%=index%>][path]" class="form-control" value="<%=row?row['path']:''%>" />
                                                            <div class="input-group-btn">
                                                                <input type="button" value="选择" class="btn btn-info btn-select-page" style="width:50px;" />
                                                            </div>
                                                        </div>
                                                        <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span>
                                                        <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span>
                                                        </td>

                                                    </tr>
                                                </script>
                                            <?php else: ?>
                                                <dl class="fieldlist" data-name="row[<?php echo $item['name']; ?>]">
                                                    <dd>
                                                        <ins><?php echo __('Array key'); ?></ins>
                                                        <ins><?php echo __('Array value'); ?></ins>
                                                    </dd>
                                                    <dd><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>
                                                    <textarea name="row[<?php echo $item['name']; ?>]" cols="30" rows="5" class="hide"><?php echo htmlentities(json_encode($item['value'] ?? '') ?? ''); ?></textarea>
                                                </dl>
                                            <?php endif; break; case "date": ?>
                                            <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value'] ?? ''); ?>" class="form-control datetimepicker" data-date-format="YYYY-MM-DD" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                            <?php break; case "time": ?>
                                            <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value'] ?? ''); ?>" class="form-control datetimepicker" data-date-format="HH:mm:ss" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                            <?php break; case "datetime": ?>
                                            <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value'] ?? ''); ?>" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                            <?php break; case "number": ?>
                                            <input <?php echo $item['extend']; ?> type="number" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value'] ?? ''); ?>" class="form-control" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                            <?php break; case "checkbox": if(is_array($item['content']) || $item['content'] instanceof \think\Collection || $item['content'] instanceof \think\Paginator): if( count($item['content'])==0 ) : echo "" ;else: foreach($item['content'] as $key=>$vo): ?>
                                            <label for="row[<?php echo $item['name']; ?>][]-<?php echo $key; ?>"><input id="row[<?php echo $item['name']; ?>][]-<?php echo $key; ?>" name="row[<?php echo $item['name']; ?>][]" type="checkbox" value="<?php echo $key; ?>" data-tip="<?php echo $item['tip']; ?>" <?php if(in_array(($key), is_array($item['value'])?$item['value']:explode(',',$item['value']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                                            <?php endforeach; endif; else: echo "" ;endif; break; case "radio": if(is_array($item['content']) || $item['content'] instanceof \think\Collection || $item['content'] instanceof \think\Paginator): if( count($item['content'])==0 ) : echo "" ;else: foreach($item['content'] as $key=>$vo): ?>
                                            <label for="row[<?php echo $item['name']; ?>]-<?php echo $key; ?>"><input id="row[<?php echo $item['name']; ?>]-<?php echo $key; ?>" name="row[<?php echo $item['name']; ?>]" type="radio" value="<?php echo $key; ?>" data-tip="<?php echo $item['tip']; ?>" <?php if(in_array(($key), is_array($item['value'])?$item['value']:explode(',',$item['value']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                                            <?php endforeach; endif; else: echo "" ;endif; break; case "select": case "selects": ?>
                                            <select <?php echo $item['extend']; ?> name="row[<?php echo $item['name']; ?>]<?php echo $item['type']=='selects'?'[]':''; ?>" class="form-control selectpicker" data-tip="<?php echo $item['tip']; ?>" <?php echo $item['type']=='selects'?'multiple':''; ?>>
                                                <?php if(is_array($item['content']) || $item['content'] instanceof \think\Collection || $item['content'] instanceof \think\Paginator): if( count($item['content'])==0 ) : echo "" ;else: foreach($item['content'] as $key=>$vo): ?>
                                                <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($item['value'])?$item['value']:explode(',',$item['value']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                            </select>
                                            <?php break; case "image": case "images": ?>
                                            <div class="form-inline">
                                                <input id="c-<?php echo $item['name']; ?>" class="form-control" size="35" name="row[<?php echo $item['name']; ?>]" type="text" value="<?php echo htmlentities($item['value'] ?? ''); ?>" data-tip="<?php echo $item['tip']; ?>">
                                                <span><button type="button" id="faupload-<?php echo $item['name']; ?>" class="btn btn-danger faupload" data-input-id="c-<?php echo $item['name']; ?>" data-mimetype="image/*" data-multiple="<?php echo $item['type']=='image'?'false':'true'; ?>" data-preview-id="p-<?php echo $item['name']; ?>"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                                                <span><button type="button" id="fachoose-<?php echo $item['name']; ?>" class="btn btn-primary fachoose" data-input-id="c-<?php echo $item['name']; ?>" data-mimetype="image/*" data-multiple="<?php echo $item['type']=='image'?'false':'true'; ?>"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                                                <ul class="row list-inline faupload-preview" id="p-<?php echo $item['name']; ?>"></ul>
                                            </div>
                                            <?php break; case "file": case "files": ?>
                                            <div class="form-inline">
                                                <input id="c-<?php echo $item['name']; ?>" class="form-control" size="35" name="row[<?php echo $item['name']; ?>]" type="text" value="<?php echo htmlentities($item['value'] ?? ''); ?>" data-tip="<?php echo $item['tip']; ?>">
                                                <span><button type="button" id="faupload-<?php echo $item['name']; ?>" class="btn btn-danger faupload" data-input-id="c-<?php echo $item['name']; ?>" data-multiple="<?php echo $item['type']=='file'?'false':'true'; ?>"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                                                <span><button type="button" id="fachoose-<?php echo $item['name']; ?>" class="btn btn-primary fachoose" data-input-id="c-<?php echo $item['name']; ?>" data-multiple="<?php echo $item['type']=='file'?'false':'true'; ?>"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                                            </div>
                                            <?php break; case "gallery": $galleryImages = array_map(function($arr){return $arr['image'];}, (array)json_decode($item['value'], true)); ?>
                                            <div class="input-group">
                                                <input id="c-<?php echo $item['name']; ?>" data-rule="required" class="form-control gallery-control hidden" size="50" name="row[<?php echo $item['name']; ?>]" type="text" value="<?php echo implode(',',$galleryImages); ?>">
                                                <div class="input-group-addon no-border no-padding pull-left">
                                                    <span><button type="button" id="plupload-<?php echo $item['name']; ?>" class="btn btn-danger plupload" data-input-id="c-<?php echo $item['name']; ?>" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp" data-multiple="true" data-preview-id="p-<?php echo $item['name']; ?>"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                                                    <span><button type="button" id="fachoose-<?php echo $item['name']; ?>" class="btn btn-primary fachoose" data-input-id="c-<?php echo $item['name']; ?>" data-mimetype="image/*" data-multiple="true"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                                                </div>
                                                <span class="msg-box n-right" for="c-<?php echo $item['name']; ?>"></span>
                                            </div>

                                            <ul class="row list-inline plupload-preview" id="p-<?php echo $item['name']; ?>" data-template="<?php echo $item['name']; ?>tpl" data-name="row[<?php echo $item['name']; ?>]"></ul>
                                            <textarea name="row[<?php echo $item['name']; ?>]" class="form-control hidden" style="margin-top:5px;"><?php echo htmlentities($item['value'] ?? ''); ?></textarea>

                                            <script type="text/html" id="<?php echo $item['name']; ?>tpl">
                                                <li class="col-xs-4">
                                                    <a href="<%=fullurl%>" data-url="<%=url%>" target="_blank" class="thumbnail">
                                                        <img src="<%=fullurl%>" class="img-responsive">
                                                    </a>
                                                    <input type="hidden" name="row[<?php echo $item['name']; ?>][<%=index%>][image]" class="form-control" placeholder="" value="<%=fullurl%>"/>
                                                    <input type="text" name="row[<?php echo $item['name']; ?>][<%=index%>][title]" class="form-control" placeholder="请输入标题" value="<%=value?value['title']:''%>"/>
                                                    <div class="input-group" style="margin:5px 0;">
                                                        <input type="text" name="row[<?php echo $item['name']; ?>][<%=index%>][path]" class="form-control" placeholder="请输入路径" value="<%=value?value['path']:''%>"/>
                                                        <div class="input-group-btn">
                                                            <input type="button" value="选择" class="btn btn-info btn-select-page" />
                                                        </div>
                                                    </div>

                                                    <a href="javascript:;" class="btn btn-danger btn-xs btn-trash"><i class="fa fa-trash"></i></a>
                                                </li>
                                            </script>
                                            <?php break; case "bool": ?>
                                            <label for="row[<?php echo $item['name']; ?>]-yes"><input id="row[<?php echo $item['name']; ?>]-yes" name="row[<?php echo $item['name']; ?>]" type="radio" value="1" <?php echo !empty($item['value'])?'checked':''; ?> data-tip="<?php echo $item['tip']; ?>" /> <?php echo __('Yes'); ?></label>
                                            <label for="row[<?php echo $item['name']; ?>]-no"><input id="row[<?php echo $item['name']; ?>]-no" name="row[<?php echo $item['name']; ?>]" type="radio" value="0" <?php echo !empty($item['value'])?'':'checked'; ?> data-tip="<?php echo $item['tip']; ?>" /> <?php echo __('No'); ?></label>
                                            <?php break; case "editor": ?>
                                            <textarea <?php echo $item['extend']; ?> name="row[<?php echo $item['name']; ?>]" class="form-control editor"
                                                data-rule="<?php echo $item['rule']; ?>" rows="5"
                                                data-tip="<?php echo $item['tip']; ?>"><?php echo htmlentities($item['value'] ?? ''); ?></textarea>
                                            <?php break; default: ?><?php echo $item['value']; endswitch; ?>
                                        </div>
                                        <div class="col-sm-4"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!--@formatter:on-->

                        <div class="form-group layer-footer">
                            <label class="control-label col-xs-12 col-sm-2"></label>
                            <div class="col-xs-12 col-sm-8">
                                <button type="submit" class="btn btn-primary btn-embossed disabled"><?php echo __('OK'); ?></button>
                                <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
</form>

<!--@formatter:off-->

<script>
    require.callback = function () {
        var tabevent = function () {
            $(document).on("click", ".nav-group li a[data-toggle='tab']", function () {
                var type = $(this).attr("href").substring(1);
                if (type == 'all') {
                    $(".table-config tr").show();
                } else {
                    $(".table-config tr").hide();
                    $(".table-config tr[data-type='" + type + "']").show();
                }
            });
            $(document).on("click", ".btn-select-page", function (e, obj) {
                var that = this;
                Fast.api.open("shop/ajax/get_page_list", "选择路径", {
                    callback: function (data) {
                        $(that).parent().prev().val(data).trigger("change");
                    }
                })
            });
            $(document).on("keyup change", ".gallery-control", function(){
                $(this).parent().next().find("input:last").trigger("change");
                return false;
            });
        };
        define('backend/addon', ['jquery', 'form'], function ($, Form) {
            var Controller = {
                config: function () {
                    Form.api.bindevent($("form[role=form]"));
                    tabevent();
                }
            };
            return Controller;
        });
        define('backend/shop/config', ['jquery', 'form'], function ($, Form) {
            var Controller = {
                index: function () {
                    Form.api.bindevent($("form[role=form]"));
                    tabevent();
                }
            };
            return Controller;
        });
    }
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
