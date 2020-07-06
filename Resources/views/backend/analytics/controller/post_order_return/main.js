//{namespace name="backend/analytics/view/post_order_return"}
//{block name="backend/analytics/controller/main"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Analytics.controller.PostOrderReturn.Main', {
	override: 'Shopware.apps.Analytics.controller.Main',
	init: function () {
		var me = this;
		me.callParent(arguments);
	},
});
//{/block}
