//{block name="backend/post_plugin_configuration/model/configuration"}
Ext.define('Shopware.apps.PostPluginConfiguration.model.Configuration', {
    extend: 'Ext.data.Model',
    fields: [
        // { name: 'id', type : 'int' },
        { name: 'unitID', type: 'string' },
        { name: 'unitGUID', type: 'string' },
        { name: 'license', type: 'string' },
        { name: 'clientID', type: 'string' },
        { name: 'apiURL', type: 'string' },
        { name: 'returnTimeMax', type: 'string' },
        { name: 'returnReasons', type: 'string' },
        { name: 'contractNumbers', type: 'string' },
        { name: 'identifier', type: 'string' },
        { name: 'paperLayout', type: 'string' },
        { name: 'dataImportOnly', type: 'string' },
        { name: 'returnOrderAllowed', type: 'string' },
        { name: 'infoName', type: 'string' },
        { name: 'infoNameExtended', type: 'string' },
        { name: 'infoPhone', type: 'string' },
        { name: 'infoStreet', type: 'string' },
        { name: 'infoZip', type: 'string' },
        { name: 'infoCity', type: 'string' },
        { name: 'infoCountry', type: 'string' }
    ],
    validations: [
        { name: 'unitID', type: 'presence' },
        { name: 'unitGUID', type: 'presence' },
        { name: 'license', type: 'presence' },
        { name: 'clientID', type: 'presence' },
        { name: 'apiURL', type: 'presence' },
        { name: 'returnTimeMax', type: 'presence' },
        { name: 'returnReasons', type: 'presence' },
        { name: 'contractNumbers', type: 'presence' },
        { name: 'identifier', type: 'presence' },
        { name: 'paperLayout', type: 'presence' },
        { name: 'dataImportOnly', type: 'presence' },
        { name: 'returnOrderAllowed', type: 'presence' },
        { name: 'infoName', type: 'presence' },
        { name: 'infoStreet', type: 'presence' },
        { name: 'infoZip', type: 'presence' },
        { name: 'infoCity', type: 'presence' },
        { name: 'infoCountry', type: 'presence' }
    ],

    proxy: {
        type: 'ajax',
        api: {
            api: {
                update: '{url controller="postPluginConfiguration" action=savePluginConfiguration}'
            },
            read: '{url controller="postPluginConfiguration" action="getPluginConfiguration"}',
            create: '{url controller="postPluginConfiguration" action="savePluginConfiguration"}',
            update: '{url controller="postPluginConfiguration" action="savePluginConfiguration"}',
            destroy: '{url controller="postPluginConfiguration" action="getPluginConfiguration"}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
