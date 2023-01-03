<?php
declare(strict_types=1);

namespace Amwal\Payments\ViewModel;

use Amwal\Payments\Api\Data\RefIdDataInterface;

class ExpressCheckoutButtonCart extends ExpressCheckoutButton
{
    /**
     * @var string|null;
     */
    private ?string $quoteId = null;

    /**
     * @return string|null
     */
    public function getQuoteId(): ?string
    {
        return $this->quoteId;
    }

    /**
     * @param string $quoteId
     * @return void
     */
    public function setQuoteId(string $quoteId): void
    {
        $this->quoteId = $quoteId;
    }

    /**
     * @return RefIdDataInterface
     */
    public function getRefIdData(): RefIdDataInterface
    {
        return $this->refIdDataFactory->create()
            ->setIdentifier($this->getQuoteId())
            ->setCustomerId($this->getCustomerId())
            ->setTimestamp($this->getTimestamp());
    }
}
