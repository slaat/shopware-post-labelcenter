//{block name="backend/shipping/view/edit/panel"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Shipping.PostConfiguration.view.edit.Panel', {
    override: 'Shopware.apps.Shipping.view.edit.Panel',

    createTabPanel: function () {
        var me = this;
        var tabPanel = me.callParent(arguments);
        tabPanel.insert(tabPanel.items.length, me.createPostConfigurationTab());
        return tabPanel;
    },

    createPostConfigurationTab: function () {
        var me = this;
        me.postConfigurationPanel = Ext.create('Shopware.apps.Shipping.PostConfiguration.view.edit.PostConfiguration', {
          record: me.editRecord
        });
        return me.postConfigurationPanel;
    }
});
//{/block}
