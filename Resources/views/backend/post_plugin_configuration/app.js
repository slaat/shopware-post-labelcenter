Ext.define('Shopware.apps.PostPluginConfiguration', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.PostPluginConfiguration',
    loadPath: '{url action=load}',
    bulkLoad: true,
    controllers: [ 'Main', 'Contract' ],
    views: [
        'main.Window'
    ],

  stores : [ 'Contract', 'Configuration'],

    launch: function () {
        return this.getController('Main').mainWindow;
    }
});
