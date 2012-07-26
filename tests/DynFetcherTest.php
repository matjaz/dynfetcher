<?php

require dirname(__FILE__) . '/../DynFetcher.class.php';

class DynFetcherTest extends PHPUnit_Framework_TestCase
{
    private function getDyn()
    {
        return new DynFetcher(dirname(__FILE__) . '/data/weather.html');
    }

    // Basic class set up
    
    /**
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage Error retrieving data
     * @expectedExceptionCode    1
     */
    public function test404Resource()
    {
        $dyn = new DynFetcher(dirname(__FILE__) . '/404');
        $dyn->find('foo', array());
    }

    /**
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage XPath did not return any results.
     * @expectedExceptionCode    2
     */
    public function testExceptionIfNoResult()
    {
        $dyn = $this->getDyn();
        $dyn->find('foo[', array());
    }

    // Argument validation

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage test must have array value.
     * @expectedExceptionCode    3
     */
    public function testItemDataMustBeAnArray()
    {
        $dyn = $this->getDyn();
        $dyn->find('foo', array(
            'test' => null
        ));
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage test does not have XPath specified.
     * @expectedExceptionCode    4
     */
    public function testItemDataMustHaveXpath()
    {
        $dyn = $this->getDyn();
        $dyn->find('foo', array(
            'test' => array()
        ));
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage XPath "bar" must sprecify name.
     * @expectedExceptionCode    5
     */
    public function testItemDataWildcardMustHaveName()
    {
        $dyn = $this->getDyn();
        $dyn->find('foo', array(
            '*' => array(
                'xpath' => 'bar'
            )
        ));
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage test process parameter is not valid callable.
     * @expectedExceptionCode    6
     */
    public function testItemDataProcessMustBeCallable()
    {
        $dyn = $this->getDyn();
        $dyn->find('foo', array(
            'test' => array(
                'xpath'   => 'bar',
                'process' => false
            )
        ));
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage test process parameter is not valid callable.
     * @expectedExceptionCode    6
     */
    public function testItemDataProcessStringMustBeValidFunction()
    {
        $dyn = $this->getDyn();
        $dyn->find('foo', array(
            'test' => array(
                'xpath'   => 'bar',
                'process' => 'parse error'
            )
        ));
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage itemProcessFunction parameter is not valid callable.
     * @expectedExceptionCode    7
     */
    public function testItemProcessFunctionMustBeValidFunction()
    {
        $dyn = $this->getDyn();
        $dyn->find('foo', array(), 'parse error');
    }

    // Parse data

    public function testWildCard()
    {
        $dyn = $this->getDyn();
        $res = $dyn->find('//div[@class="foreGlance"]', array(
            '*' => array(
                'xpath' => '.',
                'name'  => 'div[@class="titleSubtle"]'
            )
        ));
        $this->assertEquals(6, count($res));
        $this->assertArrayHasKey('Tonight', $res[0]);
        $this->assertArrayHasKey('*',       $res[2]);
    }

    public function testRequiredWildCard()
    {
        $dyn = $this->getDyn();
        $res = $dyn->find('//div[@class="foreGlance"]', array(
            '*' => array(
                'xpath'    => '.',
                'name'     => 'div[@class="titleSubtle"]',
                'required' => true
            )
        ));
        $this->assertEquals(5, count($res));
        $this->assertArrayHasKey('Sunday', $res[4]);
    }

    public function testItemNotRequired()
    {
        $dyn = $this->getDyn();
        $res = $dyn->find('//div[@class="foreGlance"]', array(
            'day' => array(
                'xpath' => 'div[@class="titleSubtle"]',
            )
        ));
        $this->assertEquals(6, count($res));
        $this->assertArrayHasKey('day', $res[5]);
        $this->assertEquals('Sunday', $res[5]['day']);
    }

    public function testItemRequired()
    {
        $dyn = $this->getDyn();
        $res = $dyn->find('//div[@class="foreGlance"]', array(
            'day' => array(
                'xpath'    => 'div[@class="titleSubtle"]',
                'required' => true
            )
        ));
        $this->assertEquals(5, count($res));
        $this->assertArrayHasKey('day', $res[4]);
        $this->assertEquals('Sunday', $res[4]['day']);
    }


    public function testRawItem()
    {
        $dyn = $this->getDyn();
        $res = $dyn->find('//div[@class="foreGlance"]', array(
            'summary' => array(
                'xpath' => 'div[@class="foreSummary"]',
                'raw'   => true
            )
        ));
        $this->assertEquals(6, count($res));
        foreach ($res as $item) {
            $this->assertTrue(is_array($item));
            $this->assertEquals(1, count($item));
            $this->assertArrayHasKey('summary', $item);
            $this->assertTrue(is_array($item['summary']));
            $this->assertInstanceOf('SimpleXMLElement', $item['summary'][0]);
        }
        $this->assertEquals('16 °C', trim((string)$res[0]['summary'][0]));
    }

    public function testItemProcess()
    {
        $dyn = $this->getDyn();
        $res = $dyn->find('//div[@class="foreGlance"]', array(
            'summary' => array(
                'xpath'    => 'div[@class="foreSummary"]',
                'raw'      => true,
                'required' => true,
                'process'  => '
                    $b = $data[0]->xpath("span");
                    if (isset($b[0])) {
                        $str = (string)$b[0] . " ";
                        if ((int)$str < 30) {
                            return false;
                        }
                    } else {
                        $str = "";
                    }
                    $str .= trim((string)$data[0]);
                    $data = $str;
                '
            )
        ));
        $temperatures = array(
            '16 °C',
            '17 °C',
            '31 | 17 °C',
            '30 | 18 °C',
            '31 | 19 °C'
        );
        foreach ($res as $i => $item) {
            $this->assertTrue(is_array($item));
            $this->assertEquals(1, count($item));
            $this->assertArrayHasKey('summary', $item);
            $this->assertEquals($temperatures[$i], $item['summary']);
        }
    }

    public function testItemProcessFunction()
    {
        $dyn = $this->getDyn();
        $res = $dyn->find('//div[@class="foreGlance"]', array(
            'min' => array(
                'xpath'    => 'div[@class="foreSummary"]',
                'process'  => 'trim',
                'required' => true
            ),
            'max' => array(
                'xpath' => 'div[@class="foreSummary"]/span',
            )
        ), '
            if (isset($item["min"])) {
                $item["min"] = preg_replace("#\D#", "", $item["min"]);
                if (strlen($item["min"])) {
                    $item["min"] = (int)$item["min"];
                    if ($item["min"] < 18) {
                        return false;
                    }
                } else {
                    unset($item["min"]);
                }
            }
            if (isset($item["max"])) {
                $item["max"] = (int)$item["max"];
                if ($item["max"] < 30) {
                    return false;
                }
            }
        ');
        $this->assertEquals(array(
            array('max' => 30, 'min' => 18),
            array('max' => 31, 'min' => 19),
        ), $res);
    }

}
