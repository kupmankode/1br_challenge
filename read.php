<?php
/**
 * Requirements:
 * - Read weather from file
 * - Find the max and min for each station
 * - Order by alphabet
 * - Output the result
 */
$time = time();
print date('c', time()).PHP_EOL;
print "Read rows".PHP_EOL;
print "Memory usage in MB: ".(memory_get_usage() / (1024 * 1024)).PHP_EOL;

$handle = fopen('data/weather.csv', 'r');
//$handle = fopen('data/result.csv', 'r');
// loadd all station names
$stations = [];
if ($handle != false) {
    while(($data = fgets($handle, 1000)) != false) {
        $arr = explode(';', $data);
        if (count($arr) == 2) {
            if (!isset($stations[$arr[0]])) {
                $stations[$arr[0]] = [];
                $stations[$arr[0]][0] = $arr[1];
                $stations[$arr[0]][1] = $arr[1];
            } else {
                if ($stations[$arr[0]][0] > $arr[1])
                    $stations[$arr[0]][0] = $arr[1];
                elseif ($stations[$arr[0]][1] < $arr[1])
                    $stations[$arr[0]][1] = $arr[1];
            }
        }
    }
    
    foreach ($stations as $name=>$values) {
        $med = round(($values[0] + $values[1]) / 2, 2);
        //print $name. ': '. $values[0] . '/'. $med . '/' . $values[1]. ';';
    }
}
print date('c', time()).PHP_EOL;
print "Execute time is: ". (time() - $time) . PHP_EOL;
print "Memory usage in MB: ".(memory_get_usage() / (1024 * 1024)).PHP_EOL;