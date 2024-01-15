<?php
declare(strict_types=1);

namespace Amwal\Payments\Block;

use Amwal\Payments\Model\Config;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class ExpressCheckoutCartButton extends Template
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var CheckoutSessionFactory
     */
    private $checkoutSessionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param Context $context
     * @param Config $config
     * @param CheckoutSessionFactory $checkoutSessionFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        CheckoutSessionFactory $checkoutSessionFactory,
        CartRepositoryInterface $quoteRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @inheritDoc
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * @return bool
     */
    private function shouldRender(): bool
    {
        if (!$this->config->isActive() || !$this->config->isExpressCheckoutActive()) {
            return false;
        }

        if ($this->getQuote() && $this->getQuote()->getGrandTotal() <= 0) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return CartInterface|null
     */
    public function getQuote(): ?CartInterface
    {
        $checkoutSession = $this->checkoutSessionFactory->create();
        $quoteId = (int) $checkoutSession->getQuoteId();
        try {
            $quote = $this->quoteRepository->get($quoteId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $quote;
    }
}
