<?php

require 'DynFetcher.class.php';

$itemXPath = '/html/body/table/tr[3]/td/center/table/tr[2]/td/div/table/tr/td[2]/div/div[div/span/img[@alt="Loto"]]';

$dyn = new DynFetcher('http://www.loterija.si/');
$data = array('round'     => '',
              'date'      => '',
              'numbers'   => array(),
              'aditional' => '',
              'lotko'     => '');

foreach ($dyn->find($itemXPath . '/div[@class="game-header"]', array('round' => array('xpath' => 'span', 'required' => true))) as $num) {
    preg_match('/.* (\d*)\..* - (.*)$/', $num['round'], $parts);
    $data['round'] = $parts[1];
    $data['date']  = $parts[2];
}

foreach ($dyn->find($itemXPath . '/div[2]/table/tr/td[@class="loto-table"]', array('num' => array('xpath' => 'div', 'required' => true))) as $num) {
    $data['numbers'][] = $num['num'];
}


foreach ($dyn->find($itemXPath . '/div[2]/table/tr/td[@class="loto-table-dodatna"]', array('aditional' => array('xpath' => 'div', 'required' => true))) as $num) {
     $data['aditional'] = $num['aditional'];
}


foreach ($dyn->find($itemXPath . '/div[2]/table/tr/td[@class="lotko-table"]', array('num' => array('xpath' => 'div', 'required' => true))) as $num) {
    $data['lotko'] .= $num['num'];
}


header('Content-type: text/plain');


if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($data);
} else {
    var_dump($data);
}

?>