<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\ThirdParty;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class CityHelper
{
    /** @var LocaleResolver  */
    private LocaleResolver $localeResolver;

    /** @var ResourceConnection  */
    private ResourceConnection $resourceConnection;

    /**
     * @param LocaleResolver $localeResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        LocaleResolver $localeResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->localeResolver = $localeResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return array
     */
    public function getCityCodes(): array
    {
        $locale = $this->localeResolver->getLocale();
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('directory_country_region_city');
        $localeCityTableName = $this->resourceConnection->getTableName('directory_country_region_city_name');
        $citiesTable = $this->resourceConnection->getTableName('cities');

        if (!$connection->isTableExists($tableName) || !$connection->isTableExists($localeCityTableName) || !$connection->isTableExists($citiesTable)) {
            return [];
        }

        if ($connection->isTableExists($citiesTable)) {
            $sql = $connection->select()->from(
                ['city' => $citiesTable],
                ['state_id', 'country_id', 'city']
            );
            foreach ($connection->fetchAll($sql) as $city) {
                $cityCodes[$city['country_id']][$city['state_id']][] = $city['city'];
            }
            return $cityCodes;
        }

        $condition = $connection->quoteInto('lng.locale = ?', $locale);
        $sql = $connection->select()->from(
            ['city' => $tableName]
        )->joinLeft(
            ['lng' => $localeCityTableName],
            'city.city_id = lng.city_id AND ' . $condition,
            ['name']
        );

        $cityCodes = [];
        foreach ($connection->fetchAll($sql) as $city) {
            $cityCodes[$city['country_id']][$city['region_id']][] = $city['name'] ?? $city['default_name'];
        }

        return $cityCodes;
    }
}
