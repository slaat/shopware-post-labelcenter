<?php
use Mockery as m;
use Acl\GreenField\Http\Response;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

class ResponseTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $response;

    protected function _before()
    {
        $this->response = new Response;
    }

    protected function _after()
    {
        m::close();
    }

    public function testResponseParsing()
    {
        $raw = m::mock("\\Acl\\GreenField\\Http\\RawResponse");
        $raw->shouldReceive("getBody")
            ->andReturn(
                "{\"data\":{\"foo\":\"bar\"},\"meta\":{\"baz\":\"qux\"}}",
                "invalid json"
            );

        $this->response->parse($raw);
        $this->assertEquals($raw, $this->response->getRawResponse());

        $dataObj = new stdClass;
        $dataObj->foo = "bar";
        $metaObj = new stdClass;
        $metaObj->baz = "qux";
        $this->assertEquals($dataObj, $this->response->data);
        $this->assertEquals($metaObj, $this->response->meta);

        $exception = false;
        try {
            $this->response->parse($raw);
        } catch (GreenFieldSDKException $e) {
            $exception = true;
        }
        $this->assertTrue($exception, "Exception was expected when trying to parse a non-JSON response");
    }

    public function testErrorParsing()
    {
        $errorResponse = [
            "error" => [[
                "code"              =>  123,
                "message"           =>  "test error",
                "extendedMessage"   =>  "error details"
            ]]
        ];
        $raw = m::mock("\\Acl\\GreenField\\Http\\RawResponse");
        $raw->shouldReceive("getBody")
            ->andReturn(json_encode($errorResponse));

        $this->response->parse($raw);
        $errors = $this->response->errors;
        $this->assertNotEmpty($errors, "Response error was not expected to be empty");
        $this->assertCount(1, $errors, "Expected exactly one error in the response");
        $error = $errors[0];
        $this->assertEquals(123, $error->code, "Error code is not of expected value");
        $this->assertEquals("test error", $error->message, "Error message is not of expected value");
        $this->assertEquals("error details", $error->details, "Error details are not of expected value");
    }
}
