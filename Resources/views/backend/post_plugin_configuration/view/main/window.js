//{block name="backend/order/post_plugin_configuration/view/main/window"}
Ext.define('Shopware.apps.PostPluginConfiguration.view.main.Window', {
    extend: 'Enlight.app.Window',
    cls: Ext.baseCSSPrefix + 'post-plugin-configuration',
    title: 'Post-Labelcenter Plugin Einstellungen',
    alias: 'widget.post_plugin_configuration-main-window',
    stateId: 'post_plugin_configuration-main-window',
    minHeight: '100px',
    layout: 'fit',
    stateful : true,
    border: false,

    initComponent: function () {
        var me = this;
        me.items = me.createTabPanel();
        me.callParent(arguments);
    },

    createTabPanel: function() {
        var me = this;

        var contractForm =   Ext.create('Shopware.apps.PostPluginConfiguration.view.detail.ContractForm', {
            configurationStore: me.configurationStore
        });

        var contractList =  Ext.create('Shopware.apps.PostPluginConfiguration.view.list.Contract', {
            listStore: me.listStore
        });

        var tab2 = Ext.create('Ext.form.Panel', {
            title: 'Zusätzliche Kennungen',
            border: false,
            bodyPadding: 10,
            items: [contractForm, contractList]
        });

        return Ext.create('Ext.tab.Panel', {
            name: 'main-tab',
            border: false,
            items: [
                Ext.create('Shopware.apps.PostPluginConfiguration.view.detail.Configuration', {
                    configurationStore: me.configurationStore
                }),
                tab2,
                Ext.create('Shopware.apps.PostPluginConfiguration.view.detail.BankForm', {
                    bankStore: me.bankStore
                })
            ]
        });
    }
});
//{/block}
