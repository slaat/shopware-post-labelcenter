//{block name="backend/order/post_daily_statement/view/main/window"}
Ext.define('Shopware.apps.PostDailyStatement.view.main.Window', {
    extend: 'Enlight.app.Window',
    cls: Ext.baseCSSPrefix + 'post-daily-statement',
    title: 'Tagesabschluss',
    alias: 'widget.post_daily_statement-main-window',
    minHeight: '100px',
    maxHeight: 260,
    buttonAlign : 'center',
    layout: 'fit',
    stateful : true,
    overflow: 'hidden',

    initComponent: function () {
        var me = this;
        var dailyStatementContainer = Ext.create('Ext.container.Container', {
            items: [
                Ext.create('Shopware.apps.PostDailyStatement.view.list.Document', {
                  listStore: me.listStore
                }),
                Ext.create('Shopware.apps.PostDailyStatement.view.detail.Configuration', {
                    listStore: me.listStore
                })
            ],
            height: '100%'
        });
        me.items = [dailyStatementContainer];
        me.callParent(arguments);
    }
});
//{/block}
