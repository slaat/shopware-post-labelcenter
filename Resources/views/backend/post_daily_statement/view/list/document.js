//{block name="backend/post_daily_statement/view/list/post_daily_statement_document"}
Ext.define('Shopware.apps.PostDailyStatement.view.list.Document', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.post_daily_statement-document-list',
    cls: Ext.baseCSSPrefix + 'document-grid',
    autoScroll: true,
    stateful: true,
    minHeight: 150,
    title: 'Generierte Dokumente',

    snippets: {
        columns: {
            name: 'Name',
            date: 'Datum',
            amount: 'Amount',
            downloadDocument: 'Download'
        }
    },
    initComponent: function() {
        var me = this;
        me.store = me.listStore;
        me.columns = [
            { header: me.snippets.columns.date, dataIndex: 'date', flex: 1, renderer: me.dateColumn },
            { header: me.snippets.columns.name, dataIndex: 'name', flex: 4, renderer: me.nameColumn }
        ];

        me.store.load({
            callback: function (response) {
            }
        });

        me.callParent(arguments);
    },

    dateColumn: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var helper = new Ext.dom.Helper;
        var spec = {
          tag: 'a',
          html: value,
          href: '{url action="loadDailyStatement"}?timestamp=' + record.get('timestamp'),
          target: '_blank'
        };
        return helper.markup(spec);
    },

    nameColumn: function(value, metaData, record, rowIndex, colIndex, store, view) {
        var helper = new Ext.dom.Helper;
        var spec = {
            tag: 'a',
            html: 'Tagesabschluss',
            href: '{url action="loadDailyStatement"}?timestamp=' + record.get('timestamp'),
            target: '_blank'
        };
        return helper.markup(spec);
    }
});
//{/block}

