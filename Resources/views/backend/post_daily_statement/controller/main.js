//{block name="backend/post_daily_statement/controller/main"}
Ext.define('Shopware.apps.PostDailyStatement.controller.Main', {
    extend: 'Enlight.app.Controller',
    init: function () {
        var me = this;
        me.control({
            'post_daily_statement-configuration-panel': {
                getDailyStatement: me.onGetDailyStatement,
                getDailyStatementList: me.getDailyStatementList
            }
        });
        me.mainWindow = me.getView('main.Window').create({
          listStore: me.getStore('Document')
        }).show();
    },

    onGetDailyStatement: function(record, store) {
        var me = this;
        me.mainWindow.setLoading(true);
        Ext.Ajax.request({
            url: '{url controller="postdailystatement" action="getdailystatement"}',
            method: 'POST',
            success: function(response) {
                me.mainWindow .setLoading(false);
                response = Ext.JSON.decode(response.responseText);
                if (!response.success) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name=document/attachemnt/error}Error{/s}',
                        response.data.error.errorMessageExtended
                    );
                    Ext.Msg.alert('Post Tracking Error', response.data.error.errorMessage + '\n' + response.data.error.errorMessageExtended );
                }
                // is data import only (without pdf)
                if (response.data.dataimportonly) {
                    Ext.Msg.alert('Bestellimport OK', 'Tagesabschluss wurde erfolgreich übermittelt.' );
                }
                me.getDailyStatementList();
            },
            failure: function(response) {
                me.mainWindow .setLoading(false);
                Shopware.Notification.createGrowlMessage(
                    '{s name=document/attachemnt/error}Error{/s}',
                    response.status + '<br />' + response.statusText
                );
            }
        });
    },
    getDailyStatementList: function() {
        var me = this;
        // hack for initial load if there are no existing documents
        me.getStore('Document').getProxy().extraParams.byClick = true;
        me.getStore('Document').load();
        delete me.getStore('Document').getProxy().extraParams.byClick;
    }
});
//{/block}
