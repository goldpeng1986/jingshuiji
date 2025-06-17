define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dealer/capital/index' + location.search,
                    add_url: 'dealer/capital/add',
                    edit_url: 'dealer/capital/edit',
                    del_url: 'dealer/capital/del',
                    multi_url: 'dealer/capital/multi',
                    import_url: 'dealer/capital/import',
                    table: 'dealer_capital',
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
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'flow_type', title: __('佣金类型'),formatter: Controller.api.events.flow_type_status,operate: false},
                        {field: 'describe', title: __('Describe'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
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
                flow_type_status:function (value){
                    if(value==10){
                        return '<a><span class="text-success">佣金收入</span></a>';
                    }
                    if(value==20){
                        return '<a><span class="text-danger">佣金提现</span></a>';
                    }
                }
            }

        }
    };
    return Controller;
});
