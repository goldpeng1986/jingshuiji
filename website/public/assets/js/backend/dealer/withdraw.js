define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dealer/withdraw/index' + location.search,
                    add_url: 'dealer/withdraw/add',
                    edit_url: 'dealer/withdraw/edit',
                    del_url: 'dealer/withdraw/del',
                    multi_url: 'dealer/withdraw/multi',
                    import_url: 'dealer/withdraw/import',
                    table: 'dealer_withdraw',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'money', title: __('Money'), operate: 'BETWEEN'},
                        {field: 'pay_type', title: __('Pay_type')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'bank_number', title: __('Bank_number'), operate: 'LIKE'},
                        {field: 'bank_name', title: __('Bank_name'), operate: 'LIKE'},
                        {field: 'withdraw_rate', title: __('提现费率'), operate: 'LIKE'},
                        {field: 'rate_money', title: __('手续费'), operate: 'LIKE'},
                        {
                            field: 'apply_status',
                            title: __('Apply_status'),
                            formatter: Controller.api.formatter.apply_status_text
                        },
                        {
                            field: 'audit_time',
                            title: __('Audit_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'reject_reason',
                            title: __('Reject_reason'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'update_time',
                            title: __('Update_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'image',
                            title: __('Image'),
                            operate: false,
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'edit',
                                    title: __('通过'),
                                    text: '通过',
                                    classname: 'btn btn-xs btn-success btn btn-ajax',
                                    extend: 'data-area=\'["600px","820px"]\'',
                                    icon: 'fa fa-check',
                                    refresh: true,
                                    confirm: '你确定要通过吗',
                                    url: 'dealer.Withdraw/pass',
                                    hidden: function (row) {
                                        if (row.apply_status != 10) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    }
                                },
                                {
                                    name: 'del',
                                    title: __('拒接理由'),
                                    text: '拒接',
                                    classname: 'btn btn-xs btn-danger btn-detail btn-dialog',
                                    extend: 'data-area=\'["800px","360px"]\'',
                                    icon: 'fa fa-trash',
                                    refresh: true,
                                    url: 'dealer.Withdraw/reject',
                                    hidden: function (row) {
                                        if (row.apply_status != 10) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    }
                                },
                                {
                                    name: 'del',
                                    title: __('拒接理由'),
                                    text: '打款',
                                    classname: 'btn btn-xs btn-success btn btn-ajax',
                                    extend: 'data-area=\'["800px","360px"]\'',
                                    icon: 'fa fa-usd',
                                    refresh: true,
                                    confirm: '你确定已经打款了吗',
                                    url: 'dealer.Withdraw/successWithdraw',
                                    hidden: function (row) {
                                        if (row.apply_status != 20) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
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
            formatter: {
                apply_status_text(value) {
                    if (value == 10) {
                        return '<a><span class="text-info">待审核</span></a>';
                    }
                    if (value == 20) {
                        return '<a><span style="color: #ff9900" class="text-success">待打款</span></a>';
                    }
                    if (value == 30) {
                        return '<a><span class="text-danger">已驳回</span></a>';
                    }
                    if (value == 40) {
                        return '<a><span class="text-success">已确认</span></a>';
                    }
                },
            }
        }
    };
    return Controller;
});
