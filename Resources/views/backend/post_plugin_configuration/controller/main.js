//{block name="backend/post_plugin_configuration/controller/main"}
Ext.define('Shopware.apps.PostPluginConfiguration.controller.Main', {
    extend: 'Enlight.app.Controller',
    refs: [
        { ref: 'PostLabelsBankForm', selector: 'post_plugin_configuration-bank-form' },
        { ref: 'configForm', selector: 'post_plugin_configuration-panel-form' }
    ],
    init: function () {
        var me = this;

        me.control({
            'post_plugin_configuration-panel button[action=save]': {
                click: me.onSaveConfigForm
            },
            'post_plugin_configuration-bank-form button[action=save]': {
                click: me.onSaveBankForm
            }
        });

      me.mainWindow = me.getView('main.Window').create({
          listStore: me.getStore('Contract'),
          configurationStore: me.getStore('Configuration'),
          bankStore: me.getStore('Bank')
        }).show();
    },

    onSaveConfigForm: function(button) {
        var win = button.up('window'),
            formPanel  = win.down('form'),
            form       = formPanel.getForm(),
            record = form.getRecord();

        if (!form.isValid()) {
            return;
        }

        if (record === undefined) {
            record = Ext.create('Shopware.apps.PostPluginConfiguration.model.Configuration');
        }

        formPanel.setLoading(true);
        form.updateRecord(record);

        record.save({
            callback: function(record, response) {
                formPanel.setLoading(false);
                if (response.success) {
                    Shopware.Notification.createGrowlMessage('Speichern Erfolgreich');
                    Ext.Msg.alert('GESPEICHERT', 'Speichern Erfolgreich');
                    // reload data after successful saving
                    formPanel.loadRecord(record);
                    formPanel.store.load();
                } else {
                    Shopware.Notification.createGrowlMessage('Fehler beim Speichern!');
                    Ext.Msg.alert('Fehler beim Speichern!', response.error ? response.error : 'Konfiguration konnte nicht gespeichert werden!');
                }
            }
        });
    },

    onSaveBankForm: function(button) {
        var me = this;
        var formPanel  = button.up('form');
        var form = formPanel.getForm();
        var record = form.getRecord();

        if (!form.isValid()) {
            return;
        }

        if (record === undefined) {
            record = Ext.create('Shopware.apps.PostPluginConfiguration.model.Configuration');
        }

        formPanel.setLoading(true);
        form.updateRecord(record);
        var contractFormComponent = me.getConfigForm();
        var contractForm = contractFormComponent.up('form').getForm();
        var unitID = contractForm.findField('unitID').getValue();
        record.getProxy().extraParams.unitID = unitID;

        record.save({
            callback: function(record, response) {
                formPanel.setLoading(false);
                if (response.success) {
                    Shopware.Notification.createGrowlMessage('Speichern Erfolgreich');
                    Ext.Msg.alert('GESPEICHERT', 'Speichern Erfolgreich');
                    // reload data after successful saving
                    formPanel.loadRecord(record);
                    formPanel.store.load();
                } else {
                    Shopware.Notification.createGrowlMessage('Fehler beim Speichern!');
                    Ext.Msg.alert('Fehler beim Speichern!', response.error ? response.error : 'Konfiguration konnte nicht gespeichert werden!');
                }
            }
        });
    }
});
//{/block}
