<?php
declare(strict_types=1);

namespace Amwal\Payments\Controller\Redirect;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Amwal\Payments\Model\Config as AmwalConfig;
use Amwal\Payments\ViewModel\ExpressCheckoutButton;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\QuoteIdMaskFactory;

class Index implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var QuoteRepositoryInterface
     */
    private QuoteRepositoryInterface $quoteRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * @var AmwalConfig
     */
    private AmwalConfig $config;

    /**
     * @var ExpressCheckoutButton
     */
    private ExpressCheckoutButton $expressCheckoutButton;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $resultRedirectFactory;

    /**
     * @var QuoteIdMaskFactory
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param AmwalConfig $config
     * @param ExpressCheckoutButton $expressCheckoutButton
     * @param RedirectFactory $resultRedirectFactory
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QuoteRepositoryInterface $quoteRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        AmwalConfig $config,
        ExpressCheckoutButton $expressCheckoutButton,
        RedirectFactory $resultRedirectFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->quoteRepository = $quoteRepository;
        $this->request = $context->getRequest();
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->config = $config;
        $this->expressCheckoutButton = $expressCheckoutButton;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    public function execute()
    {
        // Retrieve the quote_id from the URL
        $maskQuoteId = $this->request->getParam('quoteId');
        if (!$maskQuoteId) {
            return $this->redirectToErrorPage();
        }

        if (is_numeric($maskQuoteId)) {
            $maskQuoteId = $this->quoteIdMaskFactory->create()->load($maskQuoteId, 'quote_id')->getMaskedId();
        }
        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskQuoteId);

            // Fetch the quote details
            $quote = $this->quoteRepository->get($quoteId);

            // Check if the quote has an order and its status
            $order = $quote->getOrder();
            if ($order && !in_array($order->getStatus(), [Order::STATE_PENDING_PAYMENT, Order::STATE_NEW], true)) {
                return $this->redirectToErrorPage();
            }

            // Reactivate the quote
            $quote->setIsActive(true)->save();

            // Pass order data to the block
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->getBlock('amwal.redirect')->setData([
                'quote' => $quote,
                'style_css' => $this->config->getStyleCss(),
                'button_id' => $this->expressCheckoutButton->getUniqueId(),
                'checkout_button_id' => $this->expressCheckoutButton->getCheckoutButtonId(),
                'override_cart_id' => $maskQuoteId,
                'is_redirect_on_load_click' => $this->config->isRedirectOnLoadClick()
            ]);

            return $resultPage;
        } catch (\Exception $e) {
            return $this->redirectToErrorPage();
        }
    }

    /**
     * Redirect to an error page
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function redirectToErrorPage()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('/');
        return $resultRedirect;
    }
}