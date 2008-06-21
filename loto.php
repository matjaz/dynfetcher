<?php

require 'DynFetcher.class.php';

$itemXPath = '/html/body/table/tr[3]/td/center/table/tr[2]/td/div/table/tr/td[2]/div/div[div/span/img[@alt="Loto"]]';

$dyn = new DynFetcher('http://www.loterija.si/');
$data = array('round'     => 0,
              'date'      => '',
              'numbers'   => array(),
              'additional' => 0,
              'plus_numbers'   => array(),
              'plus_additional' => 0,
              'lotko'     => '');

foreach ($dyn->find($itemXPath . '/div[@class="game-header"]', array('round' => array('xpath' => 'span', 'required' => true))) as $num) {
    preg_match('/.* (\d*)\..* - (.*)$/', $num['round'], $parts);
    $data['round'] = (int)$parts[1];
    $data['date']  = $parts[2];
}


foreach ($dyn->find($itemXPath . '/div[2]/table[1]/tr/td[@class="loto-table"]', array('num' => array('xpath' => 'div', 'required' => true))) as $num) {
    $data['numbers'][] = (int)$num['num'];
}

foreach ($dyn->find($itemXPath . '/div[2]/table[1]/tr/td[@class="loto-table-dodatna"]', array('additional' => array('xpath' => 'div', 'required' => true))) as $num) {
     $data['additional'] = (int)$num['additional'];
}


foreach ($dyn->find($itemXPath . '/div[2]/table[3]/tr/td[@class="loto-table"]', array('num' => array('xpath' => 'div', 'required' => true))) as $num) {
    $data['plus_numbers'][] = (int)$num['num'];
}

foreach ($dyn->find($itemXPath . '/div[2]/table[3]/tr/td[@class="loto-table-dodatna"]', array('additional' => array('xpath' => 'div', 'required' => true))) as $num) {
     $data['plus_additional'] = (int)$num['additional'];
}

foreach ($dyn->find($itemXPath . '/div[2]/table/tr/td[@class="lotko-table"]', array('num' => array('xpath' => 'div', 'required' => true))) as $num) {
    $data['lotko'] .= (int)$num['num'];
}


header('Content-type: text/plain');


if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($data);
} else {
    var_dump($data);
}

?>