//{block name="backend/order/controller/batch"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Order.PostShipment.controller.Batch', {

  override: 'Shopware.apps.Order.controller.Batch',

    onProcessChanges: function(form) {
      var me = this;
      me.callParent(arguments);
      // compare old & new status
      var orders = form.records;

      Ext.each(orders, function (order) {
        if (Ext.isDefined(order.data.status) && Ext.isDefined(order.raw.status)) {

          var currentOrderStatusID = order.data.status;
          var previousOrderStatusID = order.raw.status;

          // todo shitty problem if only one history row - find solution
          if (currentOrderStatusID === 5 && previousOrderStatusID !== 5) {
              me.importShipment(order);
          }
        }
      });
    },

  importShipment: function(order) {
    var me = this;
    Ext.Ajax.request({

      url:'{url controller="PostOrder" action="ImportShipment"}',
      params: {
          'ordernumber': order.get('number'),
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
  },
    getPostImportShipmentErrorMessage: function (title, defaultText = "", operation) {

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
