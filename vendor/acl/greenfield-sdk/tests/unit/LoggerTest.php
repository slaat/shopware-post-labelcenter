<?php
use Mockery as m;
use Acl\GreenField\Logger;
use Psr\Log\LoggerInterface;

class LoggerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $logger;

    protected function _before()
    {
        $this->logger = new Logger;
    }

    protected function _after()
    {
        m::close();
    }

    public function testLoggerSet()
    {
        $this->assertNull($this->logger->foo(), "Logger was not yet set, expected a null in return");
    }

    public function testLogMessage()
    {
        $logger = m::mock("\\Psr\\Log\\LoggerInterface");
        $logger->shouldReceive("log")
            ->with("level", "[GREENFIELD-SDK] message")
            ->andReturn(true);
        $this->logger->setLogger($logger);

        $this->assertTrue($this->logger->log("level", "message"), "Logger call did not receive expected response");
    }
}
