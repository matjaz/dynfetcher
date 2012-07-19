<?php

require 'DynFetcher.class.php';

$URL = 'http://www.elektro-maribor.si/sl/inside.cp2?cid=4BEAE43C-90CE-CDD5-412C-A32F8F36F5B3&linkid=announcementList'; 

// XPath expression of items
$itemXPath = '//ul[@id="announces"]/li';

// Associative array, where key is name of data and value is associative array with the following keys:
// -xpath (required): XPath expression of data, relative from item
// -required: true or false
// -process:  PHP code, for additional processing of data
//            data is passed as $data variable by reference
//            if code returns false data is skipped
$itemData = array(
    'classes' => array('xpath' => '@class',                     'required' => true),
    'area'    => array('xpath' => 'p[1]',                       'required' => true),
    'date'    => array('xpath' => '//span[@class="startDate"]', 'required' => true),
    'station' => array('xpath' => 'p[3]',                       'required' => true),
    'reason'  => array('xpath' => '.',                          'process'  => '$data = trim($data);'),
);

$i = 1;

// PHP code, for additionl processing of item after all items have been processed
// item is passes as $item variable by reference
// if code returns false item is skipped
$itemProcessFunction = '

    if ($GLOBALS["i"] > 5) return false; 

    $pos = strpos($item["area"], "je: ");
    if ($pos !== false) {
        $item["area"] = substr($item["area"], $pos + 4);
    }

    $split = explode(" ", $item["classes"]);
    $item["areaID"]  = $split[0];
    $item["ISOdate"] = substr($split[1], 1);
    unset($item["classes"]);

    $pos = strpos($item["station"], ", nizkonapetostni izvod: ");
    if ($pos !== false) {
        $item["izvod"]   = substr($item["station"],    $pos + 25);
        $item["station"] = substr($item["station"], 3, $pos - 3);
    } else {
        $item["station"] = substr($item["station"], 3);
    }

    $GLOBALS["i"]++;

    // return false; // skip item
';

header('Content-type: text/plain');

$dyn = new DynFetcher($URL);

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($dyn->find($itemXPath, $itemData, $itemProcessFunction));
} else {
    print_r($dyn->find($itemXPath, $itemData, $itemProcessFunction));
}

?>
