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

class Index implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    private $request;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * @var AmwalConfig
     */
    protected $config;

    /**
     * @var ExpressCheckoutButton
     */
    protected $expressCheckoutButton;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QuoteRepositoryInterface $quoteRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        AmwalConfig $config,
        ExpressCheckoutButton $expressCheckoutButton
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->quoteRepository = $quoteRepository;
        $this->request = $context->getRequest();
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->config = $config;
        $this->expressCheckoutButton = $expressCheckoutButton;
    }

    public function execute()
    {
        // Retrieve the quote_id from the URL
        $maskQuoteId = $this->request->getParam('quoteId');
        if (!$maskQuoteId) {
            // Redirect to homepage or error page
        }

        $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskQuoteId);
        // Fetch the quote details
        $quote = null;
        if ($quoteId) {
            try {
                $quote = $this->quoteRepository->get($quoteId);
                $quote->setIsActive(true)->save();
            } catch (\Exception $e) {
                // Log error or handle gracefully
            }
        }

        // Pass order data to the block
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('amwal.redirect')->setData([
            'quote' => $quote,
            'style_css' => $this->config->getStyleCss(),
            'button_id' => $this->expressCheckoutButton->getUniqueId(),
            'checkout_button_id' => $this->expressCheckoutButton->getCheckoutButtonId(),
            'override_cart_id' => $maskQuoteId
        ]);

        return $resultPage;
    }
}
