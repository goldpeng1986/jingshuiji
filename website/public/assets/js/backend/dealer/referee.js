define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dealer/referee/index' + location.search,
                    add_url: 'dealer/referee/add',
                    edit_url: 'dealer/referee/edit',
                    del_url: 'dealer/referee/del',
                    multi_url: 'dealer/referee/multi',
                    import_url: 'dealer/referee/import',
                    table: 'dealer_referee',
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
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('用户id')},
                        {field: 'level', title: __('关系')},
                        {field: 'order_count', title: __('推广订单')},
                        {field: 'order_sum', title: __('订单金额')},
                        {field: 'create_time', title: __('添加时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
            }
        }
    };
    return Controller;
});
