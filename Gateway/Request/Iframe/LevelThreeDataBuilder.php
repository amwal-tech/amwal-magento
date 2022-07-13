<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amwal\Payments\Gateway\Request\Iframe;

use Amwal\Payments\Lib\Iframe\ApRequestKeys;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Amwal\Payments\Gateway\Subject\Iframe\SubjectReader;
use Magento\Payment\Helper\Formatter;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
/**
 * Class LevelThreeDataBuilder
 */
class LevelThreeDataBuilder implements BuilderInterface
{
	use Formatter;

	/**
	 * @var SubjectReader
	 */
	private $subjectReader;

	/**
	 * Constructor
	 *
	 * @param SubjectReader $subjectReader
	 */
	public function __construct(SubjectReader $subjectReader)
	{
		$this->subjectReader = $subjectReader;
	}

	/**
	 * @inheritdoc
	 */
	public function build(array $buildSubject)
	{
		$paymentDO = $this->subjectReader->readPayment($buildSubject);
		$orderDO = $paymentDO->getOrder();
		$shippingAddress = $orderDO->getShippingAddress();
		$order = $paymentDO->getPayment()->getOrder();

		$result = [
			ApRequestKeys::SHIPPING_AMOUNT => $order->getShippingAmount(),
			ApRequestKeys::DISCOUNT_AMOUNT => $order->getDiscountAmount(),
			ApRequestKeys::TAX_ID => $order->getCustomerTaxvat(),
			ApRequestKeys::BUYER_NAME => $this->buildFullName($orderDO->getBillingAddress()),
			ApRequestKeys::SHIP_NAME => $this->buildFullName($shippingAddress),
			ApRequestKeys::SHIP_STREET => $shippingAddress->getStreetLine1(),
			ApRequestKeys::SHIP_LOCALITY => $shippingAddress->getCity(),
			ApRequestKeys::SHIP_REGION => $shippingAddress->getRegionCode(),
			ApRequestKeys::SHIP_POSTAL_CODE => $shippingAddress->getPostcode(),
			ApRequestKeys::SHIP_COUNTRY => $shippingAddress->getCountryId()
		];

		$orderItems = $orderDO->getItems();
		if (count($orderItems) < 1) {
			throw new \InvalidArgumentException('No items found in order.');
		}

		if (count($orderItems) > 0) {
			$taxPercent = reset($orderItems)->getTaxPercent() . "%";
			$result[ApRequestKeys::TAX_RATE] = $taxPercent;

			$lv3Info = $this->createLevelThreeInfo($orderItems);
			return array_replace_recursive($result, $lv3Info);
		}

		return $result;
	}

	private function buildFullName(AddressAdapterInterface $address)
	{
		$name1 = $address->getFirstname();
		$name2 = $address->getLastname();
		$fullName = ($name1 != '' && $name2 != '') ? $name1 . ' ' . $name2 : $name1 . $name2;

		return $fullName;
	}

	private function createLevelThreeInfo(array $orderItems) {
		$result = array();
		$i = 1;
		foreach ($orderItems as $item) {
			$product_code = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_PRODUCT_CODE;
			$result[$product_code] = $item->getSku();

			$unit_cost = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_UNIT_COST;
			$result[$unit_cost] = $this->formatPrice($item->getPrice());
			
			$quantity = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_QUANTITY;
			$result[$quantity] = $item->getQtyOrdered();

			$descriptor = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_DESCRIPTOR;
			$result[$descriptor] = $item->getName();

			$measure_units = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_MEASURE_UNITS;
			$result[$measure_units] = 'EA';

			$commodity_code = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_COMMODITY_CODE;
			$result[$commodity_code] = '-';

			$tax = round($item->getPrice() * ($item->getTaxPercent() / 100), 2);
			$tax_amount = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_TAX_AMOUNT;
			$result[$tax_amount] = $this->formatPrice($tax);

			$tax_rate = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_TAX_RATE;
			$result[$tax_rate] = $item->getTaxPercent() . '%';

			$item_discount = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_DISCOUNT;
			$result[$item_discount] = $this->formatPrice($item->getDiscountAmount());

			$line_item_total = ApRequestKeys::L3_ITEM_PREFIX . strval($i) . ApRequestKeys::L3_STUB_TOTAL;
			$itemTotal = $item->getPrice() * $item->getQtyOrdered() + $tax;
			$result[$line_item_total] = $this->formatPrice($itemTotal);
			$i++;
		}

		return $result;
	}}
