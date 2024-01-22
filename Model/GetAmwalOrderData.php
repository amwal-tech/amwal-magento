<?php
declare(strict_types=1);

namespace Amwal\Payments\Model;

use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class GetAmwalOrderData
{

    private $amwalClientFactory;
    private $amwalAddressFactory;
    private $jsonSerializer;
    private $objectFactory;
    private $logger;

    public function __construct(
        AmwalClientFactory $amwalClientFactory,
        AmwalAddressInterfaceFactory $amwalAddressFactory,
        Json $jsonSerializer,
        Factory $objectFactory,
        LoggerInterface $logger
    ) {
        $this->amwalClientFactory = $amwalClientFactory;
        $this->amwalAddressFactory = $amwalAddressFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->objectFactory = $objectFactory;
        $this->logger = $logger;
    }

    /**
     * @param string $amwalOrderId
     * @return DataObject|null
     */
    public function execute(string $amwalOrderId): ?DataObject
    {
        $amwalClient = $this->amwalClientFactory->create();

        try {
            $response = $amwalClient->get('transactions/' . $amwalOrderId);
        } catch (GuzzleException $e) {
            $this->logger->error(sprintf(
                'Unable to retrieve Order data from Amwal. Exception: %s',
                $e->getMessage()
            ));
            return null;
        }

        $responseData = $response->getBody()->getContents();
        $responseData = $this->jsonSerializer->unserialize($responseData);

        $amwalOrderData = $this->objectFactory->create($responseData);

        if ($amwalOrderData->getAddressDetails()) {
            $amwalOrderAddress = $this->amwalAddressFactory->create()->setData($amwalOrderData->getAddressDetails());
            $amwalOrderData->setAddressDetails($amwalOrderAddress);
        }

        if ($amwalOrderData->getShippingDetails()) {
            $shippingDetails = $this->objectFactory->create($amwalOrderData->getShippingDetails());
            $amwalOrderData->setShippingDetails($shippingDetails);
        }

        return $amwalOrderData;
    }
}
