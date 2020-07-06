//{namespace name="backend/analytics/view/post_order_return"}
//{block name="backend/analytics/view/table/post_order_return"}
Ext.define('Shopware.apps.Analytics.view.table.PostOrderReturn', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-post_order_return',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1,
                sortable: false
            }
        };
		me.callParent(arguments);
    },

    getColumns: function () {
        return [
			{
				dataIndex: 'orderNumber',
				text: 'Bestellnummer'
			},
            {
                dataIndex: 'articleOrderNumber',
                text: 'Artikelnummer'
            },
			{
				dataIndex: 'articleName',
				text: 'Artikelname'
			},
			{
				dataIndex: 'amount',
				align: 'right',
				text: 'Menge'
			},
			{
				dataIndex: 'returnReason',
				text: 'Retourengrund'
			},
            {
                dataIndex: 'returnTime',
                text: 'Retourenzeit'
            }
        ];
    }
});
//{/block}
