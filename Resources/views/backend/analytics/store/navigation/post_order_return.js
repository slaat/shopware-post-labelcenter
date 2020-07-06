//{block name="backend/analytics/store/navigation/post_order_return"}
Ext.define('Shopware.apps.Analytics.store.navigation.PostOrderReturn', {
    extend: 'Ext.data.Store',
    alias: 'widget.analytics-store-navigation-post_order_return',
    remoteSort: true,
    fields: [
    	'orderNumber',
        'articleOrderNumber',
		'articleName',
		'amount',
		'returnReason',
        'returnTime'
	],
    proxy: {
        type: 'ajax',
        url: '{url controller=postOrderReturn action=getReturnedArticles}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}
