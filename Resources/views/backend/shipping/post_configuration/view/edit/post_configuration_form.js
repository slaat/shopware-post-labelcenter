//{namespace name=backend/shipping/post_configuration/view/edit/post_configuration}
//{block name="backend/shipping/post_configuration/view/edit/post_configuration_form"}

Ext.define('Shopware.apps.Shipping.PostConfiguration.view.edit.PostConfigurationForm', {
    extend: 'Ext.form.Panel',
    alias: 'widget.shipping-post_configuration-view-edit-post_configuration_form',
    name: 'shipping-post_configuration-view-edit-post_configuration_form',
    layout: 'column',
    cls: 'shopware-form',

    snippets: {
        notSaved: {
            title: "Nicht gespeicherte Änderungen vorhanden",
            msg: "Sind Sie sicher, dass Sie fortfahren möchten? Alle nicht gespeicherte Änderungen gehen verloren"
        },
        saving: {
            title: "Speichern erfolgreich",
            msg: "Änderungen erfolgreich gespeichert"
        }
    },

    initComponent: function () {
        var me = this;
        me.configStore = Ext.create('Shopware.apps.Shipping.PostConfiguration.store.PostConfiguration');
        var countriesIso = [];

        Ext.each(me.countries, function (country) {
            countriesIso.push(country.iso)
        });

        me.contractNumberField = me.createContractNumberField();
        me.configStore.getProxy().extraParams.countries = Ext.JSON.encode(countriesIso);
        me.configStore.getProxy().extraParams.dispatchID = me.dispatchID;

        me.configStore.load({
            callback: function(records, operation, success) {
                // find saved configuration
                // START SAVE CHECK
                var contractNumber = false;

                Ext.each(records, function (record) {
                    if (record.data.savedcontractnumber) {
                        contractNumber = record.data.savedcontractnumber;
                        me.activeRecord = me.configStore.findRecord('contractnumber', contractNumber);
                        me.contractNumberField.setValue(contractNumber);
                        return false;
                    }
                });

                if (this.first() && !contractNumber) {
                    // todo - change it
                    contractNumber = this.first().data.contractnumber;

                    // todo - make it simple later
                    Ext.each(this.first().raw.products, function (product) {
                        me.contractNumberField.setValue(contractNumber);
                        return false;
                    });
                }
            }
        });

        me.contractNumberFieldSet = Ext.create('Ext.form.FieldSet', {
           columnWidth: 1,
           items: me.contractNumberField
        });

        me.productForm = me.createProductForm();

        me.productFormFieldSet = Ext.create('Ext.form.FieldSet', {
            title: 'Produkt',
            columnWidth: 0.5,
            margin: '0 20 0 0',
            items: me.productForm
        });

        me.featureForm = me.createFeatureForm();

        me.featureFormFieldSet = Ext.create('Ext.form.FieldSet', {
            title: 'Features',
            columnWidth: 0.5,
            items: me.featureForm
        });
        me.items = [me.contractNumberFieldSet, me.productFormFieldSet, me.featureFormFieldSet];
        me.callParent(arguments);
    },
    createContractNumberField: function () {
        var me = this;
        // Create the combo box, attached to the states data store
        return Ext.create('Ext.form.ComboBox', {
            fieldLabel: 'Kennung',
            name: 'PostContractNumberSelection',
            store: me.configStore,
            // displayField: 'identifier',
            tpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                '<div class="x-boundlist-item">{literal}{identifier}{/literal} ({literal}{contractnumber}{/literal})</div>',
                '</tpl>'
            ),
            displayTpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                '{literal}{identifier}{/literal} ({literal}{contractnumber}{/literal})',
                '</tpl>'
            ),
            valueField: 'contractnumber',
            layout: 'anchor',
            anchor: '42%',
            mode: 'local',
            selectOnFocus: true,
            allowBlank: false,
            typeAhead: false,
            triggerAction: 'all',
            forceSelection: true,
            emptyText: '--Auswahl Vertragsnummer--',
            listeners: {
              scope: this,

              change : function (el, newValue, oldValue) {
                me.updateForm(newValue, oldValue);
              }

            },
          renderTo: Ext.getBody()
        });
    },

    createProductForm: function () {
        var me = this;
        return Ext.create('Ext.form.RadioGroup', {
            // Arrange radio buttons into two columns, distributed vertically
            layout: 'anchor',
            anchor: '100%',
            vertical: true,
            simpleValue: true  // set simpleValue to true to enable value binding
        });
    },

    createFeatureForm: function () {
        var me = this;
        // field as combination of checked and unchecked checkboxes
        return Ext.create('Ext.form.CheckboxGroup', {
            // Arrange checkboxes into two columns, distributed vertically
            layout: 'anchor',
            anchor: '100%',
            vertical: true,
            listeners: {
                change: function (field, newValue) {
                    me.handleFeatureChange(field, newValue);
                }
            }
        })
    },

  updateForm: function (newValue, oldValue) {
      var me = this;
      me.savedState = false;
      // get product from currently selected contract number
      me.configStore.getProxy().extraParams.contractNumber = newValue;

       me.configStore.load({
            callback: function(records, operation, success) {
                // find saved configuration
                // START SAVE CHECK
                var contractNumber = false;

                Ext.each(records, function (record) {
                    if (record.data.contractnumber == newValue) {
                        contractNumber = record.data.contractnumber;
                        me.activeRecord = me.configStore.findRecord('contractnumber', contractNumber);
                        me.contractNumberField.setValue(contractNumber);
                        return false;
                    }
                });

                // if (this.first() && !contractNumber) {
                //     // todo - change it
                //     contractNumber = this.first().data.contractnumber;
                //
                //     // todo - make it simple later
                //     Ext.each(this.first().raw.products, function (product) {
                //         me.contractNumberField.setValue(contractNumber);
                //         return false;
                //     });
                // }

                // get active record on every change of contract number
                if (oldValue && !me.savedState) {
                    me.createConfirmMessage(me.snippets.notSaved);
                }
                me.activeRecord = me.configStore.findRecord('contractnumber', newValue);
                me.contractNumberValue = newValue;

                if (!me.activeRecord) {
                    return false;
                }
                me.products = me.activeRecord.raw.products;
                me.productItems = [];
                me.productForm.removeAll();
                me.featureForm.removeAll();

                Ext.each(me.products, function(product) {
                    me.productItems.push(product);
                    me.productForm.add(
                        {
                            boxLabel: product.name,
                            name: 'rb',
                            inputValue: product.productID,
                            helpText: product.helpText,
                            product: product,
                            checked: product.checked,
                            listeners: {
                                'change' : {
                                    fn: function(field, newValue, oldValue) {
                                        // because of Extjs RadioGroup change BUG!!!!
                                        if (newValue == true) {
                                            me.handleProductChange(field.inputValue, newValue);
                                        }
                                    }
                                }
                            }
                        });

                    if (product.checked) {
                        var features = product.features;
                        me.product = product;
                        //todo - mybe delete
                        me.activeRecord.activeProduct = product;
                        me.fillFeaturesForm(features, product);
                    }
                });
            }
        });


  },

    handleProductChange: function(productId, newValue) {
      var me = this;
      Ext.each( me.products, function(product) {

        if (product.productID == productId) {
            me.product = product;
            // break loop
           return false;
        }
      });

      me.activeRecord.activeProduct = null;
      me.activeRecord.activeProduct = me.product;
      me.activeRecord.activeFeatures = [];
      var features =  me.product.features;
      me.featureForm.removeAll();
      me.fillFeaturesForm(features);
    },

    handleFeatureChange: function(field, newValue) {
        var me = this;
        me.updateProductFeatures( me.activeRecord, field.getChecked());
    },

    updateProductFeatures: function(parent, checkedFeatures) {
        Ext.each(parent.activeProduct.features, function(feature) {
            feature.checked = false;
            Ext.each(checkedFeatures, function(checkedFeature) {
                if(checkedFeature.boxLabel == feature.name && checkedFeature.thirdPartyID == feature.thirdPartyID) {
                   feature.checked = true;
                }
            });
        });
    },

    createConfirmMessage: function(data) {
        var me = this;
        Ext.Msg.confirm(data.title, data.msg, function(btn) {
            //me.sendMessageToFrame(btn, null, data.id, data.instance, data._component);
        });
    },

    fillFeaturesForm: function (features, product) {
        var me = this;

        Ext.each(features, function(feature) {
            me.featureForm.add({
                boxLabel: feature.name, name: 'cb', inputValue: feature.id, checked: feature.checked, thirdPartyID: feature.thirdPartyID
            });
        });
    }
});
//{/block}

