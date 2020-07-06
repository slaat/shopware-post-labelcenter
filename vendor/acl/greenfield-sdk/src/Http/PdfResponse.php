<?php
namespace Acl\GreenField\Http;

use Acl\GreenField\Exceptions\GreenFieldSDKException;

/**
 * GreenField API PDF Response
 *
 * The GreenField PDF response is used for responses with Content-Type 'application/json',
 * and parses the Raw Response into the class properties.
 *
 * @package   ACL\GreenFeild-PHP-SDK
 * @author    Tomaz Lovrec <tomaz.lovrec@acl.at>
 * @copyright 2017 (c) ACL GmbH
 * @license   Proprietary
 * @link      http://gitlab.acl.at/tools/greenfield-php-sdk
 * @version   1.0
 */
class PdfResponse implements ResponseInterface
{
    /**
     * Raw response object
     *
     * @var \Acl\GreenField\Http\RawResponse
     */
    protected $raw;

    /**
     * PDF Content
     *
     * @var string
     */
    protected $content;

    /**
     * {@inheritDoc}
     */
    public function parse(RawResponse $rawResp)
    {
        $this->raw = $rawResp;
        $this->content = $rawResp->getBody();
        return $this;
    }

    /**
     * Parse PDF data from string. If the second parameter is omitted, then the
     * body of the PDF is base64 decoded.
     *
     * @param string $body Pdf data string
     * @param bool $encoded Set to true if passed in Pdf data string is base64 encoded. Default true
     * @return self
     */
    public function parseFromString($body, $encoded = true)
    {
        $this->content = $encoded === true ? base64_decode($body) : $body;
        return $this;
    }

    /**
     * Sets the Raw Response to the PdfResponse object.
     *
     * @param \Acl\GreenField\Http\RawResponse $rawResp Raw response from the API
     * @return self
     */
    public function setRawResponse(RawResponse $rawResp)
    {
        $this->raw = $rawResp;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRawResponse()
    {
        return $this->raw;
    }

    /**
     * Returns the content of the PDF. If the '$encoded' parameter is set to true,
     * the returned PDF content will be base64 encoded.
     *
     * @param bool $encoded Encode PDF content before returning. Default false
     * @return string
     */
    public function getContent($encoded = false)
    {
        return $encoded === true
            ? "data:application/pdf;base64," . base64_encode($this->content)
            : $this->content;
    }
}
