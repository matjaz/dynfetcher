<?

date_default_timezone_set('Europe/Ljubljana');

require_once 'DynFetcher.class.php';

class CustomDynFetcher extends DynFetcher{
	function fetch(){
		if(! $xml_raw = @file_get_contents($this->url) )
			throw new Exception(sprintf("Missing content at url: %s ",$this->url));
		
		return $this->simpleXML = simplexml_load_string($xml_raw,
			'SimpleXMLElement', LIBXML_NOCDATA);
	}
}

$dyn = new CustomDynFetcher('http://www.planet-tus.si/xml/spored.xml');

$itemXPath = sprintf('//dan[@datum="%s"]/spored[@kino="%s"]/film',
	date("Y-m-d", strtotime("now")),"Planet Tuš Maribor");

$itemMapping = array(
	'id'		=> array('xpath' => '@id'),
	'naslov'	=> array('xpath' => 'naslov_si'),
	'naslov_en'	=> array('xpath' => 'naslov_en'),
	'cover'		=> array('xpath' => 'cover'),
	'link'		=> array('xpath' => 'link'),
	'stevilo_predstav' => array('xpath' => 'predstava/ura',
		'process' => '$data = count($data);',
		'skip_to_string' => true,
	),
	'ure' => array('xpath' => 'predstava/ura',
		'process' => '
			$out = array();
			foreach($data as $ura) $out[]=array(
				"ura" => (string)$ura, "cena" => (string)$ura["cena_eur"]);
			$data = $out;
		',
		'skip_to_string' => true,
	),
	'zvrst'		=> array('xpath' => 'zvrst','process' => '
		$data = array_map(function($i){
			return trim($i);
		},explode(",",$data));
	')
);

$filmi = $dyn->find($itemXPath,$itemMapping,'');

if(!$_SERVER["HTTP_HOST"]){ // Console...
	print_r($filmi );
};