<?php

namespace addons\shop\controller\api;

use app\common\library\Auth;
use app\common\library\Sms;
use app\common\library\Ems;
use fast\Random;
use think\Validate;
use fast\Http;
use addons\third\library\Service;
use think\Config;
use think\Session;

class Login extends Base
{

    protected $noNeedLogin = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        if (!$this->request->isPost()) {
            $this->error('请求错误');
        }

        Auth::instance()->setAllowFields(array_merge(['username'], $this->allowFields));
    }

    /**
     * 会员登录
     *
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->post('account', '');
        $password = $this->request->post('password', '');

        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }

        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $user = $this->auth->getUserinfo();
            $user['avatar'] = cdnurl($user['avatar'], true);
            $this->success(__('Logged in successful'), [
                'token' => $this->auth->getToken(),
                'user'  => $user
            ]);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 重置密码
     *
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    public function resetpwd()
    {
        $type = $this->request->param("type");
        $mobile = $this->request->param("mobile");
        $email = $this->request->param("email");
        $newpassword = $this->request->param("newpassword");
        $captcha = $this->request->param("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }

        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('phone');
        $captcha = $this->request->post('code');
        $invite_id = $this->request->post('invite_id'); //邀请人id
        if ($invite_id) {
            \think\Cookie::set('inviter', $invite_id);
        }
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        /*if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }*/
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $user = $this->auth->getUserinfo();
            $user['avatar'] = cdnurl($user['avatar'], true);
            $data = ['token' => $this->auth->getToken(), 'user' => $user];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param string $code     验证码
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        $invite_id = $this->request->post('invite_id');
        if ($invite_id) {
            \think\Cookie::set('inviter', $invite_id);
        }
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }

        $ret = $this->auth->register($username, $password, '', $mobile, []);
        if ($ret) {
            $user = $this->auth->getUserinfo();
            $user['avatar'] = cdnurl($user['avatar'], true);
            $this->success(__('Sign up successful'), [
                'token' => $this->auth->getToken(),
                'user'  => $user
            ]);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 第三方登录[绑定] 小程序
     */
    public function wxLogin()
    {
        $code = $this->request->post("code"); //小程序code
        $WxUser = $this->request->post("rawData/a"); //微信用户信息
        $referee_id = $this->request->post("referee_id",0); //上级id
        if (!$code || !$WxUser) {
            $this->error("参数不正确");
        }
        $third = get_addon_info('third');
        if (!$third || !$third['state']) {
            $this->error("请在后台插件管理安装并配置第三方登录插件");
        }
        $params = [
            'appid'      => Config::get('shop.wx_appid'),
            'secret'     => Config::get('shop.wx_app_secret'),
            'js_code'    => $code,
            'grant_type' => 'authorization_code'
        ];

        $result = Http::sendRequest("https://api.weixin.qq.com/sns/jscode2session", $params, 'GET');
        if ($result['ret']) {
            $json = (array)json_decode($result['msg'], true);
            if (isset($json['openid'])) {
                $userinfo = [
                    'platform'      => 'wechat',
                    'apptype'       => 'miniapp',
                    'openid'        => $json['openid'],
                    'userinfo'      => [
                        'nickname' => $WxUser['nickName'],
                        'avatar'   => $WxUser['avatarUrl']
                    ],
                    'openname'      => $WxUser['nickName'],
                    'access_token'  => $json['session_key'],
                    'refresh_token' => '',
                    'expires_in'    => isset($json['expires_in']) ? $json['expires_in'] : 0,
                    'unionid'       => isset($json['unionid']) ? $json['unionid'] : ''
                ];

                $third = [
                    'nickname' => $WxUser['nickName'],
                    'avatar'   => $WxUser['avatarUrl']
                ];
                $user = null;
                if ($this->auth->isLogin() || Service::isBindThird($userinfo['platform'], $userinfo['openid'], $userinfo['apptype'], $userinfo['unionid'])) {
                    Service::connect($userinfo['platform'], $userinfo);
                    $user = $this->auth->getUserinfo();
                    $user['avatar'] = cdnurl($user['avatar'], true);
                } else {
                    //进行上级id绑定
                    $userinfo['referee_id'] = $referee_id;
                    Session::set('third-userinfo', $userinfo);
                }
                $this->success('授权成功!', ['user' => $user, 'third' => $third]);
            }
        }
        $this->error("授权失败," . $result['msg']);
    }

    //小程序使用微信手机号码授权绑定
    public function getWechatMobile()
    {
        $encryptedData = $this->request->post('encryptedData', '', 'trim');
        $iv = $this->request->post('iv');
        $code = $this->request->post("code");
        if (!$encryptedData || !$iv) {
            $this->error('参数错误！');
        }
        if (strlen($iv) != 24) {
            $this->error('iv不正确！');
        }
        $invite_id = $this->request->post('invite_id');
        if ($invite_id) {
            \think\Cookie::set('inviter', $invite_id);
        }
        $third = Session::get('third-userinfo');
        $params = [
            'appid'      => Config::get('shop.wx_appid'),
            'secret'     => Config::get('shop.wx_app_secret'),
            'js_code'    => $code,
            'grant_type' => 'authorization_code'
        ];
        $result = Http::sendRequest("https://api.weixin.qq.com/sns/jscode2session", $params, 'GET');
        if ($result['ret']) {
            $json = (array)json_decode($result['msg'], true);
            $aesIV = base64_decode($iv);
            $aesCipher = base64_decode($encryptedData);
            $aesKey = base64_decode($json['session_key']);
            $re = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
            $data = json_decode($re, true);
            if ($data) {
                $user = \app\common\model\User::getByMobile($data['phoneNumber']);
                if ($user) {
                    if ($user->status != 'normal') {
                        $this->error(__('Account is locked'));
                    }
                    //如果已经有账号则直接登录
                    $ret = $this->auth->direct($user->id);
                } else {
                    $ret = $this->auth->register($data['phoneNumber'], Random::alnum(), '', $data['phoneNumber'], isset($third['userinfo']) ? $third['userinfo'] : [],$third['referee_id']);
                }
                if ($ret) {
                    //绑定第三方
                    Service::connect('wechat', $third);
                    $data = ['token' => $this->auth->getToken()];
                    $this->success(__('Logged in successful'), $data);
                } else {
                    $this->error($this->auth->getError());
                }
            } else {
                $this->error("配置参数错误");
            }
        } else {
            $this->error("授权失败");
        }
    }

    /**
     * app登录
     */
    public function appLogin()
    {
        $code = $this->request->post("code");
        $scope = $this->request->post("scope");
        if (!$code) {
            $this->error("参数不正确");
        }
        $third = get_addon_info('third');
        if (!$third || !$third['state']) {
            $this->error("请在后台插件管理安装并配置第三方登录插件");
        }
        Session::set('state', $code);
        $config = [
            'app_id'     => Config::get('shop.app_id'),
            'app_secret' => Config::get('shop.app_secret'),
            'scope'      => $scope
        ];
        $wechat = new \addons\third\library\Wechat($config);
        $userinfo = $wechat->getUserInfo(['code' => $code, 'state' => $code]);
        if (!$userinfo) {
            $this->error(__('操作失败'));
        }
        //判断是否需要绑定
        $userinfo['apptype'] = 'native';
        $userinfo['platform'] = 'wechat';

        $third = [
            'avatar'   => $userinfo['userinfo']['avatar'],
            'nickname' => $userinfo['userinfo']['nickname']
        ];
        $user = null;
        if ($this->auth->isLogin() || Service::isBindThird($userinfo['platform'], $userinfo['openid'], $userinfo['apptype'], $userinfo['unionid'])) {
            Service::connect($userinfo['platform'], $userinfo);
            $user = $this->auth->getUserinfo();
            $user['avatar'] = cdnurl($user['avatar'], true);
        } else {
            Session::set('third-userinfo', $userinfo);
        }
        $this->success('授权成功!', ['user' => $user, 'third' => $third]);
    }
}
