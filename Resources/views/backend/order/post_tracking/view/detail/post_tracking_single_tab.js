//{block name="backend/order/post_tracking/view/detail/post_tracking_single_tab"}
Ext.define('Shopware.apps.Order.PostTracking.view.detail.PostTrackingSingleTab', {
    extend: 'Ext.container.Container',
    title: 'Post Tracking',
    autoShow: true,
    stateful: true,
    currentStep: 0,

    loader: {
        url: '{url controller=PostTracking action=getTracking}'
    },

    initComponent: function() {
        var me = this;
        me.trackingWindow = me.up('window');

        me.panel =  Ext.create('Ext.panel.Panel', {
            tpl: me.createTrackingTemplate(),
            trackingData: {},
            trackingCode: me.trackingCode,
            // renderTpl: tpl,
            autoShow: true,
            autoRender: true,
            renderBlock: true,
            border: 0,
            bodyBorder: 0,
            renderTo: Ext.getBody()
        });
        me.items = [
           me.panel
        ];
        me.callParent(arguments);
    },

    afterRender: function () {
        var me = this;
        me.trackingWindow = me.up('window');
        me.trackingWindow.setLoading(true);
        me.getTrackingData();
        // me.trackingWindow.setLoading(false);
        me.callParent(arguments);
    },

    afterShow: function () {
        var me = this;
        me.trackingWindow.setLoading(true);
        if (!me.panel.renderBlock) {
            me.getTrackingData();
        }
        if (me.panel.renderBlock) {
            me.trackingWindow.setLoading(false);
        }
        me.panel.renderBlock = false;
        // me.panel.setLoading(false);
        me.callParent(arguments);
    },

    createTrackingTemplate: function () {
        var me = this;
        var image = '{link file="custom/plugins/PostLabelCenter/Resources/views/backend/_resources/images/post_logo_small.jpg"}';
        var imagePath = '{url module=frontend controller=index action=index}';

        return new Ext.XTemplate (
            '{literal}<tpl for=".">' +
            '<div class="tracking--container">' +
            '<div class="backend-logo-img"><img src="' + image + '"/></div>' +
            '<div class="tracking-track-title">Aktueller Sendungsstatus ' + me.trackingCode + '</div>' +
            '<tpl if="noTrackingData == true">' +
            '<div class="tracking-cell-head post--tracking-no-data">' +
            '<span class="tracking-red-notice-text">Es konnten keine Daten für Ihre Abfrage gefunden werden.</span>' +
            '</div>' +
            '</tpl>' +
            '<tpl for="parcels">' +
            '<tpl for="parcelEvents">' +
            '<tpl if="eventIndex == 0">' +
            '<div class="tracking-cell-head"><span class="tracking-red-notice-text">{parcelEventReasonDescription}</span>' +
            '<br><span class="tracking-black-text">Zeitpunkt: </span><span class="tracking-grey-text">{aclFormateDate}</span>' +
            '</div>' +
            '</tpl>' +
            '<div class="tracking-cell">' +
            '<div class="tracking-float-left tracking-cell-icon"><img src="' + imagePath + '{icon}"></div>' +
            '<div class="tracking-float-left tracking-cell-text">' +
            'Datum: <span class="tracking-date-text">{aclFormateDate}</span><br><span>{parcelEventReasonDescription}</span>' +
            '</div>' +
            '</div>' +
            '</tpl>' +
            '</tpl>' +
            '</div>' +
            '</tpl>{/literal}'
        );
    },

    getTrackingData: function() {
        var me = this;
        Ext.Ajax.request({
            url: '{url controller=PostTracking action=getTracking}',
            method: 'POST',
            params: {
                trackingCode: me.trackingCode,
                orderNumber: me.record.get('number')
            },
            success: function(response) {
                me.trackingWindow = me.up('window');
                me.trackingWindow.setLoading(false);
                response = Ext.JSON.decode(response.responseText);
                if (!response.data.noTrackingData) {
                    // dirty fix for indexes
                    Ext.each(response.data.parcels, function(parcel) {
                       Ext.each(parcel.parcelEvents, function(parcelEvent, index) {
                            parcelEvent.eventIndex = index;
                       });
                    });
                   me.panel.tpl.overwrite(me.panel.body, response.data);
                   me.panel.doLayout();
                } else {
                    me.panel.tpl.overwrite(me.panel.body, response.data);
                    me.panel.doLayout();
                }
            }
        });
    }
});
//{/block}
