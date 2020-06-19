define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function ($, Component, customerData) {
    'use strict';

    var quoteItemData = window.checkoutConfig.quoteItemData;

    return Component.extend({
        params: '',
        defaults: {
            template: 'Netbaseteam_Onlinedesign/summary/item/details'
        },
        quoteItemData: quoteItemData,

        getValue: function (quoteItem) {
            return quoteItem.name;
        },
        showOnlineDesignFile: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            return item.onlineDesign;
        },
        showButtonEditDesign: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            return item.buttonEditDesign;
        },
        showUploadDesignFile: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            return item.uploadDesign;
        },
        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getItem: function(item_id) {
            var itemElement = null;
            _.each(this.quoteItemData, function(element, index) {
                if (element.item_id == item_id) {
                    itemElement = element;
                }
            });
            return itemElement;
        }
    });
});