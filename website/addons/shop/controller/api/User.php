<?php

namespace addons\shop\controller\api;

use addons\shop\model\Order;
use app\common\model\MoneyLog;
use app\common\model\UserMoneyOrder;
use think\Config;
use think\Validate; // 引入 Validate 类

/**
 * 会员
 */
class User extends Base
{
    protected $noNeedLogin = ['getSigned']; // 确保 changepassword 不在此列

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
    }

    /**
     * 个人中心
     */
    public function index()
    {
        $info = $this->auth->getUserInfo();
        $info['order'] = [
            'created'  => Order::where('user_id', $this->auth->id)->where('order_type',10)->where('orderstate', 0)->where('paystate', 0)->count(),
            'paid'     => Order::where('user_id', $this->auth->id)->where('order_type',10)->where('orderstate', 0)->where('paystate', 1)->where('shippingstate', 0)->count(),
            'evaluate' => Order::where('user_id', $this->auth->id)->where('order_type',10)->where('orderstate', 0)->where('paystate', 1)->where('shippingstate', 2)->count()
        ];
        $info['avatar'] = cdnurl($info['avatar'], true);
        $signin = get_addon_info('signin');
        $info['is_install_signin'] = ($signin && $signin['state']);
        $this->success('', [
            'userInfo' => $info
        ]);
    }


    /**
     * 个人资料
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar');
        if (!$username || !$nickname) {
            $this->error("用户名和昵称不能为空");
        }
        if (strlen($bio) > 100) {
            $this->error("签名太长了！");
        }
        $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
        if ($exists) {
            $this->error(__('Username already exists'));
        }

        $avatar = str_replace(cdnurl('', true), '', $avatar);

        $user->username = $username;
        $user->nickname = $nickname;
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();

        $this->success('修改成功！');
    }

    /**
     * 保存头像
     */
    public function avatar()
    {
        $user = $this->auth->getUser();
        $avatar = $this->request->post('avatar');
        if (!$avatar) {
            $this->error("头像不能为空");
        }

        $avatar = str_replace(cdnurl('', true), '', $avatar);
        $user->avatar = $avatar;
        $user->save();
        $this->success('修改成功！');
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'), ['__token__' => $this->request->token()]);
    }

    /**
     * 分享配置参数
     */
    public function getSigned()
    {
        $url = $this->request->param('url', '', 'trim');
        $js_sdk = new \addons\shop\library\Jssdk();
        $data = $js_sdk->getSignedPackage($url);
        $this->success('', $data);
    }


    public function userMoney(){
        $money = $this->request->post('money');
        if(!$money){
            $this->error('请填写余额');
        }
        $model = new \app\common\model\User();
        $result =  $model->payRacharge($money,$this->auth->id);
        return $this->success('请支付',$result);

    }

    public function getList(){
        $model = new MoneyLog();
        $param = $this->request->get();
        $list =  $model->getList($this->auth->id,$param['page'],$param['limit']);
        $count = $model->getlistCount($this->auth->id);
        $this->success('查询成功',compact('list','count'));
    }

    /**
     * 修改密码
     * @ApiMethod (POST)
     * @param string $old_password 旧密码
     * @param string $new_password 新密码
     * @param string $confirm_password 确认新密码
     */
    public function changepassword()
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }

        $old_password = $this->request->post('old_password');
        $new_password = $this->request->post('new_password');
        $confirm_password = $this->request->post('confirm_password');

        if (!$old_password || !$new_password || !$confirm_password) {
            $this->error(__('参数不能为空'));
        }

        if ($new_password !== $confirm_password) {
            $this->error(__('新密码两次输入不一致'));
        }

        // 密码规则校验 (示例：长度至少8，包含字母和数字)
        // 前端uniapp/pages/set/change-password.vue中提示 "密码长度为8-20个字符", "必须包含字母和数字"
        // 此处后端也应做类似或更严格的校验
        $validate = new Validate([
            'new_password' => 'require|length:8,20|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,20}$/'
        ], [
            'new_password.require' => '新密码不能为空',
            'new_password.length'  => '新密码长度必须在8到20个字符之间',
            'new_password.regex'   => '新密码必须包含字母和数字'
        ]);

        if (!$validate->check(['new_password' => $new_password])) {
            $this->error($validate->getError());
        }

        if ($new_password == $old_password) {
            $this->error(__('新密码不能与旧密码相同'));
        }

        $user = $this->auth->getUser();
        // 验证旧密码
        // 注意：$this->auth->changepwd 方法通常内部会处理旧密码的验证
        // 如果 changepwd 第一个参数是新密码，第二个是旧密码，它会先验证旧密码
        // 此处假设 changepwd(新密码, 旧密码) 的行为
        $result = $this->auth->changepwd($new_password, $old_password);

        if ($result) {
            // 密码修改成功后，可能需要让当前token失效，强制用户重新登录
            // $this->auth->logout(); // 可选：如果希望修改密码后立即登出
            $this->success(__('密码修改成功'));
        } else {
            // 获取具体的错误信息，这可能来自Auth类
            $error_msg = $this->auth->getError();
            $this->error(empty($error_msg) ? __('旧密码不正确或操作失败') : $error_msg);
        }
    }
}
