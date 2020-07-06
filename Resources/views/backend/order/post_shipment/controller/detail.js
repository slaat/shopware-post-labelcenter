//{block name="backend/order/controller/detail"}
//{$smarty.block.parent}
// we are overriding me.onSaveDetails to catch save status from save action
// we don't send ImportShipment request if saving fails

Ext.define('Shopware.apps.Order.PostShipment.controller.Detail', {
  override: 'Shopware.apps.Order.controller.Detail',

  onUpdateDetailPage: function(order, window) {
    var me = this;
    me.callParent(arguments);

    var history = window.down('order-history-list').store.data.items;
    var attributes = window.down('order-overview-panel').attributeForm;

    var customsDescription = attributes.getForm().findField('__attribute_customsdescription').getValue();
    var currentOrderStatusID = false;
    var previousOrderStatusID = false;
    var historyExists = false;

    if (history && history.length) {
        historyExists = true;
        var lastHistoryEntry = history[0];
        currentOrderStatusID = lastHistoryEntry.data.currentOrderStatusId;
        previousOrderStatusID = lastHistoryEntry.data.prevOrderStatusId;
    } else {
        // currentOrderStatusID = order.getOrderStatusStore.first().get('id');
        // previousOrderStatusID = order.getOrderStatusStore.first().get('id');
        currentOrderStatusID = order.data.status;
        previousOrderStatusID = order.data.status;
    }

        // todo - problem with order store reload - not shopware way of doing backend
    if ( (!historyExists && currentOrderStatusID === 5) || (historyExists && currentOrderStatusID === 5 && previousOrderStatusID !== 5 )) {
      //call webservice

        Ext.Ajax.request({

            url:'{url controller="PostOrder" action="ImportShipment"}',
            params: {
                'ordernumber': order.get('number'),
                'statuschange' : true,
                'customsDescription': customsDescription
            },

            success: function(result) {
                var operation =  Ext.JSON.decode(result.responseText);
                if (operation.success === true) {
                    Shopware.Notification.createGrowlMessage('Import Shippment OK', 'Bestellung wurde erfolgreich importiert.');
                } else {
                    me.getPostImportShipmentErrorMessage('Import Shipment FAILURE', 'Fehler beim Importieren der Bestellung.', operation);
                }
            }
        });
    }
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
