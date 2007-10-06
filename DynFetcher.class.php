<?php

/**
* Use this class under LGPL license
* @author   Matjaz Lipus <me@matjaz.info>
* @link     http://code.google.com/p/dynfetcher
* @package  dynfetcher
* @version  1.0
*/ 
class DynFetcher
{

    protected $url;
    protected $rawData = null;

    /**
     *
     * @param string $URL URL of page
     */
    function __construct($url)
    {
        $this->url = $url;
    }

    /**
     *
     * @return string Data fetched from URL
     */
    function fetch()
    {
        if (is_null($this->rawData)) {
            $res = @file_get_contents($this->url);
            if (!is_string($res)) {
                throw new Exception('Error retrieving data');
            }
            $this->rawData = $res;
        }
        return $this->rawData;
    }

    /**
     * 
     * @param string $itemXPath Items XPath expression
     * @param array $itemData Array of key-values pairs of data we want to fetch
     * @param string $itemProcessFunction PHP code for additional processing of each item (passed argument is $item)
     * @return array Array of matched items
     */
    function find($itemXPath, $itemData, $itemProcessFunction = null)
    {
        if (!is_array($itemData)) {
            throw new Exception('$itemData must be array!');
        }
        $this->fetch();
        $simpleXML = simplexml_import_dom(@DomDocument::loadHTML($this->rawData));
        $results = $simpleXML->xpath($itemXPath);
        unset($simpleXML);

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
            if (isset($data['process'])) {
                $data['process'] = create_function('&$data', $data['process']);
            }
        }

        if (is_string($itemProcessFunction)) {
            $itemProcessFunction = create_function('&$item', $itemProcessFunction);
            $processItem = true;
        } else {
            $processItem = false;
        }

        $items = array();
        foreach ($results as &$result) {
            $item = array();
            foreach ($itemData as $key => &$keyData) {
                $itemResult = $result->xpath($keyData['xpath']);
                if (!is_array($itemResult) || !isset($itemResult[0])) {
                    if (@$keyData['required'] === true) {
                        continue 2;
                    }
                } else {
                    $itemResult = (string)$itemResult[0];
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
            if (!$processItem || $itemProcessFunction($item) !== false) {
                $items[] = $item;
            }
        }
        return $items;
    }
}


?>