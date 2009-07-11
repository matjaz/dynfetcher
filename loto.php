<?php

require 'DynFetcher.class.php';

$dyn = new DynFetcher('http://www.loterija.si/LOTERIJA,,igre_z_zrebanji,loto,loto_rezultati.htm');
$data = array('round'     => 0,
              'date'      => '',
              'numbers'   => array(),
              'additional' => 0,
              'plus_numbers'   => array(),
              'plus_additional' => 0,
              'lotko'     => '');

foreach ($dyn->find('//span[@class="game-info"]', array('round' => array('xpath' => '.', 'required' => true))) as $num) {
    preg_match('/(\d*)\..*, (.*)$/', $num['round'], $parts);
    $data['round'] = (int)$parts[1];
    $data['date']  = $parts[2];
}


foreach ($dyn->find('//div[@id="REZLOTO"]/table[1]//table[@class="tabela-mala"]/tr/td[@class="rdeca"]', array('num' => array('xpath' => '.', 'required' => true))) as $num) {
    $data['numbers'][] = (int)$num['num'];
}

foreach ($dyn->find('//div[@id="REZLOTO"]/table[1]//table[@class="tabela-mala"]/tr/td[@class="zelena"]', array('additional' => array('xpath' => '.', 'required' => true))) as $num) {
     $data['additional'] = (int)$num['additional'];
}



foreach ($dyn->find('//div[@id="REZLOTO"]/table[3]//table[@class="tabela-mala"]/tr/td[@class="zelena"]', array('num' => array('xpath' => '.', 'required' => true))) as $num) {
    $data['plus_numbers'][] = (int)$num['num'];
}

foreach ($dyn->find('//div[@id="REZLOTO"]/table[3]//table[@class="tabela-mala"]/tr/td[@class="rdeca"]', array('additional' => array('xpath' => '.', 'required' => true))) as $num) {
     $data['plus_additional'] = (int)$num['additional'];
}


foreach ($dyn->find('//div[@id="REZLOTO"]/table[2]//table[@class="tabela-mala"]/tr/td', array('num' => array('xpath' => '.', 'required' => true))) as $num) {
    $data['lotko'] .= (int)$num['num'];
}


header('Content-type: text/plain');


if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($data);
} else {
    var_dump($data);
}

?>