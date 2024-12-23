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

// echo ">" .$amcharts_maptype ."<";

switch ($amcharts_maptype) {
    case "top":
        $amcharts_server_query = "SELECT name,lat,lng FROM `hlstats_Servers` WHERE lat IS NOT NULL LIMIT 1";
        $amcharts_player_query = "SELECT playerId,skill,lastName,lat,lng FROM `hlstats_Players` WHERE (lat IS NOT NULL && game = '$amcharts_mapgame' && !hideranking) ORDER BY skill DESC LIMIT 50";
        $amcharts_title = "Top Players";
        $amcharts_rank_get = 1;
        break;

    case "active":
        $amcharts_server_query = "SELECT name,lat,lng FROM `hlstats_Servers` WHERE (lat IS NOT NULL && game = '$amcharts_mapgame') LIMIT 1";
        $amcharts_player_query = "SELECT player_Id AS playerId,skill,`Name` AS lastName,cli_lat AS lat,cli_lng AS lng FROM `hlstats_Livestats` WHERE (cli_lng IS NOT NULL)";
        $amcharts_title = "Active Players";
        break;
    
    case "recent":
        $amcharts_server_query = "SELECT name,lat,lng FROM `hlstats_Servers` WHERE lat IS NOT NULL LIMIT 1";
        $amcharts_player_query = "SELECT playerId,skill,lastName,lat,lng FROM `hlstats_Players` WHERE (lat IS NOT NULL && game = '$amcharts_mapgame' && !hideranking) ORDER BY last_event DESC LIMIT 20";
        $amcharts_title = "Recent Players";
        break;

    case "random":
    default:
        $amcharts_server_query = "SELECT name,lat,lng FROM `hlstats_Servers` WHERE lat IS NOT NULL LIMIT 1";
        $amcharts_player_query = "SELECT lastName,lat,lng FROM `hlstats_Players` WHERE lat IS NOT NULL ORDER BY RAND() LIMIT 20";
        $amcharts_title = "Ten Random Players";
        break;
    }


try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare an array to hold the JSON objects
    $result = [];
    $item1List = [];

        // Query to fetch server details
        $singleRowSql = $amcharts_server_query;
        $singleRowStmt = $pdo->query($singleRowSql);
        $singleRow = $singleRowStmt->fetch(PDO::FETCH_ASSOC);


        if ($singleRow) {
            // Add the server row data to the start of $result
            $result[] = [
                'id' => $singleRow['name'],
                'title' => $singleRow['name'],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        floatval($singleRow['lng']), // Convert to float for proper JSON formatting
                        floatval($singleRow['lat'])
                    ]
                ]
            ];

            $server_lng = $singleRow['lng'];
            $server_lat = $singleRow['lat'];
        
        } else {
            // Always return something
            // But if geo lookup has failed for server
            // it's likely failed for players as well
            $result[] = [
                'id' => 'Error Getting Server Location',
                'title' => 'Error Getting Server Location',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        0, 0
                    ]
                ]
            ];

            $server_lng = 0;
            $server_lat = 0;

        }

    // Query to fetch data
    $sql = $amcharts_player_query;
    $stmt = $pdo->query($sql);

    // Fetch data row by row
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $amcharts__rank_count ++;
        
        if ($amcharts_rank_get) {
            $amcharts_rank_display = "Rank: #" . $amcharts__rank_count . '<br>';
        }
      
        
        $result[] = [
            'id' => $row['lastName'],
            'title' => '<center>' . $row['lastName'] . '<br><small>' . $amcharts_rank_display . 'Points: ' . $row['skill'] . '<br>(Click me!)</small></center>',
            'url' => '/hlstats.php?mode=playerinfo&player=' . $row['playerId'] , 
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    floatval(round($row['lng'],2)), // Convert to float for proper JSON formatting
                    floatval(round($row['lat'],2))
                ]
            ]
        ];
        $item1List[] = $row['lastName'];
    }

    // Encode the result as a JSON string
    // echo "JSON with full data:\n";
    // echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	$ten_player_details = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // echo "\n\nJSON with item1 list:\n";
    // echo json_encode($item1List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $ten_player_names = json_encode($item1List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
