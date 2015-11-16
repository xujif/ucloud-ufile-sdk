<?php

use Xujif\UcloudUfileSdk\UfileSdk;

function dd(){
	var_dump(func_get_args());die();
}

class sdkTest extends  PHPUnit_Framework_TestCase
{
    protected $sdk;
    public function setUp(){
        $this->sdk = new UfileSdk('storage321','***','*****');
    }
    public function testPutFile()
    {
        list($ret,$code) = $this->sdk->put('text.txt',__DIR__.'/text.txt');
        $this->assertEquals($code,200);
    }
    public function testPut()
    {
        list($ret,$code) = $this->sdk->put('dd','sdfsdfsdfsfsdf');
        $this->assertEquals($code,200);
    }
    public function testGet(){
        $contents = $this->sdk->get('dd');

        $this->assertEquals($contents,'sdfsdfsdfsfsdf');
    }
    public function testExists(){
        $exists =  $this->sdk->exists('dd222');
        $this->assertEquals($exists,false);
    }

    public function testSize(){
        $size = $this->sdk->size('dd');
        $this->assertEquals($size,14);
    }
    //mime 不准
    // public function testMime(){
    //     $mime = $this->sdk->mime('text.txt');
    // }
}
