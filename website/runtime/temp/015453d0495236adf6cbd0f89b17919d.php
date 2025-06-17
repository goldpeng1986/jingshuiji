<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:81:"/www/wwwroot/cuijingshui.zwzdoac.cn/addons/third/view/hook/user_register_end.html";i:1692758246;}*/ ?>
<style>
    .social-login {
        display: flex
    }

    .social-login a {
        flex: 1;
        margin: 0 2px;
    }

    .social-login a:first-child {
        margin-left: 0;
    }

    .social-login a:last-child {
        margin-right: 0;
    }
</style>

<div class="form-group social-login mb-2">
    <?php if(in_array('wechat', $status)): ?>
        <a class="btn btn-success" href="<?php echo url('/third/connect/wechat'); ?>?url=<?php echo urlencode($url ?? ''); ?>"><i class="fa fa-wechat"></i> 微信登录</a>
    <?php endif; if(in_array('qq', $status)): ?>
        <a class="btn btn-info" href="<?php echo url('/third/connect/qq'); ?>?url=<?php echo urlencode($url ?? ''); ?>"><i class="fa fa-qq"></i> QQ登录</a>
    <?php endif; if(in_array('weibo', $status)): ?>
        <a class="btn btn-danger" href="<?php echo url('/third/connect/weibo'); ?>?url=<?php echo urlencode($url ?? ''); ?>"><i class="fa fa-weibo"></i> 微博登录</a>
    <?php endif; ?>
</div>
