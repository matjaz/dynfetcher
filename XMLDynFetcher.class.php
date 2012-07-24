<?php

require_once __DIR__ . '/DynFetcher.class.php';

class XMLDynFetcher extends DynFetcher
{
    public function fetch()
    {
        if (!$xml = @file_get_contents($this->url)) {
            throw new Exception("Missing content at url: {$this->url}");
        }
        return simplexml_load_string($xml,
            'SimpleXMLElement', LIBXML_NOCDATA);
    }
}
