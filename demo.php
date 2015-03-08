<?php

require_once __DIR__ . '/vendor/autoload.php';

use JankProfiler\JankProfiler;
use Illuminate\Support\Collection;
use SpeedBag\SpeedBag;

$regions = json_decode(file_get_contents('./fixtures/admin_regions.json'));
$cloneArrayContents = function($arr) {
    return array_map(function ($elem) {
        return clone $elem;
    }, $arr);
};
$collection = new JankProfiler(Collection::class, $cloneArrayContents($regions));
$speedBag = new JankProfiler(SpeedBag::class, $cloneArrayContents($regions));

report('construct', $speedBag, $collection);

$speedBag->map(function ($region) {
    return strtoupper($region->name);
});

$collection->map(function ($region) {
    return strtoupper($region->name);
});

report('map', $speedBag, $collection);

$usStates = function ($region) {
    return property_exists($region, 'countryName') && $region->countryName == 'United States';
};

$speedBag->filter($usStates);
$collection->filter($usStates);
report('filter', $speedBag, $collection);

$isMeiveldForSpeedBag = function ($elem) {
    return $elem->geonameId == 3577166;
};
$isMeiveldForCollection = function ($key, $elem) {
    return $elem->geonameId == 3577166;
};
$speedBag->contains($isMeiveldForSpeedBag);
$collection->contains($isMeiveldForCollection);
report('contains', $speedBag, $collection);

$collection->first($isMeiveldForCollection);
$speedBag->first($isMeiveldForSpeedBag);
report('first', $speedBag, $collection);

$collection->last();
$speedBag->last();
report('last', $speedBag, $collection);

$speedBag->reverse();
$collection->reverse();
report('reverse', $speedBag, $collection);

$regionsByCountry = json_decode(file_get_contents('./fixtures/admin_regions_by_country.json'));
$collection = new JankProfiler(Collection::class, $regionsByCountry);
$speedBag = new JankProfiler(SpeedBag::class, $regionsByCountry);

$collection->flatten();
$speedBag->flatten();
report('flatten', $speedBag, $collection);

function report($action, $speedBag, $collection)
{
    $tmp = $speedBag->report('array');
    $report1 = array_pop($tmp);
    $tmp = $collection->report('array');
    $report2 = array_pop($tmp);

    var_dump([
        'action' => $action,
        'speedBag' => [
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
}
