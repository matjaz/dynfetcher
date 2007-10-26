<?php

require 'DynFetcher.class.php';

$URL = 'http://24ur.com/';

// XPath expression of items
$itemXPath = '/html/body/table[2]/tr/td/table/tr/td[2]/table/tr/td[2]/div/table/tr/td[a]';

// Associative array, where key is name of data and value is associative array with the following keys:
// -xpath (required): XPath expression of data, relative from item
// -required: true or false
// -process:  PHP code, for additional processing of data
//            data is passed as $data variable by reference
//            if code returns false data is skipped
$itemData = array(
    'day'      => array('xpath' => 'span[@class="vreme_dan"]', 'required' => true),
    'tempText' => array('xpath' => 'a/img/@alt',               'required' => true),
    'img'      => array('xpath' => 'a/img/@src',               'process'  => '$data = "http://24ur.com" . $data;'),
);



// PHP code, for additionl processing of item after all items have been processed
// item is passes as $item variable by reference
// if code returns false item is skipped
$itemProcessFunction = '
    $tempText  = explode(" - ", $item["tempText"]);
    $item["min"] = $tempText[0];
    $item["max"] = $tempText[1];
    unset($item["tempText"]);
    
    // return false; // skip item
';





header('Content-type: text/plain');

$dyn = new DynFetcher($URL);

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($dyn->find($itemXPath, $itemData, $itemProcessFunction));
} else {
    var_dump($dyn->find($itemXPath, $itemData, $itemProcessFunction));
}

?>