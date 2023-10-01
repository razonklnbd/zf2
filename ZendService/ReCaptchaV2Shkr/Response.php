<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */

namespace ZendService\ReCaptchaV2Shkr;

use Zend\Http\Response as HTTPResponse;
use Zend\Json\Json;

/**
 * Zend_Service_ReCaptcha_Response
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage ReCaptcha
 */
class Response
{
    /**
     * Status
     *
     * true if the response is valid or false otherwise
     *
     * @var boolean
     */
    protected $status = null;

    /**
     * Error code
     *
     * The error code if the status is false. The different error codes can be found in the
     * recaptcha API docs.
     *
     * @var string
     */
    protected $errorCode = null;

    /**
     * Class constructor used to construct a response
     *
     * @param string $status
     * @param string $errorCode
     * @param \Zend\Http\Response $httpResponse If this is set the content will override $status and $errorCode
     */
    public function __construct($status = null, $errorCode = null, HTTPResponse $httpResponse = null)
    {
        if ($status !== null) {
            $this->setStatus($status);
        }

        if ($errorCode !== null) {
            $this->setErrorCode($errorCode);
        }

        if ($httpResponse !== null) {
            $this->setFromHttpResponse($httpResponse);
        }
    }

    /**
     * Set the status
     *
     * @param string $status
     * @return \ZendService\ReCaptcha\Response
     */
    public function setStatus($status)
    {
		$this->status = false;
		if(is_string($status)){
			if ($status === 'true') {
				$this->status = true;
			}
		}
        if(is_bool($status)) $this->status=$status;
#die('$this->status: '.var_export($this->status, true));
        return $this;
    }

    /**
     * Get the status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Alias for getStatus()
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->getStatus();
    }

    /**
     * Set the error code
     *
     * @param string $errorCode
     * @return \ZendService\ReCaptcha\Response
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * Get the error code
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
	 *
	 * {
	"success": false,
	"challenge_ts": "2018-03-21T13:00:46Z",
	"hostname": "www.pumpsandinstrumentations.com",
	"error-codes": [
	"timeout-or-duplicate"
	]
	}
	 *
     * Populate this instance based on a Zend_Http_Response object
     *
     * @param \Zend\Http\Response $response
     * @return \ZendService\ReCaptcha\Response
     */
    public function setFromHttpResponse(HTTPResponse $response)
    {
        $body = $response->getBody();
		$data=Json::decode($body, Json::TYPE_ARRAY);

        /*$parts = explode("\n", $body, 2);

        if (count($parts) !== 2) {
            $status = 'false';
            $errorCode = '';
        } else {
            list($status, $errorCode) = $parts;
        }*/
#die('<pre>'.var_export(array($body, $data), true));
        $this->setStatus($data['success']);
        if(array_key_exists('error-codes', $data)) $this->setErrorCode($data['error-codes']);

        return $this;
    }
}
