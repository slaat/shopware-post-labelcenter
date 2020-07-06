//{block name="backend/post_plugin_configuration/store/bank"}
Ext.define('Shopware.apps.PostPluginConfiguration.store.Bank', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    pageSize: 20,
    model : 'Shopware.apps.PostPluginConfiguration.model.Bank',

    proxy: {
        type: 'ajax',
        url: '{url controller="postPluginConfiguration" action="getPluginBank"}',
        // api: {
        //     update: '{url controller="postPluginConfiguration" action=savePluginBank}'
        // },
        reader: {
            type: 'json',
            root: 'data'
            //totalProperty:'total'
        }
    }
});
//{/block}
