<?php

namespace App\Helpers;

use Cache;
use Illuminate\Support\Facades\DB;

use Config;

class DBConnectionHelper
{

    const DB_CONNECTION_CACHE_KEY = 'db-connection';

    public static function setDBConnection($country): void
    {
        DB::purge('currentHost');

        $db = strtolower($country).'_'.config('database.connections.default-mysql.database');

        config(['database.connections.currentHost' => [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => $db,
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'unix_socket' => config('database.connections.mysql.unix_socket'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
        ]]);

        config(['queue.failed.database' => 'currentHost']);

        DB::setDefaultConnection('currentHost');
    }

    public static function setOtherDbConnection($country, $connection, $baseDb)
    {
        $country = strtolower($country);
        $db = "{$baseDb}_{$country}";

        config(["database.connections.{$connection}.database" => $db]);
    }


    //TODO::move it to country helper
    public static function countryListing($cache=true)
    {
        try {
            DB::connection('mysql-sap')->getPdo();

            $country = Cache::tags('sap_country')->rememberForever('sap_country', function(){
                return DB::connection('mysql-sap')->table('country')->where('status', true)->get();
            });

            return $country;

        } catch (\Exception $e) {

            return collect([
                (object)[
                    'country_code'  => 'MY',
                    'description'   => 'Malaysia',
                    'currency_code' => 'RM',
                    'timezone'      => '+08:00'
                ],
                (object)[
                    'country_code'  => 'BT',
                    'description'   => 'Batam',
                    'currency_code' => 'IDR',
                    'timezone'      => '+07:00'
                ],
                (object)[
                    'country_code'  => 'ID',
                    'description'   => 'Indonesia',
                    'currency_code' => 'IDR',
                    'timezone'      => '+07:00'
                ],
                (object)[
                    'country_code'  => 'PG',
                    'description'   => 'Papa New Guinea',
                    'currency_code' => '',
                    'timezone'      => '+10:00'
                ],
                (object)[
                    'country_code'  => 'PH',
                    'description'   => 'Philippines',
                    'currency_code' => 'PHP',
                    'timezone'      => '+08:00'
                ],
                (object)[
                    'country_code'  => 'PK',
                    'description'   => 'Pakistan',
                    'currency_code' => '',
                    'timezone'      => '+05:00'
                ]
            ]);
        }
    }

    public static function checkDBExists($country): ?bool
    {
        try {

            self::setDBConnection($country);

            $dbExists = Cache::tags(self::DB_CONNECTION_CACHE_KEY)->remember(self::generateCacheKey($country), 365*86400, function(){
                return DB::table('information_schema.schemata')->where('schema_name', config('database.connections.currentHost.database') )->exists();
            });

            if( !$dbExists )
                throw new \Exception('Database doesn\'t exists : '.config('database.connections.currentHost.database'));

            return TRUE;

        } catch (\Exception $e) {

            Cache::tags(self::DB_CONNECTION_CACHE_KEY)->forget(self::generateCacheKey($country));

            dump($e->getMessage());

            return FALSE;
        }
    }
    //TODO::move it to country helper
    public static function getCountryTimezone($countryCode)
    {
        return self::countryListing()->firstWhere('country_code', $countryCode)->timezone;
    }

    //TODO::move it to country helper
    public static function getCountryCurrency($countryCode)
    {
        return self::countryListing()->firstWhere('country_code', $countryCode)->currency_code;
    }

    //TODO::move it to country helper
    public static function getCountry($keyword, $field)
    {
        return self::countryListing()->where($field, $keyword)->first();
    }


    private static function generateCacheKey($country)
    {
        return self::DB_CONNECTION_CACHE_KEY. '_' .$country;
    }
}
