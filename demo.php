<?php

require_once __DIR__ . '/vendor/autoload.php';

use JankProfiler\JankProfiler;

$regions = json_decode(file_get_contents('./fixtures/admin_regions.json'));
$cloneArrayContents = function($arr) {
    return array_map(function ($elem) {
        return clone $elem;
    }, $arr);
};
$collection = new JankProfiler('Illuminate\\Support\\Collection', $cloneArrayContents($regions));
$speedBag = new JankProfiler('SpeedBag\\SpeedBag', $cloneArrayContents($regions));

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


$regionsByCountry = json_decode(file_get_contents('./fixtures/admin_regions_by_country.json'));
$collection = new JankProfiler('Illuminate\\Support\\Collection', $regionsByCountry);
$speedBag = new JankProfiler('SpeedBag\\SpeedBag', $regionsByCountry);

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
