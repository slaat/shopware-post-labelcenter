//{block name="backend/post_plugin_configuration/model/bank"}
Ext.define('Shopware.apps.PostPluginConfiguration.model.Bank', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'bankAccountOwner', type: 'string' },
        { name: 'bankBic', type: 'string' },
        { name: 'accountIban', type: 'string' }
    ],
    validations: [
        { name: 'bankAccountOwner', type: 'presence' },
        { name: 'bankBic', type: 'presence' },
        { name: 'accountIban', type: 'presence' }
    ],

    proxy: {
        type: 'ajax',
        api: {
            api: {
                update: '{url controller="postPluginConfiguration" action=savePluginBank}'
            },
            read: '{url controller="postPluginConfiguration" action="getPluginBank"}',
            create: '{url controller="postPluginConfiguration" action="savePluginBank"}',
            update: '{url controller="postPluginConfiguration" action="savePluginBank"}',
            destroy: '{url controller="postPluginConfiguration" action="getPluginBank"}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
