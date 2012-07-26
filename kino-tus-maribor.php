<?php

// requires PHP 5.3

require 'XMLDynFetcher.class.php';

$dyn = new XMLDynFetcher('http://www.planet-tus.si/xml/spored.xml');

$itemXPath = sprintf('//dan[@datum="%s"]/spored[@kino="%s"]/film',
                date('Y-m-d'), 'Planet Tuš Maribor');

$itemMapping = array(
    'id'        => array('xpath' => '@id'),
    'naslov'    => array('xpath' => 'naslov_si'),
    'naslov_en' => array('xpath' => 'naslov_en'),
    'cover'     => array('xpath' => 'cover'),
    'link'      => array('xpath' => 'link'),
    'stevilo_predstav' => array(
        'xpath'   => 'predstava/ura',
        'raw'     => true,
        'process' => function(&$data) {
            $data = count($data);
        }
    ),
    'ure' => array(
        'xpath'   => 'predstava/ura',
        'raw'     => true,
        'process' => function(&$data) {
            $data = array_map(function($ura){
                return array(
                    'ura'  => (string)$ura,
                    'cena' => (string)$ura['cena_eur']
                );
            }, $data);
        }
    ),
    'zvrst' => array(
        'xpath'   => 'zvrst',
        'process' => function(&$data) {
            $data = array_map(function($i){
                return trim($i);
            }, explode(",", $data));
        }
    )
);

$filmi = $dyn->find($itemXPath, $itemMapping);

if (!isset($_SERVER['HTTP_HOST'])) { // Console...
    print_r($filmi);
}
