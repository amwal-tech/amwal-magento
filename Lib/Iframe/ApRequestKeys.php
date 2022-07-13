<?php
namespace Amwal\Payments\Lib\Iframe;

class ApRequestKeys
{
	// Customer Fields
	const ADDRESS_1 = 'ADDR1';
	const ADDRESS_2 = 'ADDR2';
	const LOCALITY = 'CITY';
	const REGION = 'STATE';
	const POSTAL_CODE = 'ZIPCODE';
	const COUNTRY = 'COUNTRY';
	const FIRST_NAME = 'NAME1';
	const LAST_NAME = 'NAME2';
	const COMPANY = 'COMPANY_NAME';
	const PHONE = 'PHONE';
	const EMAIL = 'EMAIL';
	const IP_ADDRESS = 'CUSTOMER_IP';
	const ORDER_ID = 'ORDER_ID';

	// Txn Fields
	const AMOUNT = 'AMOUNT';
	const PAYMENT_TOKEN = 'RRNO';
	const TRANSACTION_TYPE = 'TRANSACTION_TYPE';

	// BP Request Fields
	const MERCHANT_ID = "merchant-id";
	const MODE = 'MODE';
	const TPS_DEFINITION = 'TPS_DEF';
	const TPS_HASH_TYPE = 'TPS_HASH_TYPE';
	const TPS = 'TAMPER_PROOF_SEAL';
	const DOCUMENT_TYPE = 'DOC_TYPE';
	const STORE_ACCOUNT = 'MERCHDATA_store_payment_account';
	const RESPONSE_VERSION = 'RESPONSEVERSION';
	
	// Level 2 Fields
	const INVOICE_ID = 'INVOICE_ID';
	const AMOUNT_TAX = 'AMOUNT_TAX';

	// Level 3 Fields
	const TAX_RATE = 'LV2_ITEM_TAX_RATE';
	const SHIPPING_AMOUNT = 'LV2_ITEM_SHIPPING_AMOUNT';
	const DISCOUNT_AMOUNT = 'LV2_ITEM_DISCOUNT_AMOUNT';
	const TAX_ID = 'LV2_ITEM_TAX_ID';
	const BUYER_NAME = 'LV2_ITEM_BUYER_NAME';
	const SHIP_NAME = 'LV2_ITEM_SHIP_NAME';
	const SHIP_STREET = 'LV2_ITEM_SHIP_ADDR1';
	const SHIP_LOCALITY = 'LV2_ITEM_SHIP_CITY';
	const SHIP_REGION = 'LV2_ITEM_SHIP_STATE';
	const SHIP_POSTAL_CODE = 'LV2_ITEM_SHIP_ZIP';
	const SHIP_COUNTRY = 'LV2_ITEM_SHIP_COUNTRY';
	const L3_ITEM_PREFIX = 'LV3_ITEM';
	const L3_STUB_PRODUCT_CODE = "_PRODUCT_CODE";
	const L3_STUB_UNIT_COST = "_UNIT_COST";
	const L3_STUB_QUANTITY = "_QUANTITY";
	const L3_STUB_DESCRIPTOR = "_ITEM_DESCRIPTOR";
	const L3_STUB_MEASURE_UNITS = "_MEASURE_UNITS";
	const L3_STUB_COMMODITY_CODE = "_COMMODITY_CODE";
	const L3_STUB_TAX_AMOUNT = "_TAX_AMOUNT";
	const L3_STUB_TAX_RATE = "_TAX_RATE";
	const L3_STUB_DISCOUNT = "_ITEM_DISCOUNT";
	const L3_STUB_TOTAL = "_LINE_ITEM_TOTAL";
	
	/* not using PAYMENT_TYPE b/c all payments s/b
	* made using a payment token
	* const PAYMENT_TYPE_KEY = 'PAYMENT_TYPE';
	*/

}