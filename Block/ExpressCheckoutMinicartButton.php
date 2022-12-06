<?php
declare(strict_types=1);

namespace Amwal\Payments\Block;

use Amwal\Payments\Model\Config;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\ScopeInterface;

class ExpressCheckoutMinicartButton extends Template
{

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var CustomerSessionFactory
     */
    private CustomerSessionFactory $customerSessionFactory;

    /**
     * @var CheckoutSessionFactory
     */
    private CheckoutSessionFactory $checkoutSessionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @param Context $context
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param CheckoutSessionFactory $checkoutSessionFactory
     * @param CustomerSessionFactory $customerSessionFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        ScopeConfigInterface $scopeConfig,
        CheckoutSessionFactory $checkoutSessionFactory,
        CustomerSessionFactory $customerSessionFactory,
        CartRepositoryInterface $quoteRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->customerSessionFactory = $customerSessionFactory;
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

        if (!$this->customerSessionFactory->create()->isLoggedIn() && !$this->scopeConfig->isSetFlag(Data::XML_PATH_GUEST_CHECKOUT, ScopeInterface::SCOPE_STORE)) {
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
