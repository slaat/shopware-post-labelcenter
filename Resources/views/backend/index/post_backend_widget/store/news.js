//{block name="backend/index/post_backend_widget/store/news"}
Ext.define('Shopware.apps.Index.PostBackendWidget.store.News', {
    extend: 'Shopware.store.Listing',
    model: 'Shopware.apps.Index.PostBackendWidget.model.News',
    remoteSort: true,
    autoLoad: true,

    configure: function() {
        return {
            controller: 'PostBackendWidget'
        }
    }
});
//{/block}