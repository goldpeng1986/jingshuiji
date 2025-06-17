define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dealer/order/index' + location.search,
                    add_url: 'dealer/order/add',
                    edit_url: 'dealer/order/edit',
                    del_url: 'dealer/order/del',
                    multi_url: 'dealer/order/multi',
                    import_url: 'dealer/order/import',
                    table: 'dealer_order',
                }
            });

            var table = $("#table");
            $(table).data("operate-edit", null);
            $(table).data("operate-del", null);

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
                        {field: 'order_id', title: __('Order_id')},
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
                        {field: 'order_price', title: __('Order_price'), operate:'BETWEEN'},
                        {field: 'first_money', title: __('First_money'), operate:'BETWEEN'},
                        {field: 'second_money', title: __('Second_money'), operate:'BETWEEN'},
                        {field: 'third_money', title: __('Third_money'), operate:'BETWEEN'},
                        {field: 'dealer_type', title: __('订单类型'),formatter: Controller.api.events.dealer_type_status,operate: false},
                        {field: 'is_invalid', title: __('订单状态'),formatter: Controller.api.events.is_invalid_status,operate: false},
                        {field: 'is_settled', title: __('是否结算'),formatter: Controller.api.events.is_settled_status},
                        {field: 'settle_time', title: __('Settle_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                    ]
                ]
            });
            var options = table.bootstrapTable('getOptions');
            var queryParams = options.queryParams;
            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var value = $(this).data("value");
                options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    if (value) {
                        params.filter = JSON.stringify(value);
                        let op = {}
                        for (let i in value) {
                            op[i] = '=';
                        }
                        params.op = JSON.stringify(op);
                    }
                    params = queryParams.call(this, params);
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
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
           events:{
               dealer_type_status:function (value){
                   if(value==10){
                       return '<a><span class="text-success">商品订单</span></a>';
                   }
                   if(value==20){
                       return '<a><span class="text-info">上门订单</span></a>';
                   }
                   if(value==30){
                       return '<a><span class="text-danger">服务订单</span></a>';
                   }
               },
               is_invalid_status:function (value){
                   if(value==0){
                       return '<a><span class="text-success">未失效</span></a>';
                   }
                   if(value==1){
                       return '<a><span class="text-danger">已失效</span></a>';
                   }
               },
               is_settled_status:function (value){
                   if(value==0){
                       return '<a><span class="text-danger">未结算</span></a>';
                   }
                   if(value==1){
                       return '<a><span class="text-success">已结算</span></a>';
                   }
               }
           }
        }
    };
    return Controller;
});
