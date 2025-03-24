<?php
namespace Amwal\Payments\Model\Event;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Factory class for webhook event handlers
 */
class HandlerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $handlers;

    /**
     * Default handlers mapping
     */
    private const DEFAULT_HANDLERS = [
        'order.success' => OrderSuccess::class,
        'order.failed' => OrderFailed::class,
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     * @param array $handlers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger,
        array $handlers = []
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->handlers = !empty($handlers) ? $handlers : self::DEFAULT_HANDLERS;
    }

    /**
     * Create event handler instance
     *
     * @param string $eventType
     * @return HandlerInterface
     * @throws LocalizedException
     */
    public function create(string $eventType)
    {
        $this->logger->debug(__METHOD__ . ' - Creating handler for event type: ' . $eventType);

        $handlerClass = $this->getHandlerClass($eventType);
        $handler = $this->instantiateHandler($handlerClass);
        $this->validateHandler($handler, $handlerClass);

        $this->logger->debug(__METHOD__ . ' - Successfully created handler for event type: ' . $eventType);
        return $handler;
    }

    /**
     * Get handler class for event type
     *
     * @param string $eventType
     * @return string
     * @throws LocalizedException
     */
    private function getHandlerClass(string $eventType): string
    {
        if (!isset($this->handlers[$eventType])) {
            $this->logger->debug(__METHOD__ . ' - Handler not found for event type: ' . $eventType);
            throw new LocalizedException(__('No handler defined for event type: %1', $eventType));
        }

        $this->logger->debug(__METHOD__ . ' - Using handler class: ' . $this->handlers[$eventType]);
        return $this->handlers[$eventType];
    }

    /**
     * Instantiate handler class
     *
     * @param string $handlerClass
     * @return mixed
     * @throws LocalizedException
     */
    private function instantiateHandler(string $handlerClass)
    {
        try {
            $handler = $this->objectManager->create($handlerClass);
            $this->logger->debug(__METHOD__ . ' - Handler object created successfully');
            return $handler;
        } catch (\Exception $e) {
            $this->logger->debug(__METHOD__ . ' - Failed to create handler object: ' . $e->getMessage());
            throw new LocalizedException(__('Failed to create handler for class %1: %2', $handlerClass, $e->getMessage()));
        }
    }

    /**
     * Validate handler implements required interface
     *
     * @param mixed $handler
     * @param string $handlerClass
     * @return void
     * @throws LocalizedException
     */
    private function validateHandler($handler, string $handlerClass): void
    {
        if (!$handler instanceof HandlerInterface) {
            $this->logger->debug(__METHOD__ . ' - Handler does not implement required interface: ' . HandlerInterface::class);
            throw new LocalizedException(
                __('Event handler %1 must implement %2', $handlerClass, HandlerInterface::class)
            );
        }
    }
}
