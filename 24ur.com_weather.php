<?php

require 'DynFetcher.class.php';

$URL = 'http://24ur.com/';

// XPath expression of items
$itemXPath = '//div[@id="weather"]/div[@class="content"]/div[@class="day"]/div[1]';

// Associative array, where key is name of data and value is associative array with the following keys:
// -xpath (required): XPath expression of data, relative from item
// -name:     if item name equals *, name can be dynamically specified with this XPath expression
// -required: if item data is required and xpath returns empty set, item is skipped
// -process:  PHP code, for additional processing of data
//            data is passed as $data variable by reference
//            if code returns false item is skipped
$itemData = array(
    '*'        => array('xpath'    => '.',
                        'name'     => '@class',
                        'required' => true,
                        'process'  => '$data = trim($data);'),
    'date'     => array('xpath'    => 'span'),
    'tempText' => array('xpath'    => 'following-sibling::div[@class="icon"]/a/img/@alt',
                        'required' => true),
    'img'      => array('xpath'    => 'following-sibling::div[@class="icon"]/a/img/@src',
                        'process'  => '$data = "http://24ur.com" . $data;'),
);



// PHP code, for additionl processing of item after all items have been processed
// item is passes as $item variable by reference
// if code returns false item is skipped
$itemProcessFunction = '
    $tempText  = explode(" | ", $item["tempText"]);
    $item["min"] = (string)((int)$tempText[0]);
    $item["max"] = (string)((int)$tempText[1]);
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