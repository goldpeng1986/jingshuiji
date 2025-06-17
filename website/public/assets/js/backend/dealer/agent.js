define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dealer/agent/index' + location.search,
                    add_url: 'dealer/agent/add',
                    edit_url: 'dealer/agent/edit',
                    del_url: 'dealer/agent/del',
                    multi_url: 'dealer/agent/multi',
                    import_url: 'dealer/agent/import',
                    table: 'dealer_agent',
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
                        {field: 'user.nickname', title: __('微信姓名'), operate: 'LIKE'},
                        {field: 'user.dealer_price', title: __('佣金'), operate: 'LIKE'},
                        {
                            field: 'create_time',
                            title: __('添加时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'update_time',
                            title: __('修改时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'referee.nickname', title: __('上级微信姓名')},
                        {
                            field: 'operate', title: __('Operate'),table: table,
                            buttons: [
                                {
                                name: "agent",
                                text: "推广人",//按钮名称
                                classname: 'btn btn-xs btn-primary btn-dialog',
                                icon: 'fa fa-bars',
                                    url: function (row) {
                                    console.log(row);
                                        return `dealer/referee/index?dealer_id=${row.uid}`;
                                    },
                            }
                            ],
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate
                        }
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
        agent: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        }
    };
    return Controller;
});
