//{block name="backend/post_plugin_configuration/view/detail/configuration_form"}
Ext.define('Shopware.apps.PostPluginConfiguration.view.detail.ConfigurationForm', {
    extend:'Ext.container.Container',
    cls: 'shopware-form',
    alias:'widget.post_plugin_configuration-panel-form',
    border: false,
    defaults: {
        anchor: '100%'
    },
    initComponent: function() {
        var me = this;
        me.items = me.createContainer();
        me.callParent(arguments);
    },

    createContainer: function () {
        var leftContainer, rightContainer, me = this;

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
                store: me.store,
                xtype: 'textfield',
                allowBlank : false
            },
            items: me.createLeftElements()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            columnWidth:0.5,
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                labelWidth: 155,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px 15px'
                },
                store: me.store,
                xtype: 'textfield',
                allowBlank : false
            },
            items: me.createRightElements()
        });

        var topFieldSet = Ext.create('Ext.form.FieldSet', {
            layout: 'column',
            bodyPadding: 5,
            columnWidth: 1,
            stateful: true,
            title: 'Plugin Grundeinstellungen',
            items: [leftContainer, rightContainer]
        });

        var infoContainerTop = Ext.create('Ext.container.Container', {
            columnWidth: 1,
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                labelWidth: 155,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                store: me.store,
                xtype: 'textfield',
                allowBlank : true
            },
                items: [{
                    name:'infoName',
                    fieldLabel: 'Name'
                },
                {
                    name:'infoNameExtended',
                    fieldLabel: 'Name 2'
                },
                {
                    name:'infoPhone',
                    fieldLabel: 'Telefonnummer (Pflichtfeld bei EMS)'
                },

                ]
        });

        var elements = me.createInfoContainerBottomElements();

        var infoContainerBottom = Ext.create('Ext.container.Container', {
            columnWidth: 1,
            border: false,
            layout: 'column',
            defaults: {
                anchor: '100%',
                labelWidth: 155,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                store: me.store,
                xtype: 'textfield',
                allowBlank : false
            },
            items: elements
        });

        var bottomFieldSet = Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            bodyPadding: 5,
            columnWidth: 1,
            stateful: true,
            title: 'Absenderdaten',
            items: [infoContainerTop, infoContainerBottom]
        });
        return [topFieldSet, bottomFieldSet];
    },

    createLeftElements: function () {
        return [
            {
                name:'identifier',
                fieldLabel: 'Kennung'
            },
            {
                name:'clientID',
                fieldLabel: 'ClientID'
            },
            {
                name:'unitID',
                fieldLabel: 'OrgUnitID'
            },
            {
                name:'unitGUID',
                fieldLabel: 'OrgUnitGUID'
            }

        ];
    },

    createRightElements: function () {

        return [
            {
                name:'paperLayout',
                fieldLabel: 'Etikettenformat',
                xtype: 'combobox',
                queryMode: 'local',
                displayField: 'name',
                valueField: 'value',
                store: Ext.create('Ext.data.Store', {
                    fields : ['name', 'value'],
                    data : [
                        { name: '2xA5inA4', value: '2xA5inA4'},
                        { name: 'A5', value: 'A5'},
                        { name: 'A4', value: 'A4'}
                    ]
                })
            },
            {
                xtype: 'checkbox',
                fieldLabel: 'Nur Datenimport (ohne PDF)',
                uncheckedValue: false,
                name: 'dataImportOnly'
            },
            {
                xtype: 'checkbox',
                fieldLabel: 'Frontend Retouren aktiviert',
                uncheckedValue: false,
                name: 'returnOrderAllowed'
            },
            {
                name:'apiURL',
                fieldLabel: 'API Url',
                xtype: 'combobox',
                queryMode: 'local',
                displayField: 'name',
                valueField: 'value',
                store: Ext.create('Ext.data.Store', {
                    fields : ['name', 'value'],
                    data : [
                        { name: 'Live', value: 'https://plc-ecommerce-api.post.at/api/'},
                        { name: 'Abnahme', value: 'https://abn-plc-ecommerce-api.post.at/api/'},
                    ]
                })
            },
            {
                name:'returnTimeMax',
                fieldLabel: 'Maximaler Retourenzeitraum'
            },
            {
                name:'returnReasons',
                fieldLabel: 'Retourengründe'
            }
        ];
    },

    createInfoContainerBottomElements: function () {
        var leftCont = Ext.create('Ext.container.Container',{
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
            items: [
                {
                    name:'infoStreet',
                    fieldLabel: 'Straße/Hausnummer'
                },
                {
                    name: 'infoCity',
                    fieldLabel: 'Ort'
                }
            ]
        });
        var rightCont = Ext.create('Ext.container.Container',{
            columnWidth: 0.5,
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                labelWidth: 155,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px 15px'
                },
                xtype: 'textfield',
                allowBlank : false
            },
            items: [
                {
                    name: 'infoZip',
                    fieldLabel: 'Postleitzahl'
                },
                {
                    name: 'infoCountry',
                    fieldLabel: 'Land',
                    disabled: true,
                    value: 'AT'
                }
            ]
        });
        return [leftCont, rightCont];
    }
});
//{/block}
