<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\ThirdParty;

use Amwal\Payments\ViewModel\ExpressCheckoutButton;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class CityHelper
{
    /** @var LocaleResolver  */
    private LocaleResolver $localeResolver;

    /** @var ResourceConnection  */
    private ResourceConnection $resourceConnection;

    /** @var ExpressCheckoutButton */
    private ExpressCheckoutButton $expressCheckoutButton;

    /**
     * @param LocaleResolver $localeResolver
     * @param ResourceConnection $resourceConnection
     * @param ExpressCheckoutButton $expressCheckoutButton
     */
    public function __construct(
        LocaleResolver $localeResolver,
        ResourceConnection $resourceConnection,
        ExpressCheckoutButton $expressCheckoutButton
    ) {
        $this->localeResolver = $localeResolver;
        $this->resourceConnection = $resourceConnection;
        $this->expressCheckoutButton = $expressCheckoutButton;
    }

    /**
     * @return array
     */
    public function getCityCodes(): array
    {
        $cityCodes = [];
        $connection = $this->resourceConnection->getConnection();
        $citiesTable = $this->resourceConnection->getTableName('cities');

        if ($connection->isTableExists($citiesTable)) {
            $condition = $connection->quoteInto('city.status = ?', 1);
            $sql = $connection->select()
                ->from(['city' => $citiesTable], ['city', 'state_id', 'country_id'])
                ->where($condition);

            foreach ($connection->fetchAll($sql) as $city) {
                $cityCodes[$city['country_id']][$city['state_id']][] = $city['city'];
            }
        }

        $tableName = $this->resourceConnection->getTableName('directory_country_region_city');
        $localeCityTableName = $this->resourceConnection->getTableName('directory_country_region_city_name');

        if ($connection->isTableExists($tableName) && $connection->isTableExists($localeCityTableName)) {
            $locale = $this->expressCheckoutButton->getLocale();
            $condition = $connection->quoteInto('lng.locale = ?', $locale);
            $sql = $connection->select()
                ->from(['city' => $tableName])
                ->joinLeft(['lng' => $localeCityTableName], 'city.city_id = lng.city_id AND ' . $condition, ['name']);

            foreach ($connection->fetchAll($sql) as $city) {
                $cityCodes[$city['country_id']][$city['region_id']][] = $city['name'] ?? $city['default_name'];
            }
        }

        return $cityCodes;
    }
}
