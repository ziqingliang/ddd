<?php

namespace tests;

use Exception;
use PHPUnit\Framework\TestCase;
use tests\values\ComponentValue;
use tests\values\ScalarValue;

class SimpleValueTester extends TestCase
{
    /**
     * 测试标准属性类型
     * int, float, string, bool
     * @throws Exception
     */
    public function testScalar()
    {
        ScalarValue::setStrictMode(true);

        $json = <<<JSON
{
    "name": "xiongda",
    "age": 18,
    "isAduit": true,
    "height": 1.73,
    "weight": 65.90
}
JSON;
        //construct
        $value1 = new ScalarValue(json_decode($json, true));
        $this->assertEquals('xiongda', $value1->name);
        $this->assertEquals(18, $value1->age);
        $this->assertEquals(true, $value1->isAduit);
        $this->assertEqualsWithDelta(1.73, $value1->height, 0.01);
        $this->assertEqualsWithDelta(65.90, $value1->weight, 0.01);

        //to json
        $json2 = $value1->toJson(false);
        $this->assertJsonStringEqualsJsonString($json, $json2);

        $value1->name = "xionger";
        $value1->age--;
        $value1->isAduit = false;
        //construct again
        $value2 = new ScalarValue($value1->toArray());
        $this->assertEquals($value1->name, $value2->name);
        $this->assertEquals($value1->age, $value2->age);
        $this->assertEquals($value1->isAduit, $value2->isAduit);
        $this->assertEqualsWithDelta($value1->height, $value2->height, 0.01);
        $this->assertEqualsWithDelta($value1->weight, $value2->weight, 0.01);

        //to json
        $this->assertJsonStringEqualsJsonString($value1->toJson(), $value2->toJson());

        //test equal
        $this->assertTrue($value2->equalTo($value1));
    }

    /**
     *
     * @throws Exception
     */
    public function testComponect()
    {
        ComponentValue::setStrictMode(true);
        $json = <<<JSON
{
    "names": ["name1", "name2", "name3"],
    "ages": [18, 19, 16],
    "weights": [50.0, 55.3, 45.8],
    "heights": [1.69, 1.75, 1.60],
    "isAduits": [true, true, false]
}
JSON;
        //construct
        $value1 = new ComponentValue(json_decode($json, true));
        $this->assertContains('name1', $value1->names);
        $this->assertContains(18, $value1->ages);
        $this->assertEqualsWithDelta(50.0, $value1->weights[0], 0.01);
        $this->assertContains(50.0, $value1->weights);
        $this->assertContains(true, $value1->isAduits);

        //to json
        $this->assertJsonStringEqualsJsonString($json, $value1->toJson());

        //change some
        $value1->names[0] = 'name0';
        $value1->isAduits = [false, false, false];
        $this->assertContains('name0', $value1->names);
        $this->assertNotContains(true, $value1->isAduits);

        //re construct
        $value2 = new ComponentValue($value1->toArray());
        $this->assertEquals($value1->names, $value2->names);
        $this->assertEquals($value1->isAduits, $value2->isAduits);
        $this->assertJsonStringEqualsJsonString($value1->toJson(), $value2->toJson());

        //test equal
        $this->assertTrue($value1->equalTo($value2));
        $value1->names[] = 'name4';
        $this->assertFalse($value1->equalTo($value2));
    }
}
