//{block name="backend/order/controller/list"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Order.PostShipment.controller.List', {

  override: 'Shopware.apps.Order.controller.List',

  onSaveOrder: function(editor, event, store) {
      var me = this;
      var record = event.record;
      var currentOrderStatusID = record.data.status;
      var previousOrderStatusID = record.raw.status;

      // todo: check if new status is consequence of sucessful saving
      if (currentOrderStatusID && currentOrderStatusID === 5 && previousOrderStatusID !== 5 ) {
      //call webservice
        Ext.Ajax.request({

          url:'{url controller="PostOrder" action="ImportShipment"}',
          params: {
              'ordernumber': record.get('number'),
              'statuschange' : true
          },
          success: function(result) {

            var operation =  Ext.JSON.decode(result.responseText);
            if (operation.success === true) {
              Shopware.Notification.createGrowlMessage('Import Shipment OK', 'Bestellung wurde erfolgreich importiert.');

            } else {
                me.getPostImportShipmentErrorMessage('Import Shipment FAILURE', 'Fehler beim Importieren der Bestellung.', operation);
            }
          }
        });
      }
      return me.callParent(arguments);
  },
    getPostImportShipmentErrorMessage: function (title = "", defaultText = "", operation) {

        if(Array.isArray(operation.data.error)) {
            operation.data.error.forEach(function(element) {
                var errorMessage = ' ';
                if (element.errorMessage) {
                    errorMessage = '\n' + errorMessage + '\n' + element.errorMessage;
                }
                if (element.errorMessageExtended) {
                    errorMessage = errorMessage + '\n' + element.errorMessageExtended;
                }

                Shopware.Notification.createGrowlMessage(title, defaultText + errorMessage);
            });
        } else {
            var errorMessage = ' ';
            if (operation.data.error.errorMessage) {
                errorMessage = '\n' + errorMessage + '\n' + operation.data.error.errorMessage;
            }
            if (operation.data.error.errorMessageExtended) {
                errorMessage = errorMessage + '\n' + operation.data.error.errorMessageExtended;
            }
            Shopware.Notification.createGrowlMessage(title, defaultText + errorMessage);
        }

        return errorMessage;
    }

});
//{/block}
