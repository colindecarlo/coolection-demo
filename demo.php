<?php

require_once __DIR__ . '/vendor/autoload.php';

use JankProfiler\JankProfiler;
use Illuminate\Support\Collection;
use GuzzleHttp\Client as HttpClient;

function get_list_of_admin_regions()
{
    $geonames = new HttpClient([
        'base_url' => 'http://api.geonames.org',
        'defaults' => [
            'query' => ['username' => 'coolection']
        ]
    ]);
    
    $countries = json_decode($geonames->get('/countryInfoJSON')->getBody()->getContents())->geonames;

    $getAdminRegions = function ($country) use ($geonames) {
        $response = $geonames->get('/childrenJSON', ['query' => ['geonameId' => $country->geonameId]])
            ->getBody()
            ->getContents();
        $regions = json_decode($response);

        return property_exists($regions, 'geonames')
            ? $regions->geonames
            : [];
    };

    $regions = [];
    foreach($countries as $country) {
        $regions = array_merge($regions, $getAdminRegions($country));
    }

    return $regions;
}

//$regions = get_list_of_admin_regions();
$regions = json_decode(file_get_contents('./fixtures/admin_regions.json'));
$cloneArrayContents = function() use ($regions) {
    return array_map(function ($elem) {
        return clone $elem;
    }, $regions);
};
$collection = new JankProfiler('Illuminate\\Support\\Collection', $cloneArrayContents());
$coolection = new JankProfiler('Coolection\\Coolection', $cloneArrayContents());

$report1 = $coolection->report('array')[0];
$report2 = $collection->report('array')[0];

var_dump([
    'coolection' => [
        'memory_before' => $report1['memory_before'],
        'memory_after' => $report1['memory_after'],
        'delta' => $report1['memory_after'] - $report1['memory_before']
    ],
    'collection' => [
        'memory_before' => $report2['memory_before'],
        'memory_after' => $report2['memory_after'],
        'delta' => $report2['memory_after'] - $report2['memory_before']
    ],
]);

$coolection->map(function ($region) {
    return strtoupper($region->name);
});

$collection->map(function ($region) {
    return strtoupper($region->name);
});

$tmp = $coolection->report('array');
$report1 = array_pop($tmp);
$tmp = $collection->report('array');
$report2 = array_pop($tmp);

var_dump([
    'coolection' => [
        'memory_before' => $report1['memory_before'],
        'memory_after' => $report1['memory_after'],
        'memory_delta' => $report1['memory_after'] - $report1['memory_before'],
        'execution_delta' => $report1['end_time'] - $report1['start_time']
    ],
    'collection' => [
        'memory_before' => $report2['memory_before'],
        'memory_after' => $report2['memory_after'],
        'memory_delta' => $report2['memory_after'] - $report2['memory_before'],
        'execution_delta' => $report2['end_time'] - $report2['start_time']
    ],
]);
