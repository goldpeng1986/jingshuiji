<?php

return [
    'Id'             => '主键id',
    'Order_id'       => '订单id',
    'User_id'        => '用户id (买家)',
    'Order_price'    => '订单总金额(不含运费)',
    'First_user_id'  => '分销商用户id(一级)',
    'Second_user_id' => '分销商用户id(二级)',
    'Third_user_id'  => '分销商用户id(三级)',
    'First_money'    => '分销佣金(一级)',
    'Second_money'   => '分销佣金(二级)',
    'Third_money'    => '分销佣金(三级)',
    'Is_invalid'     => '订单是否失效(0未失效 1已失效)',
    'Is_settled'     => '是否已结算佣金(0未结算 1已结算)',
    'Settle_time'    => '结算时间',
    'Create_time'    => '创建时间',
    'Update_time'    => '更新时间',
    'Dealer_type'    => '10商品订单20上门订单',
    'User.nickname'  => '昵称'
];
