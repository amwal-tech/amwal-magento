<?php

namespace Amwal\Payments\Test\Unit\Plugin\Quote;

use Amwal\Payments\Plugin\Quote\UpdateQuote;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Amwal\Payments\Model\Config\Checkout\ConfigProvider;
use Magento\Quote\Model\Quote\Payment;

class UpdateQuoteTest extends TestCase
{
    /**
     * @var UpdateQuote
     */
    private $updateQuote;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var QuoteManagement|MockObject
     */
    private $quoteManagementMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    private const STORE_ID = 1;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->quoteManagementMock = $this->createMock(QuoteManagement::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->storeMock = $this->createMock(StoreInterface::class);

        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->method('getId')->willReturn(self::STORE_ID);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->updateQuote = new UpdateQuote($this->storeManagerMock);
    }

    public function testBeforeSubmit()
    {
        $storeIdPassed = null;
        $isAmwalApiCallPassed = null;
        $importDataArgument = null;

        $this->quoteMock->expects($this->once())
            ->method('setData')
            ->with($this->equalTo('is_amwal_api_call'), $this->equalTo(true))
            ->willReturnCallback(function ($key, $value) use (&$isAmwalApiCallPassed) {
                $isAmwalApiCallPassed = $value;
            });

        $this->paymentMock->expects($this->once())
            ->method('importData')
            ->with($this->equalTo(['method' => ConfigProvider::CODE]))
            ->willReturnCallback(function ($arg) use (&$importDataArgument) {
                $importDataArgument = $arg;
            });

        $this->quoteMock->expects($this->once())
            ->method('setStoreId')
            ->with($this->equalTo(self::STORE_ID));

        $this->quoteMock->expects($this->once())
            ->method('setStoreId')
            ->with($this->equalTo(self::STORE_ID))
            ->willReturnCallback(function ($storeId) use (&$storeIdPassed) {
                $storeIdPassed = $storeId;
            });

        $this->updateQuote->beforeSubmit($this->quoteManagementMock, $this->quoteMock);

        // Assert outcomes
        $this->assertEquals(self::STORE_ID, $storeIdPassed);
        $this->assertEquals(true, $isAmwalApiCallPassed);
        $this->assertEquals(['method' => ConfigProvider::CODE], $importDataArgument);
    }
}
