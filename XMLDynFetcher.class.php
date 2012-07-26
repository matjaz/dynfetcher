<?php

require_once dirname(__FILE__) . '/DynFetcher.class.php';

class XMLDynFetcher extends DynFetcher
{
    protected function fetch()
    {
        if (!$xml = @file_get_contents($this->url)) {
            throw new UnexpectedValueException('Error retrieving data.', self::ERROR_RETRIEVE_DATA);
        }
        return simplexml_load_string($xml,
            'SimpleXMLElement', LIBXML_NOCDATA);
    }
}
