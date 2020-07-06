//{block name="backend/order/post_tracking/view/detail/post_tracking_tab_panel"}
Ext.define('Shopware.apps.Order.PostTracking.view.detail.PostTrackingTabPanel', {
    extend: 'Ext.panel.Panel',
    title: 'Post Tracking Panel',
    border: false,
    bodyBorder: false,
    autoScroll:true,

    initComponent: function () {
        var me = this;
        var orderTrackingCodes = me.record.get('trackingCode');
        me.trackingCodes = orderTrackingCodes ? orderTrackingCodes.split(';').map(function(item) { return item.trim()}) : [];
        me.items =  me.createTabPanel();
        me.callParent(arguments);
    },

    createTabPanel: function() {
        var me = this;
        var tabs = me.getTabs();
        return Ext.create('Ext.tab.Panel', {
            autoShow: true,
            border: false,
            bodyBorder: false,
            items: tabs
        });
    },

    getTabs:function () {
        var me = this;
        var tabs = [];

        Ext.each(me.trackingCodes, function(trackingCode) {
            if (trackingCode) {
                tabs.push( Ext.create('Shopware.apps.Order.PostTracking.view.detail.PostTrackingSingleTab', {
                    title: trackingCode,
                    record: me.record,
                    disabled: false,
                    layout: 'fit',
                    trackingCode: trackingCode
                }));
            }
        });
        return tabs;
    }
});
//{/block}
