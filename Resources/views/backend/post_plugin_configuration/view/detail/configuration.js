//{block name="backend/post_plugin_configuration/view/detail/configuration"}
Ext.define('Shopware.apps.PostPluginConfiguration.view.detail.Configuration', {
    extend: 'Ext.form.Panel',
    layout: 'column',
    cls: 'shopware-form',
    alias:'widget.post_plugin_configuration-panel',
    autoScroll: 'true',
    stateful: true,
    title: 'Plugin Grundeinstellungen',
    bodyPadding: 10,
    border: 0,

    initComponent: function() {
        var me = this;
        me.bbar = me.createToolbar();
        me.store = me.configurationStore;
        me.store.load({
            callback: function (records, operation, success) {
                me.getForm().loadRecord(me.store.first());
            }
        });

        me.items =  [
            me.createConfigurationForm()
        ];

        me.callParent(arguments);
    },

    createConfigurationForm: function () {
        var me = this;
        me.configurationForm = Ext.create('Shopware.apps.PostPluginConfiguration.view.detail.ConfigurationForm', {
            store: me.store
        });
        return me.configurationForm;
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
