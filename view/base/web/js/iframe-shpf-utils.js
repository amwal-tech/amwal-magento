/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
	'underscore'
], function (_) {
	'use strict';

	return {
		config: {},
		m2BillingAddress: {},

		/**
		 * Set configuration
		 * @param {Object} config
		 */
		setConfig: function (config) {
			this.config = config;
		},

		/**
		 * Set billing address
		 * @param {Object} address
		 */
		setBillingAddress: function (address) {
			this.m2BillingAddress = address;
		},

		/**
		 * Get shpf query string
		 * @returns {string}
		 */
		getShpfQueryString: function () {
			let query = {
				'merchantId' : this.config.accountId,
				'darkMode' : this.config.darkMode,
				'countryCode' : this.config.countryCode,
				'preferredLang' : this.config.preferredLang,
				'amount' : this.config.amount
			}

			return this.encodeQuery(query);
		},

		getUrlFields: function (baseUrl) {
			let query = {
				'APPROVED_URL' : baseUrl,
				'DECLINED_URL' : baseUrl,
				'MISSING_URL' : baseUrl 
			}

			//return this.encodeQuery(query);
		},

		encodeQuery: function (query) {
			let esc = encodeURIComponent;
			let queryString = Object.keys(query)
				.map(k => esc(k) + ':' + esc(query[k]))
				.join(';');

			return queryString;

		},

		getFullShpfUrl: function () {
			let baseUrl = this.config.ifr_shpf_url + this.getShpfQueryString();
			return baseUrl;
		},

		parseApResponse: function (response) {
			let parsedResponse = {};
			parsedResponse.success = false;
			parsedResponse.message = response;
			if (typeof(response) !== 'string') {
				if (response['event']['args']['newPhase'] && response['event']['args']['newPhase'].toUpperCase() === 'COMPLETED') {
					parsedResponse.success = true;
					parsedResponse.token = response['event']['args']['from'];
				} else {
					parsedResponse.message = "Could not process";
				}
			} 
			return parsedResponse;
		}
	};
});
