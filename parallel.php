<?php
use parallel\Runtime;
use parallel\Channel;

$thread_function = function ($file, $from, $to, Channel $ch) {
    $stations = [];
    $handle = fopen($file, 'r');
    fseek($handle, $from);
    while($from < $to) {
        $data = fgets($handle, 1000);
        $arr = explode(';', $data);
        if (count($arr) == 2) {
            $arr[1] = (int)$arr[1];
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
        $from = ftell($handle);
    }
    $ch->send($stations);
};
//////////////////////////////////////////////////////////////////////
/**
 * Requirements:
 * - Read weather from file
 * - Find the max and min for each station
 * - Order by alphabet
 * - Output the result
 */

 // Check CLI
if (PHP_SAPI != "cli") {
    die('Not a command');
}
// Get args
if (count($argv) != 2) {
    die('Invalid command');
}
$file = 'data/'.$argv[1];
if (!file_exists($file)){
    die('Can\'t locate the file');
}

// Pass all basic checks, continue...
$time = time();
print date('c', time()).PHP_EOL;
print "Memory usage in MB: ".(memory_get_usage() / (1024 * 1024)).PHP_EOL;

$threads = 4;
$handle = fopen($file, 'r');
// loadd all station names
$stations = [];
$trunks = [];
$from = 0;

if ($handle != false) {

    // Search the filesize
    $size = filesize($file);
    $trunk = round($size / $threads);
    $pos = $trunk;
    for($i=0;$i<$threads;$i++) {
        // move cursor to the position
        fseek($handle, $pos);
        // read the left over of the current line
        fgets($handle, 1000);
        // now we got the correct current of then next line
        $next_line = ftell($handle);
        
        // put everything in array
        $trunks[$i] = [$from, $next_line];
        
        // set the next pos
        $pos += $trunk;
        if ($pos > $size) {
            $pos = $size;
        }
        $from = $next_line;
        
    }
    $arr = [];
    // channel where the date will be sharead
    try {
        $ch = new Channel();
        for($i=0;$i<$threads;$i++) {
            $r = new Runtime();
            $args = array();
            $args[0] = $file;
            $args[1] = $trunks[$i][0];
            $args[2] = $trunks[$i][1];
            $args[3] = $ch;
            $r->run($thread_function, $args);
            $arr[] = $ch->recv();
        }
        // close channel
        $ch->close();

        print "Data received by the channel".PHP_EOL;
    } catch (Error $err) {
        print "Error: " . $err->getMessage() . PHP_EOL;
    } catch (Exception $e) {
        print "Exception: " . $e->getMessage(). PHP_EOL;
    }
    $stations = [];
    foreach ($arr as $ar) {
        foreach ($ar as $name=>$values) {
            if (!isset($stations[$name])) {
                $stations[$name] = [];
                $stations[$name][0] = $values[1];
                $stations[$name][1] = $values[1];
            } else {
                if ($stations[$name][0] > $values[1])
                    $stations[$name][0] = $values[1];
                elseif ($stations[$name][1] < $values[1])
                    $stations[$name][1] = $values[1];
            }
        }
    }
    $str = "";
    ksort($stations);
    foreach ($stations as $name=>$values) {
        $med = round(($values[0] + $values[1]) / 2, 2);
        $str .= $name. ': '. $values[0] . '/'. $med . '/' . $values[1]. ';';
    }
    print $str . PHP_EOL;
    print "Hash string: ". md5($str). PHP_EOL;
}
///////////////////////////////
print "Execute time is: ". (time() - $time) . PHP_EOL;
print "Memory usage in MB: ".(memory_get_usage() / (1024 * 1024)).PHP_EOL;
print date('c', time()).PHP_EOL;