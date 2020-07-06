//{block name="backend/order/view/detail/window"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Order.PostTracking.view.detail.Window', {
    override: 'Shopware.apps.Order.view.detail.Window',
    name: 'PostTrackingWindow',

    createTabPanel: function () {
        var me = this;
        var tabPanel = me.callParent(arguments);
        tabPanel.insert(tabPanel.items.length, me.createPostTrackingTabPanel());
        return tabPanel;
    },

    createPostTrackingTabPanel: function () {
        var me = this;
        return Ext.create('Shopware.apps.Order.PostTracking.view.detail.PostTrackingTabPanel', {
            record: me.record
        });
    }
});
//{/block}
