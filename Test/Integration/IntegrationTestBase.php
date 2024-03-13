<?php

declare(strict_types=1);

namespace Amwal\Payments\Test\Integration;

use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Api\Data\RefIdDataInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntegrationTestBase extends TestCase
{
    public const MOCK_PRODUCT_SKU = 'amwal_simple';

    protected const MOCK_REF_ID_DATA = [
        RefIdDataInterface::IDENTIFIER => '100',
        RefIdDataInterface::CUSTOMER_ID => '0',
        RefIdDataInterface::TIMESTAMP => '1707916143'
    ];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var GuestCartManagementInterface|null
     */
    private ?GuestCartManagementInterface $guestCartManagement = null;

    /**
     * @var CartItemInterfaceFactory|null
     */
    private ?CartItemInterfaceFactory $cartItemFactory = null;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface|null
     */
    private ?MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId = null;

    /**
     * @var CartRepositoryInterface|null
     */
    private ?CartRepositoryInterface $cartRepository = null;

    /**
     * @var GuestCartItemRepositoryInterface|null
     */
    private ?GuestCartItemRepositoryInterface $guestCartItemRepository = null;

    /**
     * @var RefIdDataInterfaceFactory|null
     */
    private ?RefIdDataInterfaceFactory $refIdDataFactory = null;

    /**
     * @var string|null
     */
    private ?string $guestCartMaskedId = null;

    /**
     * @var int|null
     */
    private ?int $guestCartId = null;

    /**
     * @var AmwalButtonConfigInterface|null
     */
    private ?AmwalButtonConfigInterface $cartButtonConfig = null;

    /**
     * @var array|null
     */
    private ?array $quoteResponse = null;

    /**
     * @var array|null
     */
    private ?array $amwalTransactionData = null;

    /**
     * @var OrderInterface|null
     */
    private ?OrderInterface $orderResponse = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->guestCartManagement = $this->objectManager->get(GuestCartManagementInterface::class);
        $this->cartItemFactory = $this->objectManager->get(CartItemInterfaceFactory::class);
        $this->maskedQuoteIdToQuoteId = $this->objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
        $this->cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->guestCartItemRepository = $this->objectManager->get(GuestCartItemRepositoryInterface::class);
        $this->refIdDataFactory = $this->objectManager->get(RefIdDataInterfaceFactory::class);
    }

    /**
     * @return CartInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    protected function getGuestQuote(): CartInterface
    {
        return $this->cartRepository->get(
            $this->getGuestCartId()
        );
    }

    /**
     * @return string
     * @throws CouldNotSaveException
     */
    protected function createGuestCart(): string
    {
        /** POST /V1/guest-cart */
        $this->guestCartMaskedId = $this->guestCartManagement->createEmptyCart();
        return $this->guestCartMaskedId;
    }

    /**
     * @return string
     * @throws CouldNotSaveException
     */
    protected function getMaskedGuestCartId(): string
    {
        if (!$this->guestCartMaskedId) {
            return $this->createGuestCart();
        }

        return $this->guestCartMaskedId;
    }

    /**
     * @return int
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    protected function getGuestCartId(): int
    {
        if (!$this->guestCartId) {
            $maskedId = $this->getMaskedGuestCartId();
            $this->guestCartId = $this->maskedQuoteIdToQuoteId->execute($maskedId);
        }

        return $this->guestCartId;
    }

    /**
     * @return CartItemInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws InputException
     */
    protected function addSampleProductToCart(): CartItemInterface
    {
        /** @var CartItemInterface $cartItem */
        $cartItem = $this->cartItemFactory->create();
        $cartItem->addData([
            CartItemInterface::KEY_QUOTE_ID => $this->getMaskedGuestCartId(),
            CartItemInterface::KEY_SKU => self::MOCK_PRODUCT_SKU,
            CartItemInterface::KEY_QTY => 1
        ]);

        /** POST /V1/guest-carts/:cartId/items */
        return $this->guestCartItemRepository->save($cartItem);
    }

    /**
     * @return RefIdDataInterface
     */
    protected function getMockRefIdData(): RefIdDataInterface
    {
        /** @var RefIdDataInterface $refIdData */
        $refIdData = $this->refIdDataFactory->create();
        $refIdData->setData(self::MOCK_REF_ID_DATA);

        return $refIdData;
    }

    /**
     * @param string $url
     * @param array $data
     * @param string $method
     *
     * @return mixed
     */
    protected function executeCurl(string $url, array $data, string $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'authority: qa-backend.sa.amwal.tech',
            'amwal: ' . $data['merchantId'],
            'origin: https://store.amwal.tech',
            'referer: https://store.amwal.tech',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * @return AmwalButtonConfigInterface|null
     */
    protected function getCartButtonConfigResponse(): ?AmwalButtonConfigInterface
    {
        return $this->cartButtonConfig;
    }

    /**
     * @param AmwalButtonConfigInterface $cartButtonConfig
     *
     * @return void
     */
    protected function setCartButtonConfigResponse(AmwalButtonConfigInterface $cartButtonConfig): void
    {
        $this->cartButtonConfig = $cartButtonConfig;
    }

    /**
     * @return array|null
     */
    protected function getQuoteResponse(): ?array
    {
        return $this->quoteResponse;
    }

    /**
     * @param array $quoteResponse
     *
     * @return void
     */
    protected function setQuoteResponse(array $quoteResponse): void
    {
        $this->quoteResponse = $quoteResponse;
    }

    /**
     * @return array|null
     */
    protected function getAmwalTransactionData(): ?array
    {
        return $this->amwalTransactionData;
    }

    /**
     * @param array $amwalTransactionData
     *
     * @return void
     */
    protected function setAmwalTransactionData(array $amwalTransactionData): void
    {
        $this->amwalTransactionData = $amwalTransactionData;
    }

    /**
     * @return OrderInterface|null
     */
    protected function getOrderResponse(): ?array
    {
        return $this->orderResponse;
    }

    /**
     * @param OrderInterface $orderResponse
     *
     * @return void
     */
    protected function setOrderResponse(OrderInterface $orderResponse): void
    {
        $this->orderResponse = $orderResponse;
    }
}
