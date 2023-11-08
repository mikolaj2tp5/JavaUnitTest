<?php declare(strict_types = 1);

require_once("app/libs/Sensor.php");

use PHPUnit\Framework\TestCase;

class SensorTest extends TestCase {
    private $instance;

    public function setUp(): void {
        $this->instance = new Sensor();
    }

    public function tearDown(): void {
        unset($this->instance);
    }

    public function testIsLocal() {
        $out = $this->instance->isLocal();
        $this->assertFalse($out);
    }

    public function testAddrIp() {
        $out = $this->instance->addrIp();
        $this->assertNull($out);
    }

    public function testBrowser() {
        $out = $this->instance->browser();
        $this->assertEquals("", $out);
    }

    public function testSystem() {
        $out = $this->instance->system();
        $this->assertEquals("", $out);
    }
}