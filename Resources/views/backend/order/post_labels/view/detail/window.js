//{block name="backend/order/view/detail/window"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Order.PostLabels.view.detail.Window', {
    override: 'Shopware.apps.Order.view.detail.Window',
    cls: 'post-labels-main-window',

    initComponent: function () {
        var me = this;
        me.addCustomController();
        me.callParent(arguments);
    },
    createTabPanel: function () {
        var me = this;
        var tabPanel = me.callParent(arguments);
        tabPanel.insert(tabPanel.items.length, me.createPostLabelsTabPanel());
        return tabPanel;
    },
    createPostLabelsTabPanel: function () {
        var me = this;
        return Ext.create('Shopware.apps.Order.PostLabels.view.detail.PostLabelsTab', {
            record: me.record
        });
    },
    addCustomController: function () {
        var me = this;
        var controllerInitialized = false;
        Ext.each(me.subApp.controllers.items, function(controller) {
            if (controller.id == 'PostLabels') {
                controllerInitialized = true;
            }
        });

        if (controllerInitialized) {
            return;
        }

        me.controller = Ext.create('Shopware.apps.Order.PostLabels.controller.PostLabels', {
            application: me.subApp,
            subApplication: me.subApp,
            id: 'PostLabels',
            subApp: me.subApp,
            $controllerId: 'PostLabels',
            configure: function () {
                return {
                    detailWindow: me.$className,
                    eventAlias: me.eventAlias
                }
            }
        });
        me.controller.init();
        me.subApp.controllers.add(me.controller.$controllerId, me.controller);
        return me.controller;
    }
});
//{/block}
