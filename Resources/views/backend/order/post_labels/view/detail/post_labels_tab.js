//{block name="backend/order/post_labels/view/detail/post_labels_tab"}
Ext.define('Shopware.apps.Order.PostLabels.view.detail.PostLabelsTab', {
    extend: 'Ext.container.Container',
    title: 'Post Labels',
    layout: 'auto',
    padding: 10,
    autoScroll: true,

    initComponent: function() {
        var me = this;
        me.labelPanel = Ext.create('Ext.panel.Panel', {
            border: false
        });

        me.items  =  [
            me.labelPanel
        ];
        me.callParent(arguments);
    },

    afterShow: function() {
        var me = this;
        var panel =  me.labelPanel;
        var documentView = Ext.create('Shopware.apps.Order.PostLabels.view.detail.PostLabelsDocuments',
            {
                record: me.record,
                labelPanel: me.labelPanel
            });
        panel.removeAll();
        panel.add(documentView);
        panel.doLayout();
        me.callParent(arguments);
    }
});
//{/block}
