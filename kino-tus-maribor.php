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

$itemXPath = sprintf('//dan[@datum="%s"]/spored[@kino="%s"]/film',date("Y-m-d", strtotime("now")),"Planet Tuš Maribor");
$itemMapping = array(
	'naslov'	=> array('xpath' => 'naslov_si'),
	'cover'		=> array('xpath' => 'cover'),
	'link'		=> array('xpath' => 'link')
);


print_r($dyn->find($itemXPath,$itemMapping,''));