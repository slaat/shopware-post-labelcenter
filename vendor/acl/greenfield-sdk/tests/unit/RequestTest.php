<?php
use Mockery as m;
use Acl\GreenField\Http\Request;

class RequestTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $request;

    protected function _before()
    {
        $this->request = new Request;
    }

    protected function _after()
    {
        m::close();
    }

    public function testSerialization()
    {
        $this->request
            ->setUrl("foo")
            ->setMethod("get")
            ->addHeader("bar", "baz")
            ->setData(["foo" => "bar"]);

        $this->assertEquals(
            "{\"url\":\"foo\",\"method\":\"GET\",\"payload\":{\"foo\":\"bar\"},\"headers\":{\"bar\":\"baz\"}}",
            json_encode($this->request),
            "Serialized data did not match expected value"
        );
    }

    public function testRender()
    {
        $this->request
            ->setMethod("post")
            ->setData(["foo" => "bar"])
            ->setMetaData(["baz" => "qux"]);

        $expected = [
            "data" => ["foo" => "bar"],
            "meta" => ["baz" => "qux"]
        ];
        $this->assertEquals($expected, $this->request->render(), "Rendered data did not match expected array");

        unset($expected["meta"]);
        $this->request->skipMetaData();
        $this->assertEquals(
            $expected,
            $this->request->render(),
            "Rendered data did not match expected array with removed meta data"
        );

        $expected["foo"] = $expected["data"];
        unset($expected["data"]);
        $this->request->setDataKey("foo");
        $this->assertEquals(
            $expected,
            $this->request->render(),
            "Rendered data did not match expected array with custom data key"
        );
    }
}
