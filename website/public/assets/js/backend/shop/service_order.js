define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/service_order/index' + location.search,
                    add_url: 'shop/service_order/add',
                    edit_url: 'shop/service_order/edit',
                    del_url: 'shop/service_order/del',
                    multi_url: 'shop/service_order/multi',
                    import_url: 'shop/service_order/import',
                    table: 'shop_service_order',
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
                        {field: 'order_sn', title: __('Order_sn'), operate: 'LIKE'},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'brand', title: __('Brand'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'model', title: __('Model'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.images, formatter: Table.api.formatter.images},
                        {field: 'order_state', title: __('订单状态'),formatter: Controller.api.formatter.order_state_text},
                        {field: 'pay_state', title: __('支付状态'),formatter: Controller.api.formatter.pay_state_text},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'pay_price', title: __('Pay_price')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'edit',
                                    title: __('通过'),
                                    text:'通过',
                                    classname: 'btn btn-xs btn-success btn-detail btn-dialog',
                                    extend:'data-area=\'["600px","600px"]\'',
                                    icon: 'fa fa-check',
                                    refresh:true,
                                    url: 'shop.service_order/pass',
                                    hidden:function(row){
                                        if(row.order_state==0){
                                            return false;

                                        }else{
                                            return true;

                                        }
                                    }
                                },

                                {
                                    name: 'edit',
                                    title: __('完成'),
                                    text:'完成',
                                    classname: 'btn btn-xs btn-success btn btn-ajax',
                                    extend:'data-area=\'["600px","600px"]\'',
                                    confirm: '你确定要完成了吗',
                                    icon: 'fa fa-check',
                                    refresh:true,
                                    url: 'shop.service_order/successOrder',
                                    hidden:function(row){
                                        if(row.order_state==1){
                                            return false;

                                        }else{
                                            return true;

                                        }
                                    }
                                },

                                {
                                    name: 'del',
                                    title: __('拒接理由'),
                                    text:'拒接',
                                    classname: 'btn btn-xs btn-danger btn-detail btn-dialog',
                                    extend:'data-area=\'["800px","360px"]\'',
                                    icon: 'fa fa-trash',
                                    refresh:true,
                                    url:'shop.service_order/reject',
                                    hidden:function(row){
                                        if(row.order_state==0){
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.operate}
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
        pass:function (){
            Controller.api.bindevent();

        },
        reject:function (){
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter:{
                order_state_text(value){
                    if(value==0){
                        return '<a><span class="text-danger">待审核</span></a>';
                    }
                    if(value==1){
                        return '<a><span class="text-success">审核通过</span></a>';
                    }
                    if(value==2){
                        return '<a><span class="text-danger">已拒接</span></a>';
                    }
                    if(value==3){
                        return '<a><span class="text-info">已取消</span></a>';
                    }
                    if(value==4){
                        return '<a><span class="text-success">已完成</span></a>';
                    }
                },
                pay_state_text(value){
                    if(value==10){
                        return '<a><span class="text-danger">未支付</span></a>';
                    }
                    if(value==20){
                        return '<a><span class="text-success">已支付</span></a>';
                    }
                }

            }
        }
    };
    return Controller;
});
