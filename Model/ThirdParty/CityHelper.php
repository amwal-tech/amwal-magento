<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\ThirdParty;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Locale\ResolverInterface;

class CityHelper
{

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var ResolverInterface
     */
    protected ResolverInterface $localeResolver;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ResolverInterface $localeResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->localeResolver = $localeResolver;
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
            // Panasonic for sony plugin
            $isEnableForPanasonic = $connection->tableColumnExists($citiesTable, 'is_enable_for_panasonic');
            $condition = $connection->quoteInto('city.status = ?', 1);
            if ($isEnableForPanasonic) {
                $sql = $connection->select()
                    ->from(['city' => $citiesTable], ['city', 'id', 'country_id'])
                    ->where($condition);
                foreach ($connection->fetchAll($sql) as $city) {
                    $cityCodes[$city['country_id']][$city['id']][] = $city['city'];
                }
            }else {
                $sql = $connection->select()
                    ->from(['city' => $citiesTable], ['city', 'state_id', 'country_id'])
                    ->where($condition);
                foreach ($connection->fetchAll($sql) as $city) {
                    $cityCodes[$city['country_id']][$city['state_id']][] = $city['city'];
                }
            }
        }

        // Torod\CityRegion
        $torodCityRegionName = $this->resourceConnection->getTableName('torod_cityregion_cityregion');
        if ($connection->isTableExists($torodCityRegionName)) {
            $sql = $connection->select()->from(
                ['city' => $torodCityRegionName],
                ['city_name', 'city_name_ar', 'region_id', 'country_id']
            );
            foreach ($connection->fetchAll($sql) as $city) {
                $cityCodes[$city['country_id']][$city['region_id']][] = strpos($this->localeResolver->getLocale(), 'ar') !== false ? $city['city_name_ar'] : $city['city_name'];

            }
        }

        // Magento 2 Region & City Dropdown Manager
        $tableName = $this->resourceConnection->getTableName('directory_country_region_city');
        $localeCityTableName = $this->resourceConnection->getTableName('directory_country_region_city_name');
        if ($connection->isTableExists($tableName) && $connection->isTableExists($localeCityTableName)) {
            $sql = $connection->select()
                ->from(['city' => $tableName])
                ->joinLeft(
                    ['lngname' => $localeCityTableName],
                    'city.city_id = lngname.city_id AND lngname.locale = :region_locale',
                    ['name']
                );

            foreach ($connection->fetchAll($sql, [':region_locale' => $this->localeResolver->getLocale()]) as $city) {
                $cityCodes[$city['country_id']][$city['region_id']][] = $city['name'] ?? $city['default_name'];
            }
        }

        return $cityCodes;
    }


    /**
     * @return array
     */
    public function getZipCodes(): array
    {
        $zipCodes = [];
        $connection = $this->resourceConnection->getConnection();
        $citiesTable = $this->resourceConnection->getTableName('cities_zips');
        if ($connection->isTableExists($citiesTable)) {
            $condition = $connection->quoteInto('city.status = ?', 1);
            $sql = $connection->select()
                ->from(['city' => $citiesTable], ['zip_name', 'city_id', 'country_id'])
                ->where($condition);

            foreach ($connection->fetchAll($sql) as $city) {
                $zipCodes[$city['country_id']][$city['city_id']][] = $city['zip_name'];
            }
        }
        return $zipCodes;
    }

}
