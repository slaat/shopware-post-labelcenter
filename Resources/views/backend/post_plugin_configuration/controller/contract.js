//{block name="backend/post_plugin_configuration/controller/contract"}
Ext.define('Shopware.apps.PostPluginConfiguration.controller.Contract', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'window', selector: 'post_plugin_configuration-main-window' },
        { ref: 'contractForm', selector: 'post_plugin_contract-panel' }
    ],

    init: function() {
        var me = this;
        me.control({
            'post_plugin_configuration-contract-list': {
                deleteContract: me.onDeleteContract
            },
            'post_plugin_contract-panel': {
                addContract: me.onAddContract
            }
        });
        me.callParent(arguments);
    },
    onDeleteContract: function(record) {

        var me = this,
            store = me.subApplication.getStore('Contract'),
            message = 'Wollen Sie die Kennung wirklich löschen?',
            title = 'Kennung löschen';

        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(title, message, function(response) {
            if (response !== 'yes') {
                return;
            }
            record.destroy({
                callback: function(data, operation) {
                    var records = operation.getRecords(),
                        record = records[0],
                        rawData = record.getProxy().getReader().rawData;

                    if (operation.success === true) {
                        //Shopware.Notification.createGrowlMessage(me.snippets.deleteOrder.successTitle, me.snippets.deleteOrder.successMessage, me.snippets.growlMessage);
                    } else {
                        //Shopware.Notification.createGrowlMessage(me.snippets.deleteOrder.failureTitle, me.snippets.deleteOrder.failureMessage + ' ' + rawData.message, me.snippets.growlMessage);
                    }
                    store.load();
                }
           });
        });
    },

    onAddContract: function (record, r2) {
        var me = this;

        var contractFormComponent = me.getContractForm();
        var contractForm = contractFormComponent.getForm();

        var contractNumber = contractForm.findField('unitID').getValue();
        var unitGUID = contractForm.findField('unitGUID').getValue();
        var license = contractForm.findField('license').getValue();
        var identifier = contractForm.findField('identifier').getValue();

        contractFormComponent.setLoading(true);

        if(!contractNumber || !unitGUID || !license || !identifier) {
            Ext.Msg.alert('Input Error', 'Bitte alle Werte eingeben!');
            contractFormComponent.setLoading(false);
            return;
        }

        Ext.Ajax.request({
            url: '{url controller=PostPluginConfiguration action="saveContractNumber"}',
            method: 'POST',
            params: {
                contractNumber: contractNumber,
                unitGUID: unitGUID,
                license: license,
                identifier: identifier
    },
            success: function(response) {
                contractFormComponent.setLoading(false);
                response = Ext.JSON.decode(response.responseText);
                if (response.success) {
                    Shopware.Notification.createGrowlMessage('Speichern Erfolgreich');
                    Ext.Msg.alert('GESPEICHERT', 'Speichern Erfolgreich');
                    // reload data after successful saving
                    me.subApplication.getStore('Contract').load();
                } else {
                    Shopware.Notification.createGrowlMessage('Kombination von Features nicht erlaubt', response.message);
                    Ext.Msg.alert('Contract number saving failure', response.data.error.errorMessage + '\n' + response.data.error.errorMessageExtended);
                }
            }
        });
    }
});
//{/block}
