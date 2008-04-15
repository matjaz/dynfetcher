<?php

require 'DynFetcher.class.php';

$URL = 'http://www2.24ur.com/spored/poptv_' . mktime(0, 0, 0, date('m'), date('d'), date('Y')) . '.php'; 

// XPath expression of items
$itemXPath = '//div[@id="tvspored"]/div[@class!="tv_legenda"]';

// Associative array, where key is name of data and value is associative array with the following keys:
// -xpath (required): XPath expression of data, relative from item
// -required: true or false
// -process:  PHP code, for additional processing of data
//            data is passed as $data variable by reference
//            if code returns false data is skipped
$itemData = array(
    'hour'   => array('xpath' => 'span',                 'required' => true),
    'title'  => array('xpath' => 'div/div/span/a',       'process'  => '$data = trim($data);'),
    'title1' => array('xpath' => 'div/div/span',         'process'  => '$data = trim($data);'),
    'link'   => array('xpath' => 'div/div/span/a/@href', 'process'  => '$data = "http://24ur.com" . $data;'),
    'desc'   => array('xpath' => 'div',                  'process'  => '$data = trim($data);'),
);

// PHP code, for additionl processing of item after all items have been processed
// item is passes as $item variable by reference
// if code returns false item is skipped
$itemProcessFunction = '
    if (!isset($item["title"])) {
        $item["title"] = $item["title1"];
    }
    unset($item["title1"]);
    if (empty($item["desc"])) {
        unset($item["desc"]);
    }

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
