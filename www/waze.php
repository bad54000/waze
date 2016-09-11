<?php

function checkparam($argc, $argv) {
    if (!isset($_SERVER['PWD'])) {
        die('cli only');
    }

    if ($argc < 2) {
        $argv = array_merge($argv, getAllTracks());
    }

    unset($argv[0]);
    $_tracks = array();
    foreach ($argv as &$track) {
        $t = loadconfig($track);

        if ($t === false) {
            die('No track for '.$track);
        }
        $t['short'] = $track;

        $_tracks[] = $t;
    }
    return $_tracks;
}

function loadconfig($track) {
    $config = yaml_parse_file(dirname(__FILE__).'/tracks.yml');
    return !isset($config[$track]) ? false : $config[$track];
}

function getAllTracks() {
    $config = yaml_parse_file(dirname(__FILE__).'/tracks.yml');
    return array_keys($config);
}

// script from JDOM waze plugin
function check($dep_lo, $dep_la, $arr_lo, $arr_lat) {
    
    $route1TotalTimeMin = 'old';
    $route2TotalTimeMin = 'old';
    // $route3TotalTimeMin = 'old';
    $route1Name = 'old';
    $route2Name = 'old';
    // $route3Name = 'old';

    $row='row-';
    $wazeRouteurl =  "https://www.waze.com/".$row."RoutingManager/routingRequest?from=x%3A$dep_lo+y%3A$dep_la&to=x%3A$arr_lo+y%3A$arr_lat&at=0&returnJSON=true&returnGeometries=true&returnInstructions=true&timeout=60000&nPaths=2&options=AVOID_TRAILS%3At";

    $opts = array(
        'http'=>array(
            'method'=>"GET",
            'header'=>"Accept-language: en\r\n" .
                "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:43.0) Gecko/20100101 Firefox/44.0"
        )
    );

    $context = stream_context_create($opts);
    $routeResponseText = file_get_contents($wazeRouteurl, false, $context);

    if ($routeResponseText === FALSE) {
        echo 'Difficulté à contacter le serveur'; die;
    } else {
        $routeResponseJson = json_decode($routeResponseText,true);
        $route1Name = (isset($routeResponseJson['alternatives'][0]['response']['routeName'])) ? $routeResponseJson['alternatives'][0]['response']['routeName'] : "NA";
        $route2Name = (isset($routeResponseJson['alternatives'][1]['response']['routeName'])) ? $routeResponseJson['alternatives'][1]['response']['routeName'] : "NA";
        // $route3Name = (isset($routeResponseJson['alternatives'][2]['response']['routeName'])) ? $routeResponseJson['alternatives'][2]['response']['routeName'] : "NA";
        $route1 = (isset($routeResponseJson['alternatives'][0]['response']['results'])) ? $routeResponseJson['alternatives'][0]['response']['results'] : 0;
        $route2 = (isset($routeResponseJson['alternatives'][1]['response']['results'])) ? $routeResponseJson['alternatives'][1]['response']['results'] : 0;
        // $route3 = (isset($routeResponseJson['alternatives'][2]['response']['results'])) ? $routeResponseJson['alternatives'][2]['response']['results'] : 0;
        $route1TotalTimeSec = 0;
        if ($route1 != 0) {
            foreach ($route1 as $street){
                $route1TotalTimeSec += $street['crossTime'];
            }
        }
        $route2TotalTimeSec = 0;
        if ($route2 != 0) {
            foreach ($route2 as $street){
                $route2TotalTimeSec += $street['crossTime'];
            }
        }
        // $route3TotalTimeSec = 0;
        // if ($route3 != 0) {
        //     foreach ($route3 as $street){
        //         $route3TotalTimeSec += $street['crossTime'];
        //     }
        // }
        $route1TotalTimeMin = round($route1TotalTimeSec/60);
        $route2TotalTimeMin = round($route2TotalTimeSec/60);
        // $route3TotalTimeMin = round($route3TotalTimeSec/60);
    }

    
    return array(
        array(
            'duree' => gmdate("H:i", $route1TotalTimeSec),
            'route' => $route1Name,
        ),
        array(
            'duree' => gmdate("H:i", $route2TotalTimeSec),
            'route' => $route2Name,
        )
    );
}
$tracks = checkparam($argc, $argv);
foreach ($tracks as &$track) {
    $track['results'] = check($track['dep_lo'], $track['dep_la'], $track['arr_lo'], $track['arr_la']);
}

array_map('unlink', glob(dirname(__FILE__)."/*.txt"));
ob_start();
foreach ($tracks as $t) {
    $i = 1;

    $str = '';
    echo "<h3>".$t['name']." (".$t['short'].")</h3>\n";
    foreach ($t['results'] as $res) {
        echo "Durée : ".$res['duree']."<br />\n";
        echo "Route : ".$res['route']."<br /><br />\n";

        $str .= $res['duree'].' ('.str_replace(' ', '', $res['route']).') | ';
        $i++;
    }
    $str = substr($str, 0, -3);
    echo "Last check : ".date('d/m/Y H:i');
    file_put_contents(dirname(__FILE__).'/'.$t['short'].'.txt', $str);

}
file_put_contents(dirname(__FILE__).'/live.html', ob_get_contents());
ob_clean();
