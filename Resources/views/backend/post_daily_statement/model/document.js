//{block name="backend/post_daily_statement/model/document"}
Ext.define('Shopware.apps.PostDailyStatement.model.Document', {
    extend: 'Ext.data.Model',
    fields: [
        { name : 'id', type : 'int' },
        { name: 'date', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'timestamp', type: 'string' }
    ],

    proxy:{
      type:'ajax',
      url: '{url controller="postDailyStatement" action="loadDailyStatementList"}',
      reader:{
        type:'json',
        root:'data',
        totalProperty:'total'
      }
    },
});
//{/block}
