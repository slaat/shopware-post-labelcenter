//{block name="backend/post_daily_statement/view/detail/configuration"}
Ext.define('Shopware.apps.PostDailyStatement.view.detail.Configuration', {
    extend: 'Ext.form.Panel',
    layout: 'column',
    cls: 'shopware-form',
    alias:'widget.post_daily_statement-configuration-panel',
    buttonAlign : 'center',

    formDefaults: {
        labelWidth: 155,
        style: 'margin-bottom: 10px !important;',
        labelStyle: 'font-weight: 700;',
        anchor: '100%',
    },

    snippets:{
        deliveryDate:  'Delivery date',
        form: 'Configuration'
    },

    initComponent: function() {
        var me = this;
        me.buttons = me.createButtons();
        me.callParent(arguments);
    },

    createButtons: function() {
        var me = this;
        me.createButton = Ext.create('Ext.button.Button', {
            text: 'Erstellen',
            action: 'create-document',
            cls:'primary',
            margin: '10 0',
            handler: function() {
              me.fireEvent('getDailyStatement', me.listStore) ;
            }
        });

        return [
            me.createButton
        ];
    }
});
//{/block}
