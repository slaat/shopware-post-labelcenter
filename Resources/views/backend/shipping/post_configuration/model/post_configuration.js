//{block name="backend/shipping/post_configuration/model/post_configuration"}
Ext.define('Shopware.apps.PostConfiguration.model.PostConfiguration', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'contractnumber', type: 'string' },
        { name: 'identifier', type: 'string' },
        { name: 'products', type: 'string'},
        { name: 'savedcontractnumber', type: 'string'}
    ]
});
//{/block}
