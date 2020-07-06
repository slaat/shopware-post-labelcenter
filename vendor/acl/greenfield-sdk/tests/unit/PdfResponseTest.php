<?php
use Mockery as m;
use Acl\GreenField\Http\PdfResponse;

class PdfResponseTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $response;

    protected function _before()
    {
        $this->response = new PdfResponse;
    }

    protected function _after()
    {
        m::close();
    }

    public function testContentParsed()
    {
        $rawResponse = m::mock("\\Acl\\GreenField\\Http\\RawResponse");
        $rawResponse->shouldReceive("getBody")
            ->andReturn("pdf content");
        $this->response->parse($rawResponse);

        $this->assertEquals(
            "pdf content",
            $this->response->getContent(),
            "Response content does not match expected value"
        );

        $this->assertEquals(
            "data:application/pdf;base64," . base64_encode("pdf content"),
            $this->response->getContent(true),
            "Response encoded content does not match expected value"
        );
    }
}
