<?php
namespace ZendService\ReCaptchaV2Shkr;


use Zend\Captcha\ReCaptcha;

class ReCaptchaZendCore extends ReCaptcha{
	public function setServiceV2(ReCaptchaV2 $pCaptcha){
		$this->service=$pCaptcha;
		$this->serviceParams  = $pCaptcha->getParams();
		$this->serviceOptions = $pCaptcha->getOptions();
		$this->RESPONSE  = $pCaptcha->getResponseFieldName();
		return $this;
	}
	public function isValid($value, $context = null) {
		if (!is_array($value) && !is_array($context)) {
			$this->error(self::MISSING_VALUE);
			return false;
		}

		/*if (!is_array($value) && is_array($context)) {
			$value = $context;
		}*/
		if(!array_key_exists($this->RESPONSE, $context)){
			$this->error(self::MISSING_VALUE);
			return false;
		}
		$respns2validate=$context[$this->RESPONSE];
		#die('<pre>'.print_r($context, true));

		/*if (empty($value[$this->RESPONSE])) {
			$this->error(self::MISSING_VALUE);
			return false;
		}*/

		$service = $this->getService();

		$res = $service->verify($respns2validate);

		if (!$res) {
			$this->error(self::ERR_CAPTCHA);
			return false;
		}

		if (!$res->isValid()) {
			$errCode=$res->getErrorCode();
			$this->error(self::BAD_CAPTCHA, reset($errCode));
			$service->setParam('error', reset($errCode));
			return false;
		}

		return true;
	}
}