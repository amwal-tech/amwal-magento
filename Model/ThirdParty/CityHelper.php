<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\ThirdParty;

use Magento\Framework\App\ResourceConnection;

class CityHelper
{

    /** @var ResourceConnection  */
    private ResourceConnection $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string|null $locale
     * @return array
     */
    public function getCityCodes($locale = null): array
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
