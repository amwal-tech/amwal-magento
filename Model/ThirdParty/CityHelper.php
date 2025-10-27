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

        // Check if city_ar column exists
        $tableColumns = $connection->describeTable($citiesTable);
        $hasArabicColumn = isset($tableColumns['city_ar']);

        $selectFields = ['city', 'state_id', 'country_id'];
        if ($hasArabicColumn) {
            $selectFields[] = 'city_ar';
        }

        $sql = $connection->select()
            ->from(['city' => $citiesTable], $selectFields)
            ->where('city.status = ?', 1);

        foreach ($connection->fetchAll($sql) as $city) {
            $cityArabic = $hasArabicColumn ? ($city['city_ar'] ?? null) : null;
            $cityCodes[$city['country_id']][$city['state_id']][] =
                $this->getLocalizedCityName($city['city'], $cityArabic);
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

        // Check what columns exist in the table
        $tableColumns = $connection->describeTable($table);
        $selectFields = ['region_id', 'country_id'];

        // Add city name fields if they exist
        if (isset($tableColumns['city_name'])) {
            $selectFields[] = 'city_name';
        }
        if (isset($tableColumns['city_name_ar'])) {
            $selectFields[] = 'city_name_ar';
        }

        $sql = $connection->select()->from(['city' => $table], $selectFields);

        foreach ($connection->fetchAll($sql) as $city) {
            $cityName = $city['city_name'] ?? '';
            $cityNameAr = $city['city_name_ar'] ?? null;
            $cityCodes[$city['country_id']][$city['region_id']][] =
                $this->getLocalizedCityName($cityName, $cityNameAr);
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

        // Check what columns exist in the locale table
        $localeTableColumns = $connection->describeTable($localeTable);
        $selectFields = ['name'];
        if (isset($localeTableColumns['default_name'])) {
            $selectFields[] = 'default_name';
        }

        $sql = $connection->select()
            ->from(['city' => $table])
            ->joinLeft(
                ['lngname' => $localeTable],
                'city.city_id = lngname.city_id AND lngname.locale = :region_locale',
                $selectFields
            );

        foreach ($connection->fetchAll($sql, [':region_locale' => $this->localeResolver->getLocale()]) as $city) {
            $cityName = $city['name'] ?? $city['default_name'] ?? '';
            $cityCodes[$city['country_id']][$city['region_id']][] = $cityName;
        }

        return $cityCodes;
    }

    private function getLocalizedCityName(?string $default, ?string $arabic): string
    {
        $isArabic = str_starts_with($this->localeResolver->getLocale(), 'ar');
        return $isArabic && !empty($arabic) ? $arabic : ($default ?? '');
    }
}
