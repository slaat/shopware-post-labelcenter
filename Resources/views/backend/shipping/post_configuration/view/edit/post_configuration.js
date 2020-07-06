//{namespace name=backend/shipping/post_configuration/view/edit/post_configuration}
//{block name="backend/shipping/post_configuration/view/edit/post_configuration"}
Ext.define('Shopware.apps.Shipping.PostConfiguration.view.edit.PostConfiguration', {
    extend:'Ext.container.Container',
    alias:'widget.shipping-post_configuration-view-edit-post_configuration',
    name:'shipping-post_configuration-view-edit-post_configuration',
    title:'Österreichische Post',
    layout: 'column',
    overflowY: 'scroll',

    initComponent: function() {
        var me = this;
        me.confPanel = Ext.create('Ext.panel.Panel');
        me.items  =  [
            me.confPanel
        ];
        me.callParent(arguments);
    },

    afterShow: function() {
        var me = this;
        var panel =  me.confPanel;

        if (!me.shippingConfPanel) {
            me.shippingConfPanel = Ext.create('Shopware.apps.Shipping.PostConfiguration.view.edit.PostConfigurationTabPanel', {
                record: me.record
            });
            panel.add(me.shippingConfPanel);
        }
        panel.doLayout();
        //me.shippingConfPanel.doLayout();
        me.callParent(arguments);
    }
});
//{/block}
