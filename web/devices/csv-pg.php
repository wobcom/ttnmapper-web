<?php
// http://ttnmapper.org/device/csv.php?device=oyster&startdate=2019-09-21&enddate=2019-09-21&gateways=on&gateways=on&gateways=on

/*
id, time, nodeaddr, appeui, gwaddr, modulation, datarate, snr, rssi, freq, fcount, lat, lon, alt, accuracy, hdop, sats, provider, user_agent
298682898, 2021-03-31 20:07:23, cricket_001, jpm_crickets, 647FDAFFFE007A1F, LORA, SF7BW125, 10.50, -42.00, 868.300, 15306, -33.936700, 18.871000, 109.8, 0.00, 0.0, 0, Cayenne LPP, http-ttnmapper/2.7.1
298682897, 2021-03-31 20:07:23, cricket_001, jpm_crickets, 60C5A8FFFE71A964, LORA, SF7BW125, 10.00, -79.00, 868.300, 15306, -33.936700, 18.871000, 109.8, 0.00, 0.0, 0, Cayenne LPP, http-ttnmapper/2.7.1
*/

$settings = parse_ini_file(getenv("TTNMAPPER_HOME")."/settings.conf",true);

$username   = $settings['database_postgresql']['username'];
$password   = $settings['database_postgresql']['password'];
$dbname     = $settings['database_postgresql']['database'];
$servername = $settings['database_postgresql']['host'];
$serverport = $settings['database_postgresql']['port'];


if(!isset($_REQUEST["device"])) {
  echo "No device ID specified.";
  die();
}


$device = urldecode($_REQUEST["device"]);
$startdate = 0;
$enddate = time();


if(!isset($_REQUEST["startdate"]) or $_REQUEST["startdate"]=="") {
  $startdate = 0; // 1970-01-01
}
else {
  $startdate = strtotime($_REQUEST["startdate"]);
  if($enddate === false) {
    echo '{"error": true, "error_message": "Could not parse startdate"}';
    die();
  }
}


if(!isset($_REQUEST["enddate"]) or $_REQUEST["enddate"]=="") {
  // End of today's server time
  $enddate = strtotime("today") + 24*60*60;
}
else {
  $enddate = strtotime($_REQUEST["enddate"]);
  if($enddate === false) {
    echo '{"error": true, "error_message": "Could not parse enddate"}';
    die();
  }

  // When only a date is given the time part will be set to all 0's in the parsed timestamp.
  // Increment by one day to include the speicified day's data.
  $date = new DateTime();
  $date->setTimestamp($enddate);

  // If someone types a date with the time part set to all 0, we will select an extra day. 
  // The chance of this happening is small enough for us to accept this bug.
  // We handle the case with Y-m-d and Ymd later on.
  if( $date->format('H') == "00" 
    and $date->format('i') == "00" 
    and $date->format('s') == "00") {

    // If the time part was not specified as 0
    if (strpos($_REQUEST["enddate"], '00:00:00') === false 
      and strpos($_REQUEST["enddate"], '00:00') === false )
    {
      // Include this day's data
      $enddate = $enddate + 24*60*60;
    }
  }
}


try {

  $startDateObj = new DateTime();
  $startDateObj->setTimestamp($startdate);

  $endDateObj = new DateTime();
  $endDateObj->setTimestamp($enddate);

  $startDateStr = $startDateObj->format('Y-m-d H:i:s');
  $endDateStr = $endDateObj->format('Y-m-d H:i:s');

} catch  (Exception $e) {
  echo '{"error": true, "error_message": "Could not parse datetime"}';
  die();
}



header("Content-Type: text/plain");
$columns = [];

  try {
    print "id, time, device_id, application_id, gateway_id, modulation, datarate, snr, rssi, freq, f_cnt, latitude, longitude, altitude, accuracy meters, hdop, sats, location provider, user_agent, experiment name\n";
    

  $conStr = sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s", 
        $servername, 
        $serverport,
        $dbname,
        $username,
        $password);

  $conn = new PDO($conStr);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Run data query
  $query = <<<SQL
SELECT packets.id, time, dev_id, app_id, gateway_id, modulation,
'SF' || dr.spreading_factor || 'BW' || dr.bandwidth/1000 as datarate,
snr, rssi, round(herz/1000000.0, 3) as freq, f_cnt,
latitude, longitude, altitude, accuracy_meters, hdop, satellites, "as".name, ua.name, e.name
FROM packets
JOIN antennas a on packets.antenna_id = a.id
JOIN devices d on packets.device_id = d.id
JOIN data_rates dr on packets.data_rate_id = dr.id
JOIN frequencies f on packets.frequency_id = f.id
JOIN accuracy_sources "as" on packets.accuracy_source_id = "as".id
JOIN user_agents ua on packets.user_agent_id = ua.id
LEFT JOIN experiments e on packets.experiment_id = e.id
-- WHERE (a.network_id = 'NS_TTS_V3://eu1.cloud.thethings.network' OR a.network_id = 'NS_TTS_V3://ttn.eu1.cloud.thethings.network')
WHERE d.dev_id = :device
-- AND latitude != 0.0 AND longitude != 0.0
AND time > :startdate
AND time < :enddate
-- AND packets.experiment_id IS NULL
ORDER BY time DESC LIMIT 10000
SQL;

  $stmt = $conn->prepare($query);
  $stmt->bindParam(':device', $device, PDO::PARAM_STR);
  $stmt->bindParam(':startdate', $startDateStr, PDO::PARAM_STR);
  $stmt->bindParam(':enddate', $endDateStr, PDO::PARAM_STR);
  $stmt->execute();

  $stmt->setFetchMode(PDO::FETCH_ASSOC); 

    $number_of_rows = $stmt->rowCount();
    $points = array();
    
    foreach($stmt->fetchAll() as $lineNr=>$row) { 
      $i = 0;
      foreach ($row as $key => $value) {
        $point[$key] = $value;
        print($value);
        print(",");
      }
      print "\n";
    }
    print "\nNumber of rows dumped: ".$number_of_rows;
  }
  catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    echo "An error occured running the query.";
  }
?>