//{block name="backend/order/post_labels/store/post_labels"}
 Ext.define('Shopware.apps.Order.PostLabels.store.PostLabels', {
     extend: 'Ext.data.Store',
     model: 'Shopware.apps.Order.PostLabels.model.PostLabels',
     autoLoad: false,

     proxy: {
         type: 'ajax',
         url: '{url controller=postLabels action="loadOrderLabels"}',
         reader: {
             type: 'json',
             root: 'data',
             totalProperty: 'total'
         },
         //todo: check if needed
        listeners: {
            exception: function(response, proxy, operation) {
                Ext.Msg.alert('Post Labels Error', response.reader.rawData.data.error.errorMessage + '\n' + response.reader.rawData.data.error.errorMessageExtended);
                this.errors = response.reader.rawData.error;
            }
        }
     }
 });
//{/block}
