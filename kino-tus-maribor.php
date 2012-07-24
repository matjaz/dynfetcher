<?php

require 'DynFetcher.class.php';

class CustomDynFetcher extends DynFetcher
{
	public function fetch()
	{
		if (!$xml_raw = @file_get_contents($this->url)) {
			throw new Exception("Missing content at url: {$this->url}");
		}
		return simplexml_load_string($xml_raw,
			'SimpleXMLElement', LIBXML_NOCDATA);
	}
}

$dyn = new CustomDynFetcher('http://www.planet-tus.si/xml/spored.xml');

$itemXPath = sprintf('//dan[@datum="%s"]/spored[@kino="%s"]/film',
				date('Y-m-d'), 'Planet Tuš Maribor');

$itemMapping = array(
	'id'		=> array('xpath' => '@id'),
	'naslov'	=> array('xpath' => 'naslov_si'),
	'naslov_en'	=> array('xpath' => 'naslov_en'),
	'cover'		=> array('xpath' => 'cover'),
	'link'		=> array('xpath' => 'link'),
	'stevilo_predstav' => array(
		'xpath'   => 'predstava/ura',
		'raw'     => true,
		'process' => '$data = count($data);',
	),
	'ure' => array(
		'xpath'   => 'predstava/ura',
		'raw'     => true,
		'process' => '
			$data = array_map(function($ura){
				return array(
					"ura" => (string)$ura,
					"cena" => (string)$ura["cena_eur"]
				);
			}, $data);
		',
	),
	'zvrst' => array(
		'xpath'   => 'zvrst',
		'process' => '
			$data = array_map(function($i){
				return trim($i);
			}, explode(",", $data));
		'
	)
);

$filmi = $dyn->find($itemXPath, $itemMapping);

if (!isset($_SERVER['HTTP_HOST'])) { // Console...
	print_r($filmi);
}
