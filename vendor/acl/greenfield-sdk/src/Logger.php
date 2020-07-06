<?php
namespace Acl\GreenField;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * GreenFeild Logger
 *
 * The GreenField Logger class is a wrapper around the actual logger implementing
 * the PSR-4 Logger Interface, and forwards all calls to the logger if one is set.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class Logger implements LoggerAwareInterface
{
    /**
     * Logger instance
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Magic call
     *
     * Forward all calls to the logger if one was set. If the method does not exist
     * in the logger instance, an exception is thrown.
     *
     * @param string $name Method name to call on the logger
     * @param array $arguments Method call parameters
     * @return mixed
     *
     * @throws \Acl\GreenField\Exceptions\GreenFieldSDKException
     */
    public function __call($name, array $arguments = [])
    {
        if ($this->logger === null) {
            return null;
        }

        if (method_exists($this->logger, $name) === false) {
            throw new GreenFieldSDKException(
                "Requested method name ({$name}) does not exist on the logger"
            );
        }

        // prepend log message
        $index = 0;
        if ($name === "log") {
            $index = 1;
        }
        if (isset($arguments[$index]) && is_string($arguments[$index])) {
            $arguments[$index] = "{$arguments[$index]}";
        }

        return call_user_func_array([$this->logger, $name], $arguments);
    }

    /**
     * Set logger
     *
     * Sets a logger instance on the object.
     *
     * @param \Psr\Log\LoggerInterface $logger Logger instance
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
