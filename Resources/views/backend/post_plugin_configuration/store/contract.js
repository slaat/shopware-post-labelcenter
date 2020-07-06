//{block name="backend/post_plugin_configuration/store/contract"}
Ext.define('Shopware.apps.PostPluginConfiguration.store.Contract', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    pageSize: 20,
    model : 'Shopware.apps.PostPluginConfiguration.model.Contract',

    proxy:{
        type:'ajax',
        url: '{url controller="postPluginConfiguration" action="getPluginConfigurationContracts"}',
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }
});
//{/block}
