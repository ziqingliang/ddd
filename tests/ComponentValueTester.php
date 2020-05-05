<?php

namespace tests;

use Exception;
use PHPUnit\Framework\TestCase;
use tests\values\ComponentValue2;
use tests\values\ComponentValue3;

class ComponentValueTester extends TestCase
{
    /**
     * 测试标准属性类型
     * int, float, string, bool
     * @throws Exception
     */
    public function testWithObject()
    {
        ComponentValue2::setStrictMode(true);

        $json = <<<JSON
{
    "name": "xiongda",
    "ages": [18, 19, 20],
    "scalarValue": {
        "name": "xionger",
        "age": 18,
        "isAduit": true,
        "height": 1.73,
        "weight": 65.90
    }
}
JSON;
        //construct
        $value1 = new ComponentValue2(json_decode($json, true));
        $this->assertEquals('xiongda', $value1->name);
        $this->assertEquals(18, $value1->ages[0]);
        $this->assertEquals(18, $value1->scalarValue->age);
        $this->assertEquals(true, $value1->scalarValue->isAduit);
        $this->assertEqualsWithDelta(1.73, $value1->scalarValue->height, 0.01);
        $this->assertEqualsWithDelta(65.90, $value1->scalarValue->weight, 0.01);

        //to json
        $json2 = $value1->toJson(true);
        $this->assertJsonStringEqualsJsonString($json, $json2);

        $value1->name = "xionger";
        $value1->scalarValue->age--;
        $value1->scalarValue->isAduit = false;
        //construct again
        $value2 = new ComponentValue2($value1->toArray());
        $this->assertEquals($value1->name, $value2->name);
        $this->assertEquals($value1->scalarValue->age, $value2->scalarValue->age);
        $this->assertEquals($value1->scalarValue->isAduit, $value2->scalarValue->isAduit);
        $this->assertEqualsWithDelta($value1->scalarValue->height, $value2->scalarValue->height, 0.01);
        $this->assertEqualsWithDelta($value1->scalarValue->weight, $value2->scalarValue->weight, 0.01);

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
        ComponentValue3::setStrictMode(true);
        $json = <<<JSON
{
    "name": "xiongda",
    "ages": [18, 19, 20],
    "scalarValues": [
        {
            "name": "xionger1",
            "age": 18,
            "isAduit": true,
            "height": 1.73,
            "weight": 65.90
        },
        {
            "name": "xionger2",
            "age": 18,
            "isAduit": true,
            "height": 1.73,
            "weight": 65.90
        }
    ]
}
JSON;
        //construct
        $value1 = new ComponentValue3(json_decode($json, true));
        $this->assertEquals('xiongda', $value1->name);
        $this->assertEquals(18, $value1->ages[0]);
        $this->assertEquals(18, $value1->scalarValues[0]->age);
        $this->assertEquals(true, $value1->scalarValues[0]->isAduit);
        $this->assertEqualsWithDelta(1.73, $value1->scalarValues[0]->height, 0.01);
        $this->assertEqualsWithDelta(65.90, $value1->scalarValues[0]->weight, 0.01);

        //to json
        $json2 = $value1->toJson(true);
        $this->assertJsonStringEqualsJsonString($json, $json2);

        $value1->name = "xionger";
        $value1->scalarValues[0]->age--;
        $value1->scalarValues[0]->isAduit = false;
        //construct again
        $value2 = new ComponentValue3($value1->toArray());
        $this->assertEquals($value1->name, $value2->name);
        $this->assertEquals($value1->scalarValues[0]->age, $value2->scalarValues[0]->age);
        $this->assertEquals($value1->scalarValues[0]->isAduit, $value2->scalarValues[0]->isAduit);
        $this->assertEqualsWithDelta($value1->scalarValues[1]->height, $value2->scalarValues[1]->height, 0.01);
        $this->assertEqualsWithDelta($value1->scalarValues[1]->weight, $value2->scalarValues[1]->weight, 0.01);

        //to json
        $this->assertJsonStringEqualsJsonString($value1->toJson(), $value2->toJson());

        //test equal
        $this->assertTrue($value2->equalTo($value1));
    }
}
