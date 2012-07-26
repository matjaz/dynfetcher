<?php

require dirname(__FILE__) . '/../XMLDynFetcher.class.php';

class XMLDynFetcherTest extends PHPUnit_Framework_TestCase
{
    public function testBasicsXMLRead()
    {
        $dyn = new XMLDynFetcher(dirname(__FILE__) . '/data/data.xml');
        $res = $dyn->find('//contact-info', array(
            'name'    => array('xpath' => 'name'),
            'company' => array('xpath' => 'company'),
            'phone'   => array('xpath' => 'phone')
        ));
        $contact = array(array(
            'name'    => 'Jane Smith',
            'company' => 'AT&T',
            'phone'   => '(212) 555-4567'
        ));
        $this->assertEquals($contact, $res);
    }

    /**
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage Error retrieving data
     * @expectedExceptionCode    1
     */
    public function test404()
    {
        $dyn = new XMLDynFetcher(dirname(__FILE__) . '/404');
        $res = $dyn->find('foo', array());
    }
}
