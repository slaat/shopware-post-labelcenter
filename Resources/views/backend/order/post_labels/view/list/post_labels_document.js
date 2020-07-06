//{block name="backend/order/post_labels/view/list/post_labels_document"}
Ext.define('Shopware.apps.Order.PostLabels.view.list.PostLabelsDocument', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.order-post_labels-document-list',
    cls: Ext.baseCSSPrefix + 'document-grid',
    autoScroll: true,

    snippets: {
        columns: {
            name: 'Name',
            date: 'Datum',
            status: 'Status',
            amount: 'Amount',
            downloadDocument: 'Download',
            type: 'Type',
            deletePostLabel: 'Label stornieren'
        }

    },
    initComponent: function() {
        var me = this;
        me.columns = me.getColumns();
        me.pagingbar = me.getPagingBar();
        me.callParent(arguments);
    },

    getColumns: function() {
        var me = this;
        return [
            {
                header: me.snippets.columns.date,
                dataIndex: 'date',
                flex: 1,
                renderer: me.dateColumn
            }, {
                header: me.snippets.columns.name,
                dataIndex: 'name',
                flex: 2,
                renderer: me.nameColumn,
                listeners: {
                    click: function(dataview, index, item, e) {
                        me.store.reload();
                    }
                }
            }, {
                header: me.snippets.columns.downloadDocument,
                dataIndex: 'downloaded',
                flex: 1,
                width: 30,
                renderer: me.statusColumn
            },
            me.createActionColumn()
        ];
    },

    getPagingBar: function() {
        var me = this;
        return Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom'
        })
    },

    dateColumn: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var me = this;
        return me.getLinkedDocumentColumn(record, value);
    },

    nameColumn: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var me = this;
        return me.getLinkedDocumentColumn(record, value);
    },

    statusColumn: function(value, metaData, record, rowIndex, colIndex, store, view) {
        if (record.get('downloaded')) {
            return '<div>Bereits heruntergeladen</div>';
        }
        return;
    },
    getLinkedDocumentColumn: function(record, display) {
        var me = this;
        var helper = new Ext.dom.Helper;
        if (record.get('number')) {
            display += ' ' + Ext.String.leftPad(record.get('id'));
        }

        var spec = {
            tag: 'a',
            html: display,
            href: '{url controller="PostLabels" action="openPdf"}?documentid=' + record.get('id') + '&orderid=' +  me.order.get('id') + '&orderNumber=' +  record.get('orderNumber') + '&shopId=' + me.order.get('shopId'),
            target: '_blank'
        };

        return helper.markup(spec);
    },

    createActionColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Action', {
            width:90,
            items:[
                me.createDeleteLabelColumn()
            ]
        });
    },

    createDeleteLabelColumn: function() {
        var me = this;
        return {
            iconCls:'sprite-minus-circle-frame',
            action:'deletePostLabel',
            tooltip:me.snippets.columns.deletePostLabel,

            handler:function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                    record = store.getAt(rowIndex);
                me.fireEvent('deletePostLabel', record, store, me.order);
            }
        };
    },
});
//{/block}

