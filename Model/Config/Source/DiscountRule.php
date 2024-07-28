<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

class DiscountRule implements ArrayInterface
{
    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * Constructor
     *
     * @param RuleCollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $rules = $this->ruleCollectionFactory->create();
        foreach ($rules as $rule) {
            if(!$rule->getIsActive() || !$rule->getCode()){
                continue;
            }
            $options[] = [
                'value' => $rule->getId().'-'.$rule->getCode(),
                'label' => $rule->getName(),
            ];
        }
        return $options;
    }
}
