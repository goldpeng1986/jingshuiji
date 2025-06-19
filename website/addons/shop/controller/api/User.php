<?php

namespace addons\shop\controller\api;

use addons\shop\model\Order;
use app\common\model\MoneyLog;
use app\common\model\UserMoneyOrder;
use think\Config;


/**
 * 会员
 */
class User extends Base
{
    protected $noNeedLogin = ['getSigned'];

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

        // Get user expenses
        $expenses = \addons\shop\model\Order::getUserExpenses($this->auth->id);
        $info['today_expense'] = $expenses['today_expense'];
        $info['month_expense'] = $expenses['month_expense'];

        // Clarification on money fields based on typical use:
        // $info['money'] is often 'balance' in FastAdmin user model.
        // $info['score'] is 'points'.
        // Assuming $info['money'] maps to general balance for the user.
        // If there's a separate dealer/commission balance, it might be from a different model or field.
        // For now, we'll use 'money' as the main balance and 'score' as points.
        // The frontend Vue files might need to adjust how they map these if 'dealer_price' is distinct.

        $this->success('', [
            'userInfo' => $info
            // accountInfo seems to be a computed property on the frontend based on userInfo
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
     * @ApiParams (name="oldpassword", type="string", required=true, description="旧密码")
     * @ApiParams (name="newpassword", type="string", required=true, description="新密码")
     * @ApiParams (name="renewpassword", type="string", required=true, description="确认新密码")
     */
    public function changePassword()
    {
        // 获取POST数据
        $oldPassword = $this->request->post('oldpassword');
        $newPassword = $this->request->post('newpassword');
        $renewPassword = $this->request->post('renewpassword');

        // 基本参数验证
        if (!$oldPassword || !$newPassword || !$renewPassword) {
            $this->error(__('Please provide old password, new password, and confirmation.')); // 请提供旧密码、新密码和确认密码。
        }

        if ($newPassword !== $renewPassword) {
            $this->error(__('The new password and confirmation password do not match.')); // 新密码和确认密码不匹配。
        }

        // 新密码复杂度验证 (示例：至少6位，包含字母和数字)
        // FastAdmin的Auth->changepwd默认可能只检查长度，具体规则需查阅Auth类或按需增强
        if (strlen($newPassword) < 6) { // 基本长度检查
            $this->error(__('Password must be at least 6 characters long.')); // 密码长度至少为6位。
        }
        // if (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        //     $this->error(__('Password must contain both letters and numbers.')); // 密码必须包含字母和数字。
        // }
        // 注意：更复杂的规则，例如特殊字符、大小写等，可以按需添加。

        // 调用 FastAdmin 的 Auth 类进行密码修改
        // changepwd 方法会处理旧密码验证和新密码的哈希存储
        $ret = $this->auth->changepwd($newPassword, $oldPassword);
        if ($ret) {
            // 密码修改成功后，可以选择让用户重新登录，但通常API层面仅返回成功信息
            $this->success(__('Password updated successfully. You may need to log in again.')); // 密码修改成功。您可能需要重新登录。
        } else {
            // 获取Auth类中具体的错误信息
            $this->error($this->auth->getError() ?: __('Failed to update password. Please check your old password.')); // 更新密码失败。请检查您的旧密码。
        }
    }
}
