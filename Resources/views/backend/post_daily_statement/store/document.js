//{block name="backend/post_daily_statement/store/document"}
Ext.define('Shopware.apps.PostDailyStatement.store.Document', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    pageSize: 20,
    model : 'Shopware.apps.PostDailyStatement.model.Document',

    listeners: {
        load: function(store, records, success, operation) {
            if (!records && !success) {
                var responseText = store.getProxy().getReader().rawData;
                if (!responseText.success) {
                    // show error to user
                    Ext.Msg.alert('Tagesabschluss Fehler', responseText.data.error.errorMessage + '\n' + responseText.data.error.errorMessageExtended);
                }
            }
        }
    }
});
//{/block}
