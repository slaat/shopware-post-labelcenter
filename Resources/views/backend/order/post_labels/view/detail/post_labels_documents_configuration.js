//{namespace name=backend/order/post_labels}
//{block name="backend/order/post_labels/view/detail/post_labels_documents_configuration"}
Ext.define('Shopware.apps.Order.PostLabels.view.detail.PostLabelsDocumentConfiguration', {
    extend: 'Ext.form.Panel',
    layout: 'column',
    alias:'widget.shipping_post_labels_document_configuration-panel',
    border: false,
    bodyBorder: 0,
    formDefaults: {
        labelWidth: 155,
        style: 'margin-bottom: 10px !important;',
        labelStyle: 'font-weight: 700;',
        anchor: '100%'
    },

    snippets:{
        documentType:  'Document type',
        buttons: {
            create: 'Zusätzliches Label generieren'
        },
        form: 'Konfiguration'
    },

    initComponent: function() {
        var me = this;
        var labelTypes = Ext.create('Ext.data.Store', {
            fields: ['value', 'name'],
            data : [
                { "value":"SHIPPING_LABEL", "name":"Versand Label" },
                { "value":"RETURN_LABEL", "name":"Retouren Label" }
            ]
        });

        me.items = [
            {
                xtype: 'combobox',
                fieldLabel: 'Label Typ',
                name: 'labelType',
                store: labelTypes,
                queryMode: 'local',
                displayField: 'name',
                valueField: 'value',
                forceSelection: true,
                allowBlank: false,
                triggerAction: 'all',
                value: labelTypes.getAt(0).get('value'),
                renderTo: Ext.getBody()
            },
            {
                xtype: 'button',
                text: me.snippets.buttons.create,
                cls: 'primary',
                style: 'float: right',
                action: 'getAdditionalLabel',
                handler: function () {
                    me.fireEvent('getAdditionalLabel', me.record, me.store);
                }
            }
        ];
        me.callParent(arguments);
    }
});
//{/block}
