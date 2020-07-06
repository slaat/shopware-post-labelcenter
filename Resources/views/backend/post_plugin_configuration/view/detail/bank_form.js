//{block name="backend/post_plugin_configuration/view/detail/bank_form"}
Ext.define('Shopware.apps.PostPluginConfiguration.view.detail.BankForm', {
    extend:'Ext.form.Panel',
    cls: 'shopware-form',
    alias:'widget.post_plugin_configuration-bank-form',
    title: 'Bankdaten',
    bodyPadding: 10,
    border: false,
    defaults: {
        anchor: '100%'
    },

    initComponent: function() {
        var me = this;
        me.bbar = me.createToolbar();
        me.store = me.bankStore;
        me.store.load({
            callback: function (records, operation, success) {
                me.getForm().loadRecord(me.store.first());
            }
        });

        me.items = me.createContainer();
        me.callParent(arguments);
    },

    createContainer: function () {
        var leftContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                labelWidth: 155,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                xtype: 'textfield',
                allowBlank : false
            },
            items: me.createLeftElements()
        });

        var topFieldSet = Ext.create('Ext.form.FieldSet', {
            layout: 'column',
            bodyPadding: 5,
            columnWidth: 1,
            stateful: true,
            title: 'Bankdaten',
            items: [leftContainer]
        });

        return [topFieldSet];
    },

    createLeftElements: function () {
        return [
            {
                name:'bankAccountOwner',
                fieldLabel: 'Kontoinhaber'
            },
            {
                name:'bankBic',
                fieldLabel: 'BIC'
            },
            {
                name:'accountIban',
                fieldLabel: 'IBAN'
            }
        ];
    },
    createToolbar: function() {
        var me = this;
        me.saveButton = Ext.create('Ext.button.Button', {
            cls:'primary',
            text: 'Speichern',
            action: 'save'
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            border: false,
            items: [
                { xtype: 'tbfill' },
                me.saveButton
            ]
        });

    }
});
//{/block}
