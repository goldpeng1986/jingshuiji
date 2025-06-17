define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/user_money_order/index' + location.search,
                    add_url: 'shop/user_money_order/add',
                    edit_url: 'shop/user_money_order/edit',
                    del_url: 'shop/user_money_order/del',
                    multi_url: 'shop/user_money_order/multi',
                    import_url: 'shop/user_money_order/import',
                    table: 'user_money_order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
                        {field: 'order_sn', title: __('Order_sn'), operate: 'LIKE'},
                        {field: 'pay_state', title: __('支付状态'),formatter: Controller.api.formatter.pay_state_text},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'state', title: __('状态'),formatter: Controller.api.formatter.state_text},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter:{
                pay_state_text(value) {
                    if (value == 0) {
                        return '<a><span class="text-danger">待支付</span></a>';
                    }
                    if (value == 1) {
                        return '<a><span  class="text-success">已支付</span></a>';
                    }
                },
                state_text(value) {
                    if (value == 1) {
                        return '<a><span class="text-info">正常</span></a>';
                    }
                    if (value == 2) {
                        return '<a><span  class="text-success">已完成</span></a>';
                    }
                    if (value ==3) {
                        return '<a><span  class="text-danger">已取消</span></a>';
                    }
                },
            }
        }
    };
    return Controller;
});
