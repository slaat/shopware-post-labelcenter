//{block name="backend/post_plugin_configuration/model/contract"}
Ext.define('Shopware.apps.PostPluginConfiguration.model.Contract', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'contractnumber', type: 'string' },
        { name: 'unitGUID', type: 'string' },
        { name: 'license', type: 'string' },
        { name: 'identifier', type: 'string' }
    ],
    validations: [
        { field: 'contractnumber',  type: 'presence'},
        { field: 'unitID',  type: 'presence'},
        { field: 'license',  type: 'presence'},
        { field: 'identifier',  type: 'presence'}
    ],
    proxy: {
        type : 'ajax',
        api: {
            read: '{url controller="postPluginConfiguration" action="getPluginConfigurationContracts"}',
            destroy: '{url controller="postPluginConfiguration" action=deleteContract targetField=contractnumber}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    }
});
//{/block}

