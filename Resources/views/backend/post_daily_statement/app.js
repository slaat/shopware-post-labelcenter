Ext.define('Shopware.apps.PostDailyStatement', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.PostDailyStatement',
    loadPath: '{url action=load}',
    bulkLoad: true,
    controllers: [ 'Main' ],
    views: [
        'main.Window'
    ],
    stores : [ 'Document' ],
    launch: function () {
        return this.getController('Main').mainWindow;
    }
});
