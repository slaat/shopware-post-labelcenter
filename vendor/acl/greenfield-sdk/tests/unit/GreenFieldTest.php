<?php

use Mockery as m;
use Acl\GreenField\GreenField;
use Acl\GreenField\HttpClients\HttpClientInterface;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class GreenFieldTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $gf = null;

    protected function _before() {
        $client = m::mock("\\Acl\\GreenField\\HttpClients\\HttpClientInterface");
        $client->shouldReceive("setBaseUrl");
        $this->gf = new GreenField($client);
    }

    protected function _after()
    {
        m::close();
    }

    public function testGetModule()
    {
        // invalid module required
        // must throw exception
        $exception = false;
        try {
            $module = $this->gf->foo;
        } catch (GreenFieldSDKException $e) {
            $exception = true;
        }
        $this->assertTrue(
            $exception,
            "Exception was expected since an invalid module was requested"
        );

        // valid required
        // default set
        // must not throw exception
        $exception = false;
        try {
            $module = $this->gf->plc;
        } catch (GreenFieldSDKException $e) {
            $exception = true;
        }
        $this->assertFalse(
            $exception,
            "No Exception was expected since a valid module was requested"
        );
        $this->assertInstanceOf(
            "\\Acl\\GreenField\\Modules\\Plc\\Plc",
            $module,
            "Shoepping GreenField module was expected."
        );
    }

    public function testModuleSet()
    {
        // invalid module name
        // must throw exception
        $exception = false;
        try {
            $this->gf->setModule("foo");
        } catch (GreenFieldSDKException $e) {
            $exception = true;
        }
        $this->assertTrue(
            $exception,
            "An exception was expected when setting an invalid module as default"
        );

        // valid module name
        // must not throw exception
        // must set that module as default
        $exception = false;
        try {
            $this->gf->setModule("plc");
        } catch (GreenFieldSDKException $e) {
            $exception = true;
        }
        $this->assertFalse(
            $exception,
            "No exception was expected when setting a valid module as default"
        );
        $this->assertInstanceOf(
            "\\Acl\\GreenField\\Modules\\AbstractModule",
            $this->gf->plc
        );
    }

    public function testModules()
    {
        // ensure all added modules to the $modules array are mapped to correct
        // classes
        $module = $this->gf->plc;
        $this->assertInstanceOf(
            "\\Acl\\GreenField\\Modules\\Plc\\Plc",
            $module,
            "PLC GreenField module was expected."
        );

        $module = $this->gf->shoepping;
        $this->assertInstanceOf(
            "\\Acl\\GreenField\\Modules\\Shoepping\\Shoepping",
            $module,
            "Shoepping GreenField module was expected."
        );
    }
}
