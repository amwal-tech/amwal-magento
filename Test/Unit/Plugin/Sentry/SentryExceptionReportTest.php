<?php
declare(strict_types=1);

namespace Amwal\Payments\Test\Unit\Plugin\Sentry;

use Amwal\Payments\Model\Config;
use Amwal\Payments\Plugin\Sentry\SentryExceptionReport;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http;
use Magento\Framework\App\State;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Amwal\Payments\Test\Unit\Plugin\Sentry\Fixture\DummyAmwalException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class SentryExceptionReportTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var SentryExceptionReport
     */
    private SentryExceptionReport $sentryReport;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->stateMock = $this->createMock(State::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->sentryReport = new SentryExceptionReport(
            $this->configMock,
            $this->scopeConfigMock,
            $this->stateMock,
            $this->loggerMock
        );
    }

    /**
     * Helper to invoke private methods via Reflection
     *
     * @param object $object
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    private function invokeMethod(object $object, string $methodName, array $args = [])
    {
        $reflection = new ReflectionMethod(get_class($object), $methodName);
        return $reflection->invokeArgs($object, $args);
    }

    /**
     * Helper to create an Exception with custom file, trace, and previous
     *
     * @param string $file
     * @param array $trace
     * @param \Throwable|null $previous
     * @return \Exception
     */
    private function createException(string $file = '', array $trace = [], ?\Throwable $previous = null): \Exception
    {
        $exception = new \RuntimeException('Test exception', 0, $previous);

        $fileProp = new ReflectionProperty(\Exception::class, 'file');
        $fileProp->setValue($exception, $file);

        $traceProp = new ReflectionProperty(\Exception::class, 'trace');
        $traceProp->setValue($exception, $trace);

        return $exception;
    }

    /**
     * Test that an exception within Amwal namespace is detected as an Amwal exception.
     */
    public function testIsAmwalExceptionWithAmwalNamespace(): void
    {
        $amwalException = new DummyAmwalException('Amwal error');

        $result = $this->invokeMethod($this->sentryReport, 'isAmwalException', [$amwalException]);
        $this->assertTrue($result);
    }

    /**
     * Test that an exception whose path is inside a project directory containing "amwal"
     * (e.g. /var/www/amwal-magento-store/vendor/magento/...) is NOT detected as an Amwal exception.
     */
    public function testIsAmwalExceptionDoesNotMatchMagentoFilesInAmwalNamedProjectDirectory(): void
    {
        $exception = $this->createException(
            '/var/www/amwal-magento-store/vendor/magento/framework/App/Http.php',
            [
                [
                    'class' => 'Magento\\Framework\\App\\Http',
                    'file' => '/var/www/amwal-magento-store/vendor/magento/framework/App/Http.php',
                    'line' => 150
                ]
            ]
        );

        $result = $this->invokeMethod($this->sentryReport, 'isAmwalException', [$exception]);
        $this->assertFalse($result);
    }

    /**
     * Test that an exception thrown from an Amwal vendor path is detected.
     */
    public function testIsAmwalExceptionMatchesAmwalVendorPath(): void
    {
        $exception = $this->createException(
            '/var/www/magento/vendor/amwal/payments/Model/Checkout.php'
        );

        $result = $this->invokeMethod($this->sentryReport, 'isAmwalException', [$exception]);
        $this->assertTrue($result);
    }

    /**
     * Test that an exception thrown from an Amwal app/code path is detected.
     */
    public function testIsAmwalExceptionMatchesAmwalAppCodePath(): void
    {
        $exception = $this->createException(
            '/var/www/magento/app/code/Amwal/Payments/Model/Checkout.php'
        );

        $result = $this->invokeMethod($this->sentryReport, 'isAmwalException', [$exception]);
        $this->assertTrue($result);
    }

    /**
     * Test that an exception with an Amwal frame in the stack trace is detected.
     */
    public function testIsAmwalExceptionMatchesTraceFrame(): void
    {
        $exception = $this->createException(
            '/var/www/magento/vendor/magento/framework/App/Http.php',
            [
                [
                    'class' => 'Amwal\\Payments\\Model\\Checkout\\PlaceOrder',
                    'file' => '/var/www/magento/vendor/amwal/payments/Model/Checkout/PlaceOrder.php',
                    'line' => 45
                ]
            ]
        );

        $result = $this->invokeMethod($this->sentryReport, 'isAmwalException', [$exception]);
        $this->assertTrue($result);
    }

    /**
     * Test that chained previous Amwal exception is detected.
     */
    public function testIsAmwalExceptionMatchesPreviousAmwalException(): void
    {
        $amwalException = new DummyAmwalException('Original Amwal error');

        $wrapperException = $this->createException(
            '/var/www/magento/vendor/magento/framework/App/Http.php',
            [],
            $amwalException
        );

        $result = $this->invokeMethod($this->sentryReport, 'isAmwalException', [$wrapperException]);
        $this->assertTrue($result);
    }

    /**
     * Test that beforeCatchException skips reporting if exception is not from Amwal.
     */
    public function testBeforeCatchExceptionSkipsNonAmwalException(): void
    {
        $this->configMock->expects($this->never())->method('isSentryReportEnabled');

        $httpMock = $this->createMock(Http::class);
        $bootstrapMock = $this->createMock(Bootstrap::class);

        $nonAmwalException = $this->createException(
            '/var/www/amwal-store/vendor/magento/framework/App/Http.php',
            [
                [
                    'class' => 'Magento\\Framework\\App\\Http',
                    'file' => '/var/www/amwal-store/vendor/magento/framework/App/Http.php'
                ]
            ]
        );

        $this->sentryReport->beforeCatchException($httpMock, $bootstrapMock, $nonAmwalException);
    }

    /**
     * Test setTags handles string, integer, and array values without error.
     */
    public function testSetTagsAcceptsStringAndNumericValues(): void
    {
        $this->configMock->method('isSentryReportEnabled')->willReturn(false);

        // When Sentry is disabled, setTags should return false safely without throwing TypeError
        $resultString = $this->sentryReport->setTags('key', 'value');
        $this->assertFalse($resultString);

        $resultInt = $this->sentryReport->setTags('order_id', 12345);
        $this->assertFalse($resultInt);

        $resultArray = $this->sentryReport->setTags(['order_id' => 12345, 'action' => 'pay']);
        $this->assertFalse($resultArray);
    }

    /**
     * Test initialization is guarded so Sentry is not initialized multiple times.
     */
    public function testInitializeSentrySDKGuardsAgainstMultipleInits(): void
    {
        $this->configMock->method('isSentryReportEnabled')->willReturn(true);
        $this->stateMock->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->scopeConfigMock->method('getValue')
            ->with(Store::XML_PATH_SECURE_BASE_URL)
            ->willReturn('https://example.com/');

        // First initialization
        $result1 = $this->invokeMethod($this->sentryReport, 'initializeSentrySDK');
        $this->assertTrue($result1);

        // Verify isInitialized property is now true
        $reflectionClass = new ReflectionClass(SentryExceptionReport::class);
        $initProp = $reflectionClass->getProperty('isInitialized');
        $this->assertTrue($initProp->getValue($this->sentryReport));

        // Second call should return true immediately via the guard without re-initializing Sentry
        $result2 = $this->invokeMethod($this->sentryReport, 'initializeSentrySDK');
        $this->assertTrue($result2);
    }

    /**
     * Test report does nothing when Sentry reporting is disabled in config.
     */
    public function testReportDoesNothingWhenDisabled(): void
    {
        $this->configMock->method('isSentryReportEnabled')->willReturn(false);

        // Should not throw or fail
        $this->sentryReport->report(new \RuntimeException('Test error'));
        $this->assertTrue(true);
    }
}
