//{namespace name=backend/shipping/post_configuration/view/edit/post_configuration}
//{block name="backend/shipping/post_configuration/view/edit/post_configuration_tab_panel"}
Ext.define('Shopware.apps.Shipping.PostConfiguration.view.edit.PostConfigurationTabPanel', {
    extend:'Ext.container.Container',
    alias:'widget.shipping-post_configuration-view-edit-post_configuration_tab_panel',
    name:'shipping-post_configuration-view-edit-post_configuration_tab_panel',
    title:'Österreichische Post',

    initComponent: function () {
        var me = this;
        me.items = [
            Ext.create('Shopware.apps.Shipping.PostConfiguration.view.edit.PostConfigurationForm', {
                countries: me.record.raw.countries,
                dispatchID: me.record.get('id')
            })
        ];
        me.callParent(arguments);
    }
});
//{/block}
