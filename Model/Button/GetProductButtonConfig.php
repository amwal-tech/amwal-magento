<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Button;

use Amwal\Payments\Api\Data\AmwalAddressInterfaceFactory;
use Amwal\Payments\Api\Data\AmwalButtonConfigInterface;
use Amwal\Payments\Api\Data\RefIdDataInterface;
use Amwal\Payments\Model\Data\AmwalButtonConfig;
use Amwal\Payments\Model\Data\AmwalButtonConfigFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GetProductButtonConfig extends GetConfig
{
    /**
     * @param RefIdDataInterface $refIdData
     * @param int $productId
     * @return AmwalButtonConfigInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(RefIdDataInterface $refIdData, int $productId): AmwalButtonConfigInterface
    {
        /** @var AmwalButtonConfig $buttonConfig */
        $buttonConfig = $this->buttonConfigFactory->create();
        $quote = $this->checkoutSessionFactory->create()->getQuote();

        $this->addGenericButtonConfig($buttonConfig, $refIdData, $quote);

        $buttonConfig->setAmount($this->getAmount($productId));
        $buttonConfig->setId($this->getButtonId($productId));

        return $buttonConfig;
    }

    /**
     * @param int|null $productId
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getAmount(int $productId): float
    {
        $product = $this->productRepository->getById($productId);
        return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
    }
}
