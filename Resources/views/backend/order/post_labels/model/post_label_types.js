//{block name="backend/order/post_labels/model/post_label_types"}
Ext.define('Shopware.apps.Order.PostLabels.model.PostLabelTypes',{
    extend:'Ext.data.Model',
    initComponent: function() {
        var me = this;
        me.callParent(arguments);
    },
    fields:[
        { name: 'type', type:'string' },
        { name: 'name', type:'string' }
    ]
});
//{/block}
