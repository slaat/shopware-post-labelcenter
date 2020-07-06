//{block name="backend/index/post_backend_widget/view/main"}
Ext.define('Shopware.apps.Index.PostBackendWidget.view.Main', {
    extend: 'Shopware.apps.Index.view.widgets.Base',
    alias: 'widget.post-backend-widget',
    layout: 'fit',
    bodyPadding: '8 8',
    resizable: {
        handles: 's'
    },
    minHeight: 250,

    initComponent: function() {
        var me = this;
        me.newsStore = Ext.create('Shopware.apps.Index.PostBackendWidget.store.News');

        me.items = [
            me.createIntroPanel(),
            me.createNewsGrid()
        ];

        me.tools = [{
            type: 'refresh',
            scope: me,
            handler: me.refreshView
        }];

        me.callParent(arguments);
    },

    createNewsGrid: function() {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            border: 0,
            store: me.newsStore,
            columns: me.createColumns()
        });
    },

    createColumns: function() {
        var me = this;
        return [
            {
                dataIndex: 'content',
                header: 'Inhalt',
                flex: 1,
                renderer: me.contentColumn,
                style: 'float: left'
            },
            {
                dataIndex: 'date',
                header: 'Datum',
                flex: 1,
                style: 'float: right',
                renderer: me.dateColumn
            }
        ]
    },

    refreshView: function() {
        var me = this;

        if(!me.newsStore) {
            return;
        }

        me.newsStore.reload();
    },

    contentColumn: function(value, cellEl, record) {
        return Ext.String.format('{literal}<a href="{0}"  target="_blank">{1}</a>{/literal}', record.get('link'), value);
    },

    dateColumn: function(value, cellEl, record) {
       return Ext.String.format('{literal}<a href="{0}" target="_blank">{1}</a>{/literal}', record.get('link'), value);
    },

    createIntroPanel: function() {
        var image = '{link file="custom/plugins/PostLabelCenter/Resources/views/backend/_resources/images/post_logo_small.jpg"}';

        return Ext.create('Ext.panel.Panel', {
            html: '<img src="' + image + '" style="width: 100px;">',
            maxHeight: 40,
            layout: {
                align: 'stretch',
                padding: 10,
                type: 'vbox'
            }
        });
    }
});
//{/block}
