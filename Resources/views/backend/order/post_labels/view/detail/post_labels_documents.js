//{block name="backend/order/post_labels/view/detail/post_labels_documents"}
Ext.define('Shopware.apps.Order.PostLabels.view.detail.PostLabelsDocuments', {
    extend: 'Ext.container.Container',
    alias:'widget.order-post_labels-document-panel',
    autoScroll: true,

    snippets:{
        title: 'Labels',
        gridTitle: 'Genererierte Labels',
    },

    initComponent:function () {
        var me = this;

        me.postLabelDocumentStore = Ext.create('Shopware.apps.Order.PostLabels.store.PostLabels', {
            order: me.order
        });
        me.postLabelDocumentStore.getProxy().extraParams.orderNumber = me.record.get('number');
        me.postLabelDocumentStore.getProxy().extraParams.orderId = me.record.get('id');
        me.postLabelDocumentStore.getProxy().extraParams.shopID = 1;
        me.postLabelDocumentStore.load();

        me.items = [
            me.createDocumentGrid(),
            me.createDocumentForm()
        ];
        me.callParent(arguments);
    },

    createDocumentGrid: function() {
        var me = this;
        return Ext.create('Shopware.apps.Order.PostLabels.view.list.PostLabelsDocument', {
            store: me.postLabelDocumentStore,
            minHeight: 150,
            minWidth: 250,
            border: false,
            bodyBorder: 0,
            region: 'center',
            title: 'Generierte Labels',
            style: 'margin-bottom: 10px;',
            order: me.record
        });
    },

    createDocumentForm: function() {
        var me = this;
        me.documentForm = Ext.create('Shopware.apps.Order.PostLabels.view.detail.PostLabelsDocumentConfiguration', {
            region: 'bottom',
            record: me.record,
            store: me.postLabelDocumentStore
        });
        return me.documentForm;
    }
});
//{/block}
