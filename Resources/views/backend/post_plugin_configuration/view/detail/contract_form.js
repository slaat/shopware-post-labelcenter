//{block name="backend/post_plugin_configuration/view/detail/contract_form"}
Ext.define('Shopware.apps.PostPluginConfiguration.view.detail.ContractForm', {
    extend:'Ext.form.Panel',
    layout: 'fit',
    cls: 'shopware-form',
    alias:'widget.post_plugin_contract-panel',
    columnWidth: 1,
    stateful: true,
    border: false,
    defaults: {
        anchor: '100%'
    },

    initComponent: function() {
        var me = this;
        me.items =  [
            me.createContainer()
        ];
        me.callParent(arguments);
    },

    createContainer: function () {

        var container, me = this;
        container = Ext.create('Ext.container.Container', {
            layout: 'fit',
            border: false,
            items: me.createContractFormElements()
        });

        return container;
    },

    createContractFormElements:function () {
        var me = this;
        var leftContainer = Ext.create('Ext.form.FieldSet', {
            layout: 'column',
            anchor: '100%',
            align: 'stretch',
            bodyPadding: 5,
            title: 'Zusätzliche Kennungen:',

            defaults: {
                columnWidth: 0.25,
                margin: '10 0 10 10',
                labelWidth: 70
            },

            items: [
                {
                    xtype: 'label',
                    name: 'description',
                    columnWidth: 1,
                    margin: '10 0 0',
                    text: 'Derzeit keine Funktion bei verschiedenen Debitoren.',
                    labelStyle: 'font-weight: 700;',
                },
                {
                    xtype: 'textfield',
                    name: 'identifier',
                    fieldLabel: 'Kennung',
                    labelStyle: 'font-weight: 700;'
                },
                {
                    xtype: 'textfield',
                    name: 'license',
                    fieldLabel: 'ClientID',
                    labelStyle: 'font-weight: 700;'
                },
                {
                    xtype: 'textfield',
                    name: 'unitID',
                    fieldLabel: 'OrgUnitID',
                    labelStyle: 'font-weight: 700;',
                    margin: '10 0 0 20 0',
                },
                {
                    xtype: 'textfield',
                    name: 'unitGUID',
                    fieldLabel: 'OrgUnitGUID',
                    labelStyle: 'font-weight: 700;'
                },
                {
                    xtype: 'button',
                    text: 'Hinzufügen',
                    cls:'primary',
                    style: 'float: right',
                    handler: function() {
                        me.fireEvent('addContract') ;
                    }
                }
            ]
        });

        return [leftContainer];
    },

    createButtons: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: 'Hinzufügen',
            cls:'primary',
            anchor: 'right',
            columnWidth: 0.25,
            handler: function() {
                me.fireEvent('addContract') ;
            }
        });
    }

});
//{/block}
