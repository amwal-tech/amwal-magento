<?php
declare(strict_types=1);

namespace Amwal\Payments\Setup\Patch\Data;

use Magento\Customer\Model\Indexer\Address\AttributeProvider as AddressAttributeProvider;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class to add the amwal address id attribute to the customer address entity
 */
class AddCustomerAddressAmwalAddressId implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'amwal_address_id';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            AddressAttributeProvider::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'text',
                'label' => 'Amwal Address Id',
                'input' => 'text',
                'required' => false,
                'sort_order' => 100,
                'visible' => false,
                'system' => false,
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
