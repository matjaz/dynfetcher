<?php

require 'DynFetcher.class.php';

$URL = 'http://www.arso.gov.si/';

header('Content-type: text/plain');

$dyn = new DynFetcher($URL);

$weather = $dyn->find('/html/body/table[2]/tr/td[2]/div[2]/table/tr/th[@colspan="2"]', array(
    'day' => array('xpath' => '.', 'required' => true)
));

foreach($dyn->find('/html/body/table[2]/tr/td[2]/div[2]/table/tr[3]/td[not(@class)]',
					array('tempText' => array('xpath' => '.', 'required' => true)),
'
    $tempText    = explode(" / ", $item["tempText"]);
    $item["min"] = $tempText[0];
    $item["max"] = $tempText[1];
    unset($item["tempText"]);
') as $key => $data) {
    $weather[$key] = array_merge($weather[$key], $data);
}

$i = 0;
foreach($dyn->find('/html/body/table[2]/tr/td[2]/div[2]/table/tr[2]/td[not(@class) or @class="pojav"]', array(
    'typeIMG'  => array('xpath' => 'img/@src',      'process'  => '$data = "http://www.arso.gov.si" . $data;'),
    'typeText' => array('xpath' => 'img/@alt',      'process'  => '$data = str_replace("er:", "er", str_replace("pojav: ", "", $data));'),
    'weatherIMG'  => array('xpath' => 'a/img/@src', 'process'  => '$data = "http://www.arso.gov.si" . $data;'),
    'weatherText' => array('xpath' => 'a/img/@alt')
)) as $data) {
    $weather[$i] = array_merge($weather[$i], $data);
	if (isset($data['weatherIMG'])) {
		$i++;
	}
}

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($weather);
} else {
    var_dump($weather);
}

?>