<?php
declare(strict_types=1);

namespace Amwal\Payments\Block\Adminhtml\Order\View\Tab;

use Amwal\Payments\Model\AmwalClientFactory;
use Amwal\Payments\Model\Config;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class AmwalTab extends Template implements TabInterface
{
    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var AmwalClientFactory
     */
    protected AmwalClientFactory $amwalClientFactory;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var PriceCurrencyInterface
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @var array|null Cached decoded Amwal API data
     */
    private ?array $decodedAmwalData = null;

    /**
     * Define the template file
     *
     * @var string
     */
    protected $_template = 'Amwal_Payments::order/view/tab/amwal_tab.phtml';

    /**
     * Constructor
     *
     * @param Context                $context
     * @param Registry               $registry
     * @param AmwalClientFactory     $amwalClientFactory
     * @param Config                 $config
     * @param LoggerInterface        $logger
     * @param PriceCurrencyInterface $priceCurrency
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AmwalClientFactory $amwalClientFactory,
        Config $config,
        LoggerInterface $logger,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->amwalClientFactory = $amwalClientFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve tab label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Amwal');
    }

    /**
     * Retrieve tab title.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Amwal Order Summary');
    }

    /**
     * Determine if the tab can be shown.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Determine if the tab is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve the current order from the registry.
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getCurrentOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * Retrieve Amwal order data from the external API.
     *
     * @return string|null JSON string if successful, or null if not available.
     */
    public function getAmwalOrderData(): ?string
    {
        $order = $this->getCurrentOrder();
        if (!$order) {
            $this->logger->warning('No current order found in registry.');
            return null;
        }

        $amwalOrderId = $order->getAmwalOrderId();
        if (!$amwalOrderId) {
            return null;
        }

        try {
            $amwalClient = $this->amwalClientFactory->create();
            $response = $amwalClient->get('transactions/' . $amwalOrderId);

            if ($response->getStatusCode() === 200) {
                return $response->getBody()->getContents();
            } else {
                $this->logger->warning(sprintf(
                    'Amwal API returned non-200 status code for order ID "%s": %s',
                    $amwalOrderId,
                    $response->getStatusCode()
                ));
            }
        } catch (GuzzleException $e) {
            $this->logger->warning(sprintf(
                'Unable to retrieve Amwal order details for order ID "%s". Exception: %s',
                $amwalOrderId,
                $e->getMessage()
            ));
        }

        return null;
    }

    /**
     * Get decoded Amwal data (cached).
     *
     * @return array
     */
    public function getDecodedAmwalData(): array
    {
        if ($this->decodedAmwalData === null) {
            $json = $this->getAmwalOrderData();
            if ($json) {
                $this->decodedAmwalData = json_decode($json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->decodedAmwalData = [];
                }
            } else {
                $this->decodedAmwalData = [];
            }
        }
        return $this->decodedAmwalData;
    }

    /**
     * Retrieve the order data from the current order.
     *
     * @return array|null Order data if available, or null if not available.
     */
    public function getOrderData(): ?array
    {
        $order = $this->getCurrentOrder();
        if (!$order) {
            $this->logger->warning('No current order found in registry.');
            return null;
        }
        return $order->getData();
    }

    /**
     * Retrieve the Amwal order URL.
     *
     * @return string|null URL if available, or null if not available.
     */
    public function getAmwalOrderUrl(): ?string
    {
        $order = $this->getCurrentOrder();
        if (!$order) {
            $this->logger->warning('No current order found in registry.');
            return null;
        }

        $amwalOrderId = $order->getAmwalOrderId();
        if (!$amwalOrderId) {
            return null;
        }

        $amwalClient = $this->amwalClientFactory->create();
        $baseUrl = $amwalClient->getConfig('base_uri');

        return $baseUrl . 'transactions/' . $amwalOrderId;
    }

    /**
     * Format a currency amount for display.
     *
     * @param mixed $amount
     * @return string
     */
    public function formatCurrency($amount): string
    {
        $value = (float) $amount;
        $order = $this->getCurrentOrder();
        $currencyCode = $order ? $order->getOrderCurrencyCode() : 'SAR';

        $formatted = $this->priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $currencyCode
        );

        // Strip HTML tags so the result is plain text (e.g. "SAR 61.82" instead of "<span>SAR 61.82</span>")
        return strip_tags((string) $formatted);
    }

    /**
     * Get the CSS class for a status badge.
     *
     * @param string|null $status
     * @return string
     */
    public function getStatusBadgeClass(?string $status): string
    {
        if (!$status) {
            return 'amwal-badge--default';
        }

        $statusLower = strtolower($status);
        $map = [
            'success'   => 'amwal-badge--success',
            'completed' => 'amwal-badge--success',
            'failed'    => 'amwal-badge--danger',
            'expired'   => 'amwal-badge--danger',
            'cancelled' => 'amwal-badge--danger',
            'pending'   => 'amwal-badge--warning',
            'processing' => 'amwal-badge--info',
        ];

        return $map[$statusLower] ?? 'amwal-badge--default';
    }

    /**
     * Get the type badge class (SANDBOX/LIVE).
     *
     * @param string|null $type
     * @return string
     */
    public function getTypeBadgeClass(?string $type): string
    {
        if (!$type) {
            return 'amwal-type-pill--default';
        }
        return strtoupper($type) === 'SANDBOX'
            ? 'amwal-type-pill--sandbox'
            : 'amwal-type-pill--live';
    }

    /**
     * Check if installment data is present.
     *
     * @return bool
     */
    public function hasInstallment(): bool
    {
        $data = $this->getDecodedAmwalData();
        return isset($data['installment_duration']) && $data['installment_duration'] !== null;
    }

    /**
     * Check if there is refund-related data worth showing.
     *
     * @return bool
     */
    public function hasRefundInfo(): bool
    {
        $data = $this->getDecodedAmwalData();
        $refundedAmount = (float)($data['refunded_amount'] ?? 0);
        return $refundedAmount > 0 || !empty($data['is_refundable']) || !empty($data['refund_tracker']);
    }

    /**
     * Get order items from order_details.order_content.
     *
     * @return array
     */
    public function getOrderItems(): array
    {
        $data = $this->getDecodedAmwalData();
        return $data['order_details']['order_content'] ?? [];
    }

    /**
     * Get order errors from order_details.error.
     *
     * @return array
     */
    public function getOrderErrors(): array
    {
        $data = $this->getDecodedAmwalData();
        if (!empty($data['order_details']['error']) && is_array($data['order_details']['error'])) {
            return $data['order_details']['error'];
        }
        return [];
    }

    /**
     * Format a masked card number display.
     *
     * @return string
     */
    public function getMaskedCardDisplay(): string
    {
        $data = $this->getDecodedAmwalData();
        $number = $data['number'] ?? '';
        $brand = $data['paymentBrand'] ?? $data['card_payment_brand'] ?? '';

        if (empty($number) && empty($data['card_last_4_digits'])) {
            return '';
        }

        // Use the masked number from API if available (e.g., "545454xxxxxx5454")
        if (!empty($number)) {
            // Format: #### •••• •••• ####
            $display = preg_replace('/x+/', ' •••• •••• ', $number);
        } elseif (!empty($data['card_last_4_digits'])) {
            $display = '•••• •••• •••• ' . $data['card_last_4_digits'];
        } else {
            $display = '';
        }

        $parts = [];
        if ($display) {
            $parts[] = $display;
        }
        if ($brand) {
            $parts[] = strtoupper($brand);
        }

        return implode(' — ', $parts);
    }

    /**
     * Format a date string for display.
     *
     * @param string|null $dateString
     * @param int         $format  (unused, kept for parent compatibility)
     * @param bool        $showTime (unused, kept for parent compatibility)
     * @param string|null $timezone (unused, kept for parent compatibility)
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function formatDate($dateString = null, $format = \IntlDateFormatter::MEDIUM, $showTime = true, $timezone = null): string
    {
        if (!$dateString) {
            return (string) __('N/A');
        }
        try {
            $date = new \DateTime($dateString);
            return $date->format('M d, Y \a\t h:i A');
        } catch (\Exception $e) {
            return (string) $dateString;
        }
    }

    /**
     * Determine if the "Update Order Status" button should be shown.
     *
     * @return bool
     */
    public function shouldShowUpdateButton(): bool
    {
        $data = $this->getDecodedAmwalData();
        $amwalStatus = $data['status'] ?? '';
        $currentOrder = $this->getCurrentOrder();
        $storeStatus = $currentOrder ? $currentOrder->getStatus() : '';

        return $amwalStatus === 'success' && $storeStatus !== 'processing';
    }

    /**
     * Get the formatted address block from address_details.
     *
     * @return string
     */
    public function getFormattedAddress(): string
    {
        $data = $this->getDecodedAmwalData();
        $addr = $data['address_details'] ?? [];

        if (empty($addr)) {
            return (string) __('N/A');
        }

        $lines = [];
        $name = trim(($addr['first_name'] ?? '') . ' ' . ($addr['last_name'] ?? ''));
        if ($name) {
            $lines[] = $name;
        }
        if (!empty($addr['street1'])) {
            $lines[] = $addr['street1'];
        }
        $cityLine = trim(($addr['city'] ?? '') . ', ' . ($addr['state'] ?? '') . ' ' . ($addr['postcode'] ?? ''));
        if ($cityLine && $cityLine !== ', ') {
            $lines[] = $cityLine;
        }
        if (!empty($addr['country'])) {
            $lines[] = $addr['country'];
        }

        return implode("\n", $lines);
    }
}
