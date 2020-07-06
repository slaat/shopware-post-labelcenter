//{block name="backend/post_plugin_configuration/view/list/post_plugin_configuration_contract"}
Ext.define('Shopware.apps.PostPluginConfiguration.view.list.Contract', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.post_plugin_configuration-contract-list',
    cls: Ext.baseCSSPrefix + 'document-grid',
    autoScroll: true,
    overflow: 'scroll',
    stateful: true,
    minHeight: 50,

    snippets: {
        columns: {
            identifier: 'Kennung',
            orgUnitId: 'orgUnitID',
            orgUnitGUID: 'orgUnitGUID',
            license: 'ClientID'
        }
    },
    initComponent: function() {
        var me = this;
        me.store = me.listStore;
        me.store.load();
        me.columns = [
            { header: me.snippets.columns.identifier, dataIndex: 'identifier', flex: 1,  },
            { header: me.snippets.columns.license, dataIndex: 'license', flex: 1,  },
            { header: me.snippets.columns.orgUnitId, dataIndex: 'contractnumber', flex: 1,  },
            { header: me.snippets.columns.orgUnitGUID, dataIndex: 'unitGUID', flex: 1,  },
            me.createActionColumn()
        ];

        me.callParent(arguments);
    },

    createActionColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Action', {
            width: 90,
            items: [
                me.createContractDeleteColumn()
            ]
        });
    },

    createContractDeleteColumn: function() {
        var me = this;
        return {
            iconCls:'sprite-minus-circle-frame',
            tooltip: 'Kennung löschen?',

            handler:function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                    record = store.getAt(rowIndex);
                me.fireEvent('deleteContract', record);
            }
        };
    },

    registerEvents: function() {
        this.addEvents(
            'deleteContract'
        );
    }
});
//{/block}

