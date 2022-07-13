/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
	[
		'underscore',
		'jquery',
		'Magento_Payment/js/view/payment/cc-form',
		'Magento_Checkout/js/model/quote',
		'Magento_Checkout/js/checkout-data',
		'Amwal_Payments/js/validator',
		'Amwal_Payments/js/iframe-shpf-utils',
		'Magento_Ui/js/model/messageList',
		'Amwal_Payments/js/view/payment/validator-handler',
		'Magento_Checkout/js/model/full-screen-loader',
		'mage/translate',
		'prototype',
		'domReady!'
	],
	function (
		_,
		$,
		Component,
		quote,
		checkoutData,
		validator,
		shpfUtils,
		globalMessageList,
		validatorManager,
		fullScreenLoader,
		$t
	) {
		'use strict';

		return Component.extend({
			defaults: {
				template: 'Amwal_Payments/payment/iframe/form',
				active: false,
				code: 'amwal_iframe',
				amwal_code: 'amwal_payments',
				paymentPayload: {
					token: null,
					type: null
				},
				additionalData: {},
				paymentTypeSelectorId: 'amwal_iframe-payment-type-selector',
				iframeContainerId: 'amwal_iframe-ifr-iframe-container',
				paymentMethodName: '[name="payment[method]"',
				ifrIframeId: 'amwal_iframe-ifr-iframe',
				iframeOrigin: 'https://qa-checkout.sa.amwal.tech'
			},
			
			/**
			 * @returns {exports.initialize}
			 */
			initialize: function () {
				var self = this;

				self._super();

				return self;
			},

			/**
			 * Set list of observable attributes
			 *
			 * @returns {exports.initObservable}
			 */
			initObservable: function () {

				if (this.canIfr()) {
					shpfUtils.setConfig(window.checkoutConfig.payment[this.getCode()]);
					shpfUtils.setBillingAddress(this.getBillingAddress());
					quote.billingAddress.subscribe(
						this.refreshBillingAddress.bind(this)
					);
				}


				validator.setConfig(window.checkoutConfig.payment[this.getCode()]);

				this._super()
					.observe(['active']);
				return this;
			},

			refreshBillingAddress: function () {
				if (this.isIfrActive() && quote.billingAddress()) {
					shpfUtils.setBillingAddress(quote.billingAddress());
					this.initIfrIframe();
				}
			},


			/**
			 * Get payment name
			 *
			 * @returns {String}
			 */
			getCode: function () {
				return this.code;
			},


			/**
			 * Get Iframe Gateway Environment
			 *
			 * @returns {String}
			 */
			getEnvironment: function () {
				return window.checkoutConfig.payment[this.getCode()].environment;
			},

			/**
			 * Get billing address
			 *
			 * @returns {String}
			 */
			getBillingAddress: function () {
				let billingAddress = checkoutData.getBillingAddressFromData();
				if (!billingAddress) {
					billingAddress = quote.billingAddress();
				}
				return billingAddress;
			},

			/**
			 * Check if payment is active
			 *
			 * @returns {Boolean}
			 */
			isActive: function () {
				let active = this.getCode() === this.isChecked();

				this.active(active);

				return active;
			},

			changePaymentType: function (val) {
				this.handlePaymentType();
			},

			getPaymentTypeConfig: function () {
				return window.checkoutConfig.payment[this.getCode()]['paymentType'];
			},

			multiplePaymentTypes: function () {
				return "";
			},

			/* 
			* Create/Destroy appropriate forms for
			* selected payment type
			*/ 
			handlePaymentType: function () {
				
					this.buildIfrIframe();
					this.initIfrIframe();
			},

			/* 
			* Retreive payment type selector
			*/ 
			getPaymentTypeSelector: function () {
				return $('#' + this.paymentTypeSelectorId);
			},

			/* 
			* Retreive value of payment type selector
			*/ 
			getSelectedPaymentType: function () {
				return this.getPaymentTypeSelector().val();
			},

			/* 
			* Can IFR be used in checkout?
			*/
			canIfr: function () {
				return this.getPaymentTypeConfig().include('IFR');
			},

			/* 
			* Is IFR only available payment type?
			*/
			onlyIfr: function () {
				return 'IFR';
			},

			/* 
			* Is IFR the active payment type?
			*/
			isIfrActive: function () {
				return (
					
						this.onlyIfr() 
					
				);
			},

			getAccountId: function () {
				return window.checkoutConfig.payment[this.getCode()].accountId;
			},

			getCountryCode: function () {
				return window.checkoutConfig.payment[this.getCode()].countryCode;
			},

			getPreferredLang: function () {
				return window.checkoutConfig.payment[this.getCode()].preferredLang;
			},

			getDarkMode: function () {
				return window.checkoutConfig.payment[this.getCode()].darkMode;
			},

			getAmount: function () {
				return quote.totals()['grand_total'];
			},


			/*
			* Set iframe source if IFR is active
			*/
			initIfrIframe: function () {
				if (this.isIfrActive()) {
					//this.getIfrIframe().attr('src', shpfUtils.getFullShpfUrl());
					
				}
			},

			/*
			* Display iframe
			*/
			buildIfrIframe: function () {
				this.getIframeContainer().show();

				
				var self = this;
				self.adde = window.addEventListener("message", async ev => {
					if (ev.data == "paymentSuccessful") {
					  var token = (Math.random()*100000);
					  self.setPaymentTokenInfo(token);
					  self.placeOrder('parent');
			  
					}
				  });


			},

			/*
			* Hide iframe and clear source
			*/
			deactivateIfrIframe: function () {
				this.getIfrIframe().attr('src', "about:blank");
				this.getIframeContainer().hide();
			},

			getIfrIframe: function () {
				return $('#' + this.ifrIframeId);
			},

			/*
			* Retreive iframe container
			*/
			getIframeContainer: function () {
				return $('#' + this.iframeContainerId);
			},

			/**
			 * Get data
			 *
			 * @returns {Object}
			 */
			getData: function () {
				var data = {
					'method': this.getCode(),
					'additional_data': {
						'payment_token': this.paymentPayload.token,
						'payment_type': this.paymentPayload.type
					}
				};

				data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

				return data;
			},

			/**
			 * Action to place order
			 */
			placeOrder: function (key) {
				var self = this;

				if (key) {
					return self._super();
				}
				// place order on success validation
				validatorManager.validate(self, function () {
					return self.placeOrder('parent');
				}, function (err) {

					if (err) {
						self.showError(err);
					}
				});

				return false;
			},

			placeOrderClick: function () {
				if (this.paymentPayload.token) {
					this.placeOrder('parent');
				} else if (this.isIfrActive()) {
					this.beginTokenFlow();
				}
			},

			submitIfrIframe: function() {
				let submitMsg = this.getIfrSubmitMsg();
				// find iframe
				let iframeCw = document.getElementById(this.ifrIframeId).contentWindow;
				// send submit message
				iframeCw.postMessage(submitMsg, '*');
			},

			setPaymentTokenInfo: function (token) {
				this.setPaymentPayload(token);
			},

			getIfrSubmitMsg: function () {
				return { 
					'originCode' : 'amwal_iframe_magento',
					'action' : 'submit'
				};
			},

			beginTokenFlow: function () {
				fullScreenLoader.startLoader();
			},

			/**
			 * Sets payment token and type information
			 *
			 * @param {Object} paymentToken
			 * @private
			 */	
			setPaymentPayload: function (paymentToken) {
				this.paymentPayload.token = paymentToken;
				this.paymentPayload.type = this.isIfrActive() ? 'IFR' :'CREDIT';
			},

			/**
			 * Show error message
			 *
			 * @param {String} errorMessage
			 * @private
			 */
			showError: function (errorMessage) {
				globalMessageList.addErrorMessage({
					message: errorMessage
				});
			}
		});
	}
);
