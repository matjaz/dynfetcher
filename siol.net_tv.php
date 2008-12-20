<?php

require 'DynFetcher.class.php';

/*
$chn = 'SLO1';
$chn = 'SLO2';
$chn = 'POPTV';
$chn = 'AKANAL';
$chn = 'TV3';
$chn = 'DISCOVERY';
$chn = 'HBO';
$chn = 'ANIMAL';
*/
$chn = 'POPTV';

$URL = 'http://www.siol.net/tv-spored.aspx?chn=' . $chn;

// XPath expression of items
$itemXPath = '//table[@class="schedule"]/tr';

// Associative array, where key is name of data and value is associative array with the following keys:
// -xpath (required): XPath expression of data, relative from item
// -required: true or false
// -process:  PHP code, for additional processing of data
//            data is passed as $data variable by reference
//            if code returns false data is skipped
$itemData = array(
    'time' => array('xpath' => 'td[@class="time"]',
                    'required' => true),
    'text' => array('xpath' => 'td[@class="prog"]/div/a',
                    'required' => true),
    'link' => array('xpath' => 'td[@class="prog"]/div/a/@href',
                    'process' => '$data = "http://siol.net/" . $data;'),
    'desc' => array('xpath' => 'td[@class="prog"]/div/a/@onmouseover')
);



// PHP code, for additionl processing of item after all items have been processed
// item is passes as $item variable by reference
// if code returns false item is skipped
$itemProcessFunction = '
	if (isset($item["desc"])) {
		$item["desc"] = substr($item["desc"], 11, -2);
	}
';





header('Content-type: text/plain');

$dyn = new DynFetcher($URL);

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($dyn->find($itemXPath, $itemData, $itemProcessFunction));
} else {
    var_dump($dyn->find($itemXPath, $itemData, $itemProcessFunction));
}

?>