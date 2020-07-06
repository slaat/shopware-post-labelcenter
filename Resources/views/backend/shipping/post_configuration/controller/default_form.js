//{block name="backend/shipping/post_configuration/controller/default_form"}
Ext.define('Shopware.apps.Shipping.PostConfiguration.controller.DefaultForm', {
  override : 'Shopware.apps.Shipping.controller.DefaultForm',

  // todo: create async event to save only configuration part of form - original form should be saved without interruption
  onDispatchSave : function(button, event) {
    var me = this;
    me.onSavePostConfigurationForm(button, event);
    me.callParent(arguments);
  },

    // todo catch only tab save
  onSavePostConfigurationForm: function(button, event) {
    var me = this;
    var mainForm = me.getMainFormData(button);
    var dispatchID = mainForm.getRecord().get('id');
    var confForm = button.up('window').down('shipping-post_configuration-view-edit-post_configuration_form');

    if (confForm && confForm.activeRecord) {
        var activeProduct = confForm.activeRecord.activeProduct;
    } else {
        return false;
    }

    if (!activeProduct) {
        // to implement if needed
        Shopware.Notification.createGrowlMessage('Achtung', 'Es wurde kein Produkt ausgewählt!');
    }
    else {
        activeProduct.productID = activeProduct.productID.toString();
        activeProduct = Ext.JSON.encode([activeProduct]);

        Ext.Ajax.request({
            url: '{url controller=PostShippingConfiguration action="savePostConfiguration"}',
            method: 'POST',
            params: {
                dispatchID: dispatchID,
                contractNumber: confForm.activeRecord.data.contractnumber,
                record: activeProduct
            },
            success: function (response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    Shopware.Notification.createGrowlMessage('Speichern Erfolgreich');
                    Ext.Msg.alert('GESPEICHERT', 'Speichern Erfolgreich');
                    // reload data after successful saving
                    //confForm.configStore.load();
                } else {
                    Shopware.Notification.createGrowlMessage('Kombinationen von Features sind nicht erlaubt!', status.message);
                    Ext.Msg.alert('NICHT GESPEICHERT', 'Kombinationen von Features sind nicht erlaubt!');
                }
            }
        });
    }
  }
});
//{/block}
