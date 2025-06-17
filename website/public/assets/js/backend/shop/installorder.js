define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    if (Config.shop && Config.shop.printtype == 'clodop') {
        require([Config.shop.clodop_ip_port + '/CLodopfuncs.js']);
    }
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/installorder/index' + location.search,
                    // add_url: 'shop/order/add',
                    // edit_url: 'shop/order/edit',
                    del_url: 'shop/installorder/del',
                    multi_url: 'shop/installorder/multi',
                    import_url: 'shop/installorder/import',
                    table: 'shop_order',
                }
            });

            var table = $("#table");

            //当双击单元格时
            table.on('dbl-click-row.bs.table', function (e, row, element, field) {
                $(".btn-detail", element).trigger("click");
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [{
                        checkbox: true
                    },
                        {
                            field: 'id',
                            title: __('Id')
                        },
                        {
                            field: 'order_sn',
                            title: __('Order_sn'),
                            operate: 'LIKE'
                        },
                        {
                            field: 'receiver',
                            title: __('Receiver'),
                            operate: 'LIKE'
                        },
                        {
                            field: 'address',
                            title: __('Address'),
                            operate: 'LIKE'
                        },
                        {
                            field: 'zipcode',
                            title: __('Zipcode'),
                            operate: 'LIKE',
                            visible: false,
                        },
                        {
                            field: 'mobile',
                            title: __('Mobile'),
                            operate: 'LIKE'
                        },
                        {
                            field: 'orderstate',
                            title: __('Orderstate'),
                            searchList: {
                                "0": __('Orderstate 0'),
                                "1": __('Orderstate 1'),
                                "2": __('Orderstate 2'),
                                "3": __('Orderstate 3'),
                                "4": __('Orderstate 4'),
                            },
                            formatter: Controller.api.formatter.order_state_text
                        },
                        {
                            field: 'order_type',
                            title: __('订单类型'),
                            formatter: Controller.api.formatter.order_type_text
                        },
                        {
                            field: 'install_status',
                            title: __('安装状态'),
                            formatter: Controller.api.formatter.install_status_text
                        },
                        {
                            field: 'order_renew_status',
                            title: __('续费状态'),
                            formatter: Controller.api.formatter.order_renew_status_text
                        },
                        {
                            field: 'order_renew_time_text',
                            title: __('到期时间'),
                        },
                        {
                            field: 'paystate',
                            title: __('Paystate'),
                            searchList: {
                                "0": __('Paystate 0'),
                                "1": __('Paystate 1')
                            },
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'amount',
                            title: __('Amount'),
                            operate: 'BETWEEN'
                        },
                        {
                            field: 'goodsprice',
                            title: __('Goodsprice'),
                            operate: 'BETWEEN'
                        },
                        {
                            field: 'discount',
                            title: __('Discount'),
                            operate: 'BETWEEN'
                        },
                        {
                            field: 'shippingfee',
                            title: __('Shippingfee'),
                            operate: 'BETWEEN'
                        },
                        {
                            field: 'saleamount',
                            title: __('Saleamount'),
                            operate: 'BETWEEN'
                        },
                        {
                            field: 'payamount',
                            title: __('Payamount'),
                            operate: 'BETWEEN'
                        },
                        {
                            field: 'paytype',
                            title: __('Paytype'),
                            operate: 'LIKE'
                        },

                        {
                            field: 'memo',
                            title: __('Memo'),
                            operate: 'LIKE'
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "normal": __('Status normal'),
                                "hidden": __('Status hidden')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },

                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            clickToSelect: false,
                            buttons: [{
                                name: '取消电子面单',
                                title: __('取消电子面单'),
                                classname: 'btn btn-xs btn-success btn-ajax',
                                text: '取消电子面单',
                                icon: 'fa fa-sticky-note-o',
                                extend: 'data-area=\'["90%","90%"]\'',
                                url: 'shop/order/cancel_electronics/oe_id/{oe_id}',
                                hidden: function (row) {
                                    return !row.print_template;
                                },
                                success: function () {
                                    table.bootstrapTable('refresh', {});
                                }
                            }, {
                                name: '订单详情',
                                title: __('订单详情'),
                                classname: 'btn btn-xs btn-primary btn-dialog btn-detail',
                                text: '订单详情',
                                icon: 'fa fa-sticky-note-o',
                                extend: 'data-area=\'["90%","90%"]\'',
                                url: 'shop/reneworder/detail'
                            }]
                        }
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
            // 批量打印电子面单
            $(document).on('click', '.btn-print-multiple-electronic', function () {
                let ids = Table.api.selectedids(table);
                if (!ids.length) {
                    Toastr.error('请选择需打印的订单');
                    return;
                }
                Layer.index = 0;
                Layer.open({
                    id: 'electronic',
                    title: '请选择电子面单模板',
                    content: Template("electronictpl", {ids: ids}),
                    zIndex: 8,
                    btn: ["开始打印", "取消"],
                    success: function (layero, index) {
                        Form.events.selectpage(layero);
                    },
                    yes: function (index, layero) {
                        let electronics_id = $('#electronics_id', layero).val();
                        if (!electronics_id) {
                            Toastr.error('请选择电子面单模板');
                            return;
                        }
                        Fast.api.ajax(
                            {
                                url: 'shop/order/prints',
                                data: {
                                    ids: ids.join('_'),
                                    electronics_id: electronics_id
                                }
                            }, function (data) {
                                let html = '';
                                data.forEach(item => {
                                    //模板，获取成功
                                    if (item.Success) {
                                        html += '<div style="page-break-after: always">';
                                        html += item.PrintTemplate;
                                        html += '</div>';
                                    }
                                });
                                html && Controller.api.print('电子面单打印', html);
                                return false;
                            }
                        );
                    }
                });
            });
            // 批量打印发货单
            $(document).on('click', '.btn-print-multiple-invoice', function () {
                let order_ids = Table.api.selectedids(table);
                if (!order_ids.length) {
                    Toastr.error('请选择需打印的订单');
                    return;
                }
                Fast.api.ajax({
                    url: 'shop/order/orderList',
                    data: {
                        ids: order_ids
                    }
                }, function (data) {
                    var html = Template("invoicetpl", data);
                    Controller.api.print('发货单打印', html);
                    return false;
                })
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter:{
                order_renew_status_text(value){
                   if(value==10){
                       return '<a><span class="text-danger">待续约</span></a>';
                   }
                   if(value==20){
                       return '<a><span class="text-success">正常</span></a>';
                   }
                    if(value==30){
                        return '<a><span class="text-warning">已到期</span></a>';
                    }
                },
                install_status_text(value){
                    if(value==1){
                        return '<a><span class="text-danger">待确认</span></a>';
                    }
                    if(value==2){
                        return '<a><span class="text-success">已确认</span></a>';
                    }
                },
                order_state_text(value){
                    if(value==0){
                        return '<a><span class="text-danger">待安装</span></a>';
                    }
                    if(value==1){
                        return '<a><span class="text-info">已取消</span></a>';
                    }
                    if(value==2){
                        return '<a><span class="text-info">已失效</span></a>';
                    }
                    if(value==3){
                        return '<a><span class="text-success">已完成</span></a>';
                    }
                    if(value==4){
                        return '<a><span class="text-success">退款</span></a>';
                    }
                },
                order_type_text(value){
                    return '<a><span class="">续费订单</span></a>';
                }
            }
        }
    };
    return Controller;
});
