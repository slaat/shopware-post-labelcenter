//{block name="backend/order/post_labels/store/post_label_types"}
 Ext.define('Shopware.apps.Order.PostLabels.store.PostLabelTypes', {
     extend: 'Ext.data.Store',
     model: 'Shopware.apps.Order.PostLabels.model.PostLabelTypes',
     autoLoad: true,
     data: [{
         type: 'label',
         name: 'Label'
     }]
 });
//{/block}
