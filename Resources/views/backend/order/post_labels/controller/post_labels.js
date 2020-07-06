//{block name="backend/order/post_labels/controller/post_labels"}
Ext.define('Shopware.apps.Order.PostLabels.controller.PostLabels', {
    extend: 'Enlight.app.Controller',

    snippets: {
        deletePostLabel: {
            title: 'Label stornieren',
            message: 'Wollen Sie das Label wirklich stornieren?',
            successTitle: 'Erfolg',
            successMessage: 'Das Label wurde storniert.',
            failureTitle: 'Fehler',
            failureMessage: 'Während des Stornierungvorganges ist ein Fehler aufgetreten.'
        }
    },

    refs: [
        { ref: 'postLabelDocumentsPanel', selector: 'order-post_labels-document-panel' }
    ],

    init: function() {
        var me = this;
        me.control({
            'shipping_post_labels_document_configuration-panel': {
                getAdditionalLabel: me.getAdditionalLabel
            },
            'order-post_labels-document-list': {
                deletePostLabel: me.deletePostLabel
            }
        });
    },

    getAdditionalLabel: function (record, store) {
        var me = this;
        var labelType = me.getPostLabelDocumentsPanel();
        labelType = labelType.documentForm.getForm().getValues();
        labelType = labelType.labelType;
        me.getPostLabelDocumentsPanel().setLoading(true);

        if (!record || !record.get('number') || !store) {
            me.getPostLabelDocumentsPanel().setLoading(false);
            Shopware.Notification.createGrowlMessage('Label generierung Fehler ', 'Request Parameter sind nicht vorhanden!');
        }

        Ext.Ajax.request({
            url:'{url controller="PostOrder" action="ImportShipment"}',
            params: {
                'ordernumber': record.get('number'),
                'labeltype': labelType
            },
            success: function(result) {
                me.getPostLabelDocumentsPanel().setLoading(false);
                var operation =  Ext.JSON.decode(result.responseText);
                if (operation.success === true) {
                    Shopware.Notification.createGrowlMessage('Label wurde generiert', 'Label wurde erfolgreich generiert.');
                    store.load();
                } else {
                    if(operation.data.isMessage) {
                        var title = operation.data.error.length > 1 ? 'Warnung! Fehler beim generieren von Label' : operation.data.error[0].errorMessage;
                        var message = "";
                        var openTag = operation.data.error.length > 1 ? "<li style='list-style: decimal; padding-left: 14px'>" : "";
                        var closeTag = operation.data.error.length > 1 ? "</li>" : "";
                        operation.data.error.forEach(function(error) {
                            message = message + openTag + error.errorMessageExtended + closeTag;
                        });
                        message = operation.data.error.length > 1 ? "<ol style='list-style: decimal; padding-left: 14px'>" + message + "</ol>" : message;
                        Ext.Msg.alert(title, message);
                    }
                    Shopware.Notification.createGrowlMessage('Label generierung Fehler ', 'Fehler beim generieren von Label!');
                }
            }
        });
    },

    deletePostLabel: function(record, store, order) {

        var me = this;
        var message = me.snippets.deletePostLabel.message,
            title = me.snippets.deletePostLabel.title;

        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(title, message, function(response) {
            if (response !== 'yes') {
                return;
            }

            me.getPostLabelDocumentsPanel().setLoading(true);

            Ext.Ajax.request({
                url:'{url controller="PostOrder" action="cancelShipment"}',
                params: {
                    'documentId': record.get('id'),
                    'orderId': order.get('id')
                },
                success: function(result) {
                    me.getPostLabelDocumentsPanel().setLoading(false);
                    var operation =  Ext.JSON.decode(result.responseText);
                    if (operation.success === true) {
                        Shopware.Notification.createGrowlMessage('Label wurde gelöscht', 'Label wurde erfolgreich gelöscht.');
                        me.removeLabel(record);
                        store.load();
                    } else {
                        Shopware.Notification.createGrowlMessage('Label Löschen Fehler ', 'Fehler beim Löschen von Label!');
                    }
                }
            });

        });
    },

    removeLabel: function(record) {
        var me = this;
        record.destroy({
            callback: function(data, operation) {
                var records = operation.getRecords(),
                    record = records[0],
                    rawData = record.getProxy().getReader().rawData;
                if (operation.success === true) {
                    Shopware.Notification.createGrowlMessage(me.snippets.deletePostLabel.successTitle, me.snippets.deletePostLabel.successMessage, me.snippets.growlMessage);
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.deletePostLabel.failureTitle, me.snippets.deletePostLabel.failureMessage /*+ ' ' + rawData.message*/, me.snippets.growlMessage);
                }
            }
        });

    }

});
//{/block}
