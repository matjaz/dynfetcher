<?php

/**
* Use this class under LGPL license
* @author   Matjaz Lipus <me@matjaz.info>
* @link     https://github.com/matjaz/dynfetcher
* @package  dynfetcher
* @version  1.1
*/ 
class DynFetcher
{
    const ERROR_RETRIEVE_DATA        = 1;
    const ERROR_NO_RESULT            = 2;
    const ERROR_ITEM_MUST_BE_ARRAY   = 3;
    const ERROR_NO_XPATH             = 4;
    const ERROR_WILDCARD_NO_NAME     = 5;
    const ERROR_INVALID_PROCESS      = 6;
    const ERROR_INVALID_ITEM_PROCESS = 7;

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
                throw new UnexpectedValueException('Error retrieving data.', self::ERROR_RETRIEVE_DATA);
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
    public function find($itemXPath, array $itemData, $itemProcessFunction = null)
    {
        $results = @$this->fetch()->xpath($itemXPath);

        if (!is_array($results)) {
            throw new UnexpectedValueException('XPath did not return any results.', self::ERROR_NO_RESULT);
        }

        foreach ($itemData as $key => &$data) {
            if (!is_array($data)) {
                throw new InvalidArgumentException("$key must have array value.", self::ERROR_ITEM_MUST_BE_ARRAY);
            }
            if (!isset($data['xpath'])) {
                throw new InvalidArgumentException("$key does not have XPath specified.", self::ERROR_NO_XPATH);
            }
            if ($key === '*' && !isset($data['name'])) {
                throw new InvalidArgumentException("XPath \"{$data['xpath']}\" must sprecify name.", self::ERROR_WILDCARD_NO_NAME);
            }
            if (isset($data['process'])) {
                if (is_string($data['process']) && !is_callable($data['process'])) {
                    $data['process'] = @create_function('&$data', $data['process']);
                }
                if (!is_callable($data['process'])) {
                    throw new InvalidArgumentException("$key process parameter is not valid callable.", self::ERROR_INVALID_PROCESS);
                }
            }
        }

        if (is_string($itemProcessFunction)) {
            $itemProcessFunction = @create_function('&$item', $itemProcessFunction);
            if (!is_callable($itemProcessFunction)) {
                throw new InvalidArgumentException('itemProcessFunction parameter is not valid callable.', self::ERROR_INVALID_ITEM_PROCESS);
            }
        }

        $items = array();
        foreach ($results as &$result) {
            $item = array();
            foreach ($itemData as $key => &$keyData) {
                if ($key === '*') {
                    $keyResult = $result->xpath($keyData['name']);
                    if (is_array($keyResult) && isset($keyResult[0])) {
                        $key = (string)$keyResult[0];
                    } else if (@$keyData['required'] === true) {
                        continue 2;
                    }
                }
                $itemResult = $result->xpath($keyData['xpath']);
                if (!is_array($itemResult) || !isset($itemResult[0])) {
                    if (@$keyData['required'] === true) {
                        continue 2;
                    }
                } else {
                    if (@$keyData['raw'] !== true) {
                        $itemResult = (string)$itemResult[0];
                    }

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
            if (!$itemProcessFunction || $itemProcessFunction($item) !== false) {
                $items[] = $item;
            }
        }
        return $items;
    }
}
