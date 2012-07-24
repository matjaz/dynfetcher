<?php

/**
* Use this class under LGPL license
* @author   Matjaz Lipus <me@matjaz.info>
* @link     https://github.com/matjaz/dynfetcher
* @package  dynfetcher
* @version  1.0
*/ 
class DynFetcher
{
    protected $url;

    private $simpleXML = null;

    /**
     *
     * @param string $URL URL of page
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Fetches a website content.
     * It is called automatticaly inside of {@link #find} method.
     * @return SimpleXMLElement SimpleXML object representation of webpage.
     */
    protected function fetch()
    {
        if (is_null($this->simpleXML)) {
            $data = @file_get_contents($this->url);
            if (!is_string($data)) {
                throw new Exception('Error retrieving data');
            }
            $this->simpleXML = simplexml_import_dom(@DomDocument::loadHTML($data));
        }
        return $this->simpleXML;
    }

    /**
     * 
     * @param string $itemXPath Items XPath expression
     * @param array $itemData Array of key-values pairs of data we want to fetch
     * @param mixed $itemProcessFunction String or callable. Function for additional processing of each item (passed argument is $item). If false is returned item is skiped.
     * @return array Array of matched items
     */
    public function find($itemXPath, $itemData, $itemProcessFunction = null)
    {
        if (!is_array($itemData)) {
            throw new Exception('$itemData must be array!');
        }
        $results = $this->fetch()->xpath($itemXPath);

        //print_r($results);
       // var_dump($results);

        if (!is_array($results)) {
            throw new Exception('XPath did not return any results');
        }

        foreach ($itemData as $key => &$data) {
            if (!is_array($data)) {
                throw new Exception("$key must have array value!");
            }
            if (!isset($data['xpath'])) {
                throw new Exception("$key does not have XPath specified!");
            }
            if ($key === '*' && !isset($data['name'])) {
                throw new Exception("XPath \"{$data['xpath']}\" must sprecify name!");
            }
            if (isset($data['process'])) {
                $data['process'] = create_function('&$data', $data['process']);
            }
        }

        if (is_string($itemProcessFunction)) {
            $itemProcessFunction = create_function('&$item', $itemProcessFunction);
        }

        $items = array();
        foreach ($results as &$result) {
            $item = array();
            foreach ($itemData as $key => &$keyData) {
                if ($key === '*') {
                    $keyResult = $result->xpath($keyData['name']);
                    if (@$keyData['required'] === true && !isset($keyResult[0])) {
                        continue 2;
                    }
                    $key = (string)$keyResult[0];
                }
                $itemResult = $result->xpath($keyData['xpath']);
                if (!is_array($itemResult) || !isset($itemResult[0])) {
                    if (@$keyData['required'] === true) {
                        continue 2;
                    }
                } else {
                    if(@$keyData['skip_to_string'] !== true){
                        $itemResult = (string)$itemResult[0];
                    };

                    if (isset($keyData['process'])) {
                        if ($keyData['process']($itemResult) !== false) {
                            $item[$key] = $itemResult;
                        } else if (@$keyData['required'] === true) {
                            continue 2;
                        }
                    } else {
                        $item[$key] = $itemResult;
                    }
                }
            }
            if ($itemProcessFunction && $itemProcessFunction($item) !== false) {
                $items[] = $item;
            }
        }
        return $items;
    }
}
