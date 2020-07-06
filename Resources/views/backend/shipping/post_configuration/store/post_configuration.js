//{block name="backend/shipping/post_configuration/store/post_configuration"}
Ext.define('Shopware.apps.Shipping.PostConfiguration.store.PostConfiguration', {
    extend: 'Ext.data.Store',
    model : 'Shopware.apps.PostConfiguration.model.PostConfiguration',
    autoLoad: false,
    proxy: {
        type:'ajax',
        url: '{url controller="postShippingConfiguration" action="loadShippingConfigurations"}',
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    },
    listeners: {
        load: function (store, records, params) {

            var responseText = store.getProxy().getReader().rawData;
            if (!responseText.success) {
                // show error to user
                Ext.Msg.alert('Fehler - Keine Kennung gefunden!', responseText.data.error.errorMessage + '\n' + responseText.data.error.errorMessageExtended);
            }
        }
    }
});
//{/block}
