/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract',
    'Magento_Paypal/js/in-context/express-checkout-wrapper',
    'Magento_Paypal/js/action/set-payment-method',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/model/messageList',
    'Magento_Ui/js/lib/view/utils/async'
], function ($, Component, Wrapper, setPaymentMethod, additionalValidators, messageList) {
    'use strict';

    return Component.extend(Wrapper).extend({
        defaults: {
            template: 'Mageplaza_Osc/payment/paypal-express-in-context',
            validationElements: 'input'
        },

        /**
         * Listens element on change and validate it.
         *
         * @param {HTMLElement} context
         */
        initListeners: function (context) {
            $.async(this.validationElements, context, function (element) {
                $(element).on('change', function () {
                    this.validate();
                }.bind(this));
            }.bind(this));
        },

        /**
         *  Validates Smart Buttons
         */
        validate: function () {
            this._super();

            if (this.actions) {
                additionalValidators.validate(true) ? this.actions.enable() : this.actions.disable();
            }
        },

        /** @inheritdoc */
        beforePayment: function (resolve, reject) {
            var promise = $.Deferred();

            setPaymentMethod(this.messageContainer).done(function () {
                return promise.resolve();
            }).fail(function (response) {
                var error;

                try {
                    error = JSON.parse(response.responseText);
                } catch (exception) {
                    error = this.paymentActionError;
                }

                this.addError(error);

                return reject(new Error(error));
            }.bind(this));

            return promise;
        },

        /**
         * Populate client config with all required data
         *
         * @return {Object}
         */
        prepareClientConfig: function () {
            this._super();
            this.clientConfig.quoteId = window.checkoutConfig.quoteData['entity_id'];
            this.clientConfig.customerId = window.customerData.id;
            this.clientConfig.button = 0;

            return this.clientConfig;
        },

        /**
         * Adding logic to be triggered onClick action for smart buttons component
         */
        onClick: function () {
            additionalValidators.validate();
            this.selectPaymentMethod();
        },

        /**
         * Adds error message
         *
         * @param {String} message
         */
        addError: function (message) {
            if ($.type(message) === "object") {
                messageList.addErrorMessage(message);
            } else {
                messageList.addErrorMessage({
                    'message': message
                });
            }
        },

        /**
         * After payment execute
         *
         * @param {Object} res
         * @param {Function} resolve
         * @param {Function} reject
         *
         * @return {*}
         */
        afterPayment: function (res, resolve, reject) {
            if (res.success) {
                return resolve(res.token);
            }

            this.addError(res['error_message']);

            return reject(new Error(res['error_message']));
        },

        /**
         * After onAuthorize execute
         *
         * @param {Object} res
         * @param {Function} resolve
         * @param {Function} reject
         * @param {Object} actions
         *
         * @return {*}
         */
        afterOnAuthorize: function (res, resolve, reject, actions) {
            if (res.success) {
                resolve();

                return actions.redirect(res.redirectUrl);
            }

            this.addError(res['error_message']);

            return reject(new Error(res['error_message']));
        }
    });
});
