<?php
// Database configuration
// Using HLStatsx native Variables
$host = DB_ADDR;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

// Declare vars
$amcharts_rank_get = 0;
$amcharts_rank_display = "";
$amcharts__rank_count = 0;

// Which map are we displaying?

switch ($amcharts_maptype) {
    case "top":
        $amcharts_player_query = "SELECT playerId,skill,lastName,lat,lng FROM `hlstats_Players` WHERE (lat IS NOT NULL && game = '$amcharts_mapgame' && !hideranking) ORDER BY skill DESC LIMIT 50";
        $amcharts_title = "Top Players";
        $amcharts_rank_get = 1;
        break;

    case "active":
        $amcharts_player_query = "SELECT player_Id AS playerId,skill,`Name` AS lastName,cli_lat AS lat,cli_lng AS lng FROM `hlstats_Livestats` WHERE (cli_lng IS NOT NULL)";
        $amcharts_title = "Active Players";
        break;
    
    case "recent":
        $amcharts_player_query = "SELECT playerId,skill,lastName,lat,lng FROM `hlstats_Players` WHERE (lat IS NOT NULL && game = '$amcharts_mapgame' && !hideranking) ORDER BY last_event DESC LIMIT 20";
        $amcharts_title = "Recent Players";
        break;

    case "random":
    default:
        $amcharts_player_query = "SELECT lastName,lat,lng FROM `hlstats_Players` WHERE lat IS NOT NULL ORDER BY RAND() LIMIT 20";
        $amcharts_title = "Ten Random Players";
        break;
    }

    $amcharts_region_query = "SELECT `value` FROM `hlstats_Options` WHERE `keyname` = 'google_map_region'";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare an array to hold the JSON objects
    $result = [];
    $item1List = [];

        // Query to fetch map region from admin details
        $singleRowSql = $amcharts_region_query;
        $singleRowStmt = $pdo->query($singleRowSql);
        $singleRow = $singleRowStmt->fetch(PDO::FETCH_ASSOC);
        
        $map_center = region_to_lat_lon($singleRow['value']);

        $server_lat = $map_center['lat'];
        $server_lng = $map_center['lng'];
        $server_zoom = $map_center['zoom'];


    // Query to fetch data
    $sql = $amcharts_player_query;
    $stmt = $pdo->query($sql);

    // Fetch data row by row
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $amcharts__rank_count ++;
        
        if ($amcharts_rank_get) {
            $amcharts_rank_display = "Rank: #" . $amcharts__rank_count . '<br>';
        }
      
        if (array_key_exists('skill',$row) && array_key_exists('playerId',$row)) {
            $player_skill = $row['skill'];
            $player_id = $row['playerId'];
        }else{
            $player_skill = 0;
            $player_id = 1;
        }

        $result[] = [
            'id' => $row['lastName'],
            'title' => '<center>' . $row['lastName'] . '<br><small>' . $amcharts_rank_display . 'Points: ' . $player_skill . '<br>(Click me!)</small></center>',
            'url' => '../../hlstats.php?mode=playerinfo&player=' . $player_id , 
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    " ". floatval(round($row['lng'],2)), // Convert to float for proper JSON formatting
                    " ". floatval(round($row['lat'],2))
                ]
            ]
        ];
        $item1List[] = $row['lastName'];
    }

    // Encode the result as a JSON string
    // echo "JSON with full data:\n";
    // echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	$json_player_details = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // echo "\n\nJSON with item1 list:\n";
    // echo json_encode($item1List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $json_player_names = json_encode($item1List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


function region_to_lat_lon($region) {

    switch ($region) {
        case 'ARGENTINA':
            $array['lat'] = -38.4161;
            $array['lng'] = -63.6167;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'ASIA':
            $array['lat'] = 34.0479;
            $array['lng'] = 100.6197;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'AUSTRALIA':
            $array['lat'] = -25.2744;
            $array['lng'] = 133.7751;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'AUSTRIA':
            $array['lat'] = 47.5162;
            $array['lng'] = 14.5501;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'BELGIUM':
            $array['lat'] = 50.8503;
            $array['lng'] = 4.3517;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'BRAZIL':
            $array['lat'] = -14.2350;
            $array['lng'] = -51.9253;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'CHINA':
            $array['lat'] = 35.8617;
            $array['lng'] = 104.1954;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'DENMARK':
            $array['lat'] = 56.2639;
            $array['lng'] = 9.5018;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'EAST EUROPE':
            $array['lat'] = 55.3781;
            $array['lng'] = 37.6173;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'EUROPE':
            $array['lat'] = 54.5260;
            $array['lng'] = 15.2551;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'FINLAND':
            $array['lat'] = 61.9241;
            $array['lng'] = 25.7482;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'FRANCE':
            $array['lat'] = 46.6034;
            $array['lng'] = 1.8883;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'GERMANY':
            $array['lat'] = 51.1657;
            $array['lng'] = 10.4515;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'ITALY':
            $array['lat'] = 41.8719;
            $array['lng'] = 12.5674;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'JAPAN':
            $array['lat'] = 36.2048;
            $array['lng'] = 138.2529;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'NETHERLANDS':
            $array['lat'] = 52.1326;
            $array['lng'] = 5.2913;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'NORTH AFRICA':
            $array['lat'] = 26.8206;
            $array['lng'] = 30.8025;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'NORTH AMERICA':
            $array['lat'] = 54.5260;
            $array['lng'] = -105.2551;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'NORTH EUROPE':
            $array['lat'] = 60.4720;
            $array['lng'] = 8.4689;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'NORWAY':
            $array['lat'] = 60.4720;
            $array['lng'] = 8.4689;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'POLAND':
            $array['lat'] = 51.9194;
            $array['lng'] = 19.1451;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'ROMANIA':
            $array['lat'] = 45.9432;
            $array['lng'] = 24.9668;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'RUSSIA':
            $array['lat'] = 61.5240;
            $array['lng'] = 105.3188;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'SOUTH AFRICA':
            $array['lat'] = -30.5595;
            $array['lng'] = 22.9375;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'SOUTH AMERICA':
            $array['lat'] = -14.2350;
            $array['lng'] = -51.9253;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'SOUTH KOREA':
            $array['lat'] = 35.9078;
            $array['lng'] = 127.7669;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'SPAIN':
            $array['lat'] = 40.4637;
            $array['lng'] = -3.7492;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'SUISSE':
            $array['lat'] = 46.8182;
            $array['lng'] = 8.2275;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'SWEDEN':
            $array['lat'] = 60.1282;
            $array['lng'] = 18.6435;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'TAIWAN':
            $array['lat'] = 23.6978;
            $array['lng'] = 120.9605;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'TURKEY':
            $array['lat'] = 38.9637;
            $array['lng'] = 35.2433;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'UNITED KINGDOM':
            $array['lat'] = 55.3781;
            $array['lng'] = -3.4360;
            $array['zoom'] = 8;
            return $array;
            break;
        case 'WORLD':
            $array['lat'] = 0;
            $array['lng'] = 0;
            $array['zoom'] = 8;
            return $array;
            break;
        default:
            // Handle case where region does not match any case
            $array['lat'] = null;
            $array['lng'] = null;
            $array['zoom'] = null;
            return $array;
            break;
    }
    
}

?>
