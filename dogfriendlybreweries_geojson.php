<?php
// kudos to http://stackoverflow.com/a/18106727/1778785 for snippet of PHP to read Google spreadsheet as CSV
//all tabs in dog spreadsheet
//$googleSpreadsheetUrl = "https://docs.google.com/spreadsheets/d/1autJJNR6y4HcZfIiFEqtfOTS2T6pIozExLZ6wgyNnUQ/pub?output=csv";

//dog parks
// $googleSpreadsheetUrl = "https://docs.google.com/spreadsheets/d/1autJJNR6y4HcZfIiFEqtfOTS2T6pIozExLZ6wgyNnUQ/pub?gid=0&single=true&output=csv";
//dog shops and services
// $googleSpreadsheetUrl = "https://docs.google.com/spreadsheets/d/1autJJNR6y4HcZfIiFEqtfOTS2T6pIozExLZ6wgyNnUQ/pub?gid=16195898&single=true&output=csv";
//dog friendly shops
//$googleSpreadsheetUrl = "https://docs.google.com/spreadsheets/d/1autJJNR6y4HcZfIiFEqtfOTS2T6pIozExLZ6wgyNnUQ/pub?gid=630384392&single=true&output=csv";
//dog friendly breweries
$googleSpreadsheetUrl = "https://docs.google.com/spreadsheets/d/1autJJNR6y4HcZfIiFEqtfOTS2T6pIozExLZ6wgyNnUQ/pub?gid=421615217&single=true&output=csv";

$dogData = $_GET["mapURL"];

$rowCount = 0;
$features = array();
$error = FALSE;
$output = array();

// attempt to set the socket timeout, if it fails then echo an error
if ( ! ini_set('default_socket_timeout', 15))
{
  $output = array('error' => 'Unable to Change PHP Socket Timeout');
  $error = TRUE;
} // end if, set socket timeout

// if the opening the CSV file handler does not fail
if ( !$error && (($handle = fopen($googleSpreadsheetUrl, "r")) !== FALSE) )
{
  // while CSV has data, read up to 10000 rows
  while (($csvRow = fgetcsv($handle, 10000, ",")) !== FALSE)
  {
    $rowCount++;

    if ($rowCount == 1) { continue; } // skip the first/header row of the CSV

    $features[] = array(
      'type'     => 'Feature',
      'geometry' => array(
        'type'   => 'Point',
        'coordinates' => array(
          (float) $csvRow[1], // longitude, casted to type float
          (float) $csvRow[0]  // latitude, casted to type float
        )
      ),
      'properties' => array(
        'title' => $csvRow[2],
        'notes' => $csvRow[3],
        'property3' => $csvRow[4]
      )
    );
  } // end while, loop through CSV data

  fclose($handle); // close the CSV file handler

  $output = array(
    'type' => 'FeatureCollection',
    'features' => $features
  );
}  // end if, read file handler opened

// else, file didn't open for reading
else
{
  $output = array('error' => 'Problem Reading Google CSV');
}  // end else, file open fail

// convert the PHP output array to JSON "pretty" format
$jsonOutput = json_encode($output, JSON_PRETTY_PRINT);

// render JSON and no cache headers
header('Content-type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Access-Control-Allow-Origin: *');

print $jsonOutput;
// print $dogData;
