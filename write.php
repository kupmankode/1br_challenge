<?php
 // Check CLI
 if (PHP_SAPI != "cli") {
    die('Not a command');
}
// Get args
if (count($argv) != 3) {
    die('Invalid command');
}
$rows = $argv[1];
if (!is_numeric($rows)){
    die('The number of rows must be numeric.');
}
$file = 'data/'.$argv[2];
if (file_exists($file)){
    die('The file exists. Please choose another name.');
}
// Read base data
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
    die('Please check base data file.');

//set temperature range
$temp_range = [-99, 99];
$time = time();
print date('c', time()).PHP_EOL;
print "Generate ".number_format($rows)." rows".PHP_EOL;
print "Memory usage in MB: ".(memory_get_usage() / (1024 * 1024)).PHP_EOL;

$limit = 1000000;
$step = 0;
$is_first_write = false;
$end_line = "\n";
while ($rows > 0) {
    if ($rows > $limit) {
       $step = $limit;
       $rows = $rows - $limit;
    } else {
        $step = $rows;
        $rows = 0;
    }
    if ($rows == 0) {
        $end_line = "";
    }
    $arr = build_weather_data($stations, $temp_range, $step);
    print "Memory usage to store array in MB: ".(memory_get_usage() / (1024 * 1024)).PHP_EOL;
    
    file_put_contents($file, implode("\n", $arr).$end_line, FILE_APPEND);
    

}

print "Execute time is: ". (time() - $time) . PHP_EOL;
print date('c', time()).PHP_EOL;
/////////////////////////////////////////////////////
function build_weather_data($stations, $temp_range, $rows) {
    $total = count($stations) - 1;
    $arr = [];
    for($i=0;$i<$rows;$i++) {
        $arr[] = $stations[rand(0,$total)].';'.rand($temp_range[0], $temp_range[1]) + (rand(1,99) / 100);
    }
    return $arr;
}