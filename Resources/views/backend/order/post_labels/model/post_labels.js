//{block name="backend/order/post_labels/model/post_labels"}
Ext.define('Shopware.apps.Order.PostLabels.model.PostLabels',{
    extend:'Ext.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'date', type:'string' },
        { name: 'name', type:'string'},
        { name: 'type', type:'string'},
        { name: 'downloaded', type:'boolean'},
        { name: 'orderNumber', type:'int'},
        { name: 'orderId', type: 'int' } // needed because of performance
    ],

    proxy: {
        type : 'ajax',
        api: {
            url: '{url controller=postLabels action="loadOrderLabels"}',
            destroy: '{url controller="postLabels" action=deletePostLabel}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    }
});
//{/block}
