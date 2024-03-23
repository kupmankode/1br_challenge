<?php
$handle = fopen('data/weather_stations.csv', 'r');
// loadd all station names
$stations = [];
if ($handle != false) {
    fgets($handle, 1000);
    fgets($handle, 1000);
    while(($data = fgets($handle, 1000)) != false) {
        $arr = explode(';', $data);
        if (count($arr) == 2) {
            $stations[] = $arr[0];
        }
    }
}
if (empty($stations))
    die('Check station template file');
//get number of rows from command line
$rows = 1000000;

//set temperature range
$temp_range = [-99, 99];

// if less than millions, use array otherwise use generator
if ($rows < 1000001) {
    $arr = build_weather_data($stations, $temp_range, $rows);
    file_put_contents('data/result.csv', implode("\n", $arr));
}

function build_weather_data($stations, $temp_range, $rows) {
    $total = count($stations) - 1;
    $arr = [];
    for($i=0;$i<$rows;$i++) {
        $arr[] = $stations[rand(0,$total)].';'.rand($temp_range[0], $temp_range[1]) + (rand(1,99) / 100);
    }
    return $arr;
}