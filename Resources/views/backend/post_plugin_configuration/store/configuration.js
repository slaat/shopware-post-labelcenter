//{block name="backend/post_plugin_configuration/store/configuration"}
Ext.define('Shopware.apps.PostPluginConfiguration.store.Configuration', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    pageSize: 20,
    model : 'Shopware.apps.PostPluginConfiguration.model.Configuration',

    proxy: {
        type: 'ajax',
        url: '{url controller="postPluginConfiguration" action="getPluginConfiguration"}',
        api: {
            update: '{url controller="postPluginConfiguration" action=savePluginConfiguration}'
        },
        reader: {
            type: 'json',
            root: 'data',
            //totalProperty:'total'
        }
    }
});
//{/block}
