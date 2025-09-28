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

        $cityCodes = array_merge_recursive(
            $cityCodes,
            $this->getCitiesFromMainTable($connection),
            $this->getCitiesFromTorodRegion($connection),
            $this->getCitiesFromDirectoryTables($connection)
        );

        return $cityCodes;
    }

    private function getCitiesFromMainTable($connection): array
    {
        $cityCodes = [];
        $citiesTable = $this->resourceConnection->getTableName('cities');

        if (!$connection->isTableExists($citiesTable)) {
            return [];
        }

        $sql = $connection->select()
            ->from(['city' => $citiesTable], ['city', 'city_ar', 'state_id', 'country_id'])
            ->where('city.status = ?', 1);

        foreach ($connection->fetchAll($sql) as $city) {
            $cityCodes[$city['country_id']][$city['state_id']][] =
                $this->getLocalizedCityName($city['city'], $city['city_ar']);
        }

        return $cityCodes;
    }

    private function getCitiesFromTorodRegion($connection): array
    {
        $cityCodes = [];
        $table = $this->resourceConnection->getTableName('torod_cityregion_cityregion');

        if (!$connection->isTableExists($table)) {
            return [];
        }

        $sql = $connection->select()->from(
            ['city' => $table],
            ['city_name', 'city_name_ar', 'region_id', 'country_id']
        );

        foreach ($connection->fetchAll($sql) as $city) {
            $cityCodes[$city['country_id']][$city['region_id']][] =
                $this->getLocalizedCityName($city['city_name'], $city['city_name_ar']);
        }

        return $cityCodes;
    }

    private function getCitiesFromDirectoryTables($connection): array
    {
        $cityCodes = [];
        $table = $this->resourceConnection->getTableName('directory_country_region_city');
        $localeTable = $this->resourceConnection->getTableName('directory_country_region_city_name');

        if (!$connection->isTableExists($table) || !$connection->isTableExists($localeTable)) {
            return [];
        }

        $sql = $connection->select()
            ->from(['city' => $table])
            ->joinLeft(
                ['lngname' => $localeTable],
                'city.city_id = lngname.city_id AND lngname.locale = :region_locale',
                ['name', 'default_name']
            );

        foreach ($connection->fetchAll($sql, [':region_locale' => $this->localeResolver->getLocale()]) as $city) {
            $cityCodes[$city['country_id']][$city['region_id']][] =
                $city['name'] ?? $city['default_name'];
        }

        return $cityCodes;
    }

    private function getLocalizedCityName(?string $default, ?string $arabic): string
    {
        $isArabic = strpos($this->localeResolver->getLocale(), 'ar') !== false;
        return $isArabic && !empty($arabic) ? $arabic : ($default ?? '');
    }
}
