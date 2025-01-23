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
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\Locale\Resolver;

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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $resultRedirectFactory;

    /**
     * @var Resolver
     */
    private Resolver $resolver;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param AmwalConfig $config
     * @param ExpressCheckoutButton $expressCheckoutButton
     * @param LoggerInterface $logger
     * @param RedirectFactory $resultRedirectFactory
     * @param Resolver $resolver
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QuoteRepositoryInterface $quoteRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        AmwalConfig $config,
        ExpressCheckoutButton $expressCheckoutButton,
        LoggerInterface $logger,
        RedirectFactory $resultRedirectFactory,
        Resolver $resolver
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->quoteRepository = $quoteRepository;
        $this->request = $context->getRequest();
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->config = $config;
        $this->expressCheckoutButton = $expressCheckoutButton;
        $this->logger = $logger;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resolver = $resolver;

    }

    public function execute()
    {
        // Retrieve the quote_id from the URL
        $maskQuoteId = $this->request->getParam('quoteId');
        if (!$maskQuoteId) {
            // Log and redirect to homepage or error page
            $this->logger->error('Missing quoteId parameter in the request.');
            return $this->redirectToErrorPage();
        }

        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskQuoteId);

            // Fetch the quote details
            $quote = $this->quoteRepository->get($quoteId);

            // Check if the quote has an order and its status
            $order = $quote->getOrder();
            if ($order && !in_array($order->getStatus(), [Order::STATE_PENDING_PAYMENT, Order::STATE_NEW], true)) {
                // Log the invalid status
                $this->logger->warning(sprintf('Invalid order status: %s for quoteId: %s', $order->getStatus(), $quoteId));
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
                'is_redirect_on_load_click' => $this->config->isRedirectOnLoadClick(),
                'locale' => $this->getLocaleCode(),
            ]);

            return $resultPage;
        } catch (\Exception $e) {
            // Log the exception and redirect to error page
            $this->logger->error(sprintf('Error processing quoteId: %s, error: %s', $maskQuoteId, $e->getMessage()));
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


    /**
     * Get the locale code for the current store
     * @return string
     */
    private function getLocaleCode(): string
    {
        if ($locale = $this->resolver->getLocale()) {
            $locale = explode('_', $locale)[0];
        }
        return $locale ?? 'en';
    }
}
