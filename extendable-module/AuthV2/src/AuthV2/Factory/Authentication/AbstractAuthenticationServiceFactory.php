<?php
namespace AuthV2\Factory\Authentication;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Lib3rdParty\Crypt\CommunicationEncryptDecrypt;
use AuthV2\Api\StatelessAuthService;

abstract class AbstractAuthenticationServiceFactory implements FactoryInterface, ServiceLocatorAwareInterface {
	#abstract protected function getUserTable();
	private $sm;
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator){
		$this->sm=$serviceLocator;
		return $this;
	}
	public function getServiceLocator(){ return $this->sm; }
	private $request;
	/**
	 * @return Request
	 */
	private function getRequest(){
		if(!isset($this->request)) $this->request=$this->getServiceLocator()->get('Request');
		return $this->request;
	}
	/**
	 * @throws \Exception
	 * @return \Lib3rdParty\Crypt\Parameters
	 */
	protected function getAuthorizationParametersFromHeader(){
	    $params=CommunicationEncryptDecrypt::getParametersFromRequest($this->getRequest());
	    $encryptedData=$params->getData();
	    if(empty($encryptedData)) throw new \Exception('authorization data NOT found');
	    return $params;
	    
	    
	    
	    
	    
	    
	    $authString=null;
	    $headers = $this->getRequest()->getHeaders();
	    /*
	     * no token provided: Zend\Http\PhpEnvironment\Request<pre>Array(    [Content-Type] => application/x-www-form-urlencoded    [Content-Length] => 47    [X-Original-Url] => /login/stateless    [X-Requested-With] => XMLHttpRequest    [User-Agent] => Java/1.8.0_102    [Host] => cart.shopping.shahadat.web    [Accept] => application/json    [Connection] => keep-alive)
	     */
	    if ($headers->has('Authorization')){
	        $token=$headers->get('Authorization');
	        #die('token found! '.$token->getFieldValue());
	        if(false !== $token) $authString=$token->getFieldValue();
	    } #else echo('no token provided: '.get_class($request).'<pre>'.print_r($headers->toArray(), true));
	    /* if ($headers->has('X-Token')){
	        $token=$headers->get('X-Token');
	        #die('token found! '.$token->getFieldValue());
	        if(false !== $token) $authString=$token->getFieldValue();
	    } #else echo('no token provided: '.get_class($request).'<pre>'.print_r($headers->toArray(), true)); */
	    if(!empty($authString)){
	        
	    }
	    return $authString;
	}
	protected function getTokenFromHeader(){
		$token2rtrn=null;
		$headers = $this->getRequest()->getHeaders();
		/*
		 * no token provided: Zend\Http\PhpEnvironment\Request<pre>Array(    [Content-Type] => application/x-www-form-urlencoded    [Content-Length] => 47    [X-Original-Url] => /login/stateless    [X-Requested-With] => XMLHttpRequest    [User-Agent] => Java/1.8.0_102    [Host] => cart.shopping.shahadat.web    [Accept] => application/json    [Connection] => keep-alive)
		 */
		if ($headers->has('Token')){
			$token=$headers->get('Token');
			#die('token found! '.$token->getFieldValue());
			if(false !== $token) $token2rtrn=$token->getFieldValue();
		} #else echo('no token provided: '.get_class($request).'<pre>'.print_r($headers->toArray(), true));
		if ($headers->has('X-Token')){
			$token=$headers->get('X-Token');
			#die('token found! '.$token->getFieldValue());
			if(false !== $token) $token2rtrn=$token->getFieldValue();
		} #else echo('no token provided: '.get_class($request).'<pre>'.print_r($headers->toArray(), true));
		return $token2rtrn;
	}
	protected function isPreflightCorsRequested(){ return ('options'==strtolower($this->getRequest()->getMethod())); }
	private $userTable;
	protected function getUserTable(){
	    if(!isset($this->userTable)) $this->userTable=$this->getServiceLocator()->get('AuthV2\\User\\UserTable');
	    return $this->userTable;
	}
	private function getClosureForIdentityArray(){
	    return $this->getServiceLocator()->get('AuthV2\\ClosureForIdentityArray');
	}
	public function createService(ServiceLocatorInterface $serviceLocator) {
	    $this->setServiceLocator($serviceLocator);
	    StatelessAuthService::setStaticSystemPrefix($serviceLocator->get('AuthV2\\SysPrefix'));
	    try {
	        $authStorage=$serviceLocator->get('AuthStorage');
	    } catch (\Exception $e) {
	        die('@'.__LINE__.': '.__FILE__.' - '.$e->getMessage().'<pre>'.$e->getTraceAsString());
	    }
	    $checkToken=false;
	    $auth2rtrn=new StatelessAuthService($authStorage, $serviceLocator->get('AuthV2\\ValidatableAuthAdapter'), $this->getUserTable());
	    $auth2rtrn->setClosureForIdentityArray($this->getClosureForIdentityArray());
	    try {
	        $auth2rtrn->registerRacsDataHandlerClosure($serviceLocator->get('AuthV2\\RacsDataHandlerClosure'));
	    } catch (\Exception $e) {
	    }
	    try {
	        $authParameters=$this->getAuthorizationParametersFromHeader();
	        #die('id: '.$authParameters->getId().PHP_EOL.print_r($authParameters->getArrayCopy(), true).PHP_EOL.'@'.__LINE__.': '.__FILE__.PHP_EOL);
	        if(false==$auth2rtrn->setEncryptedAuthorizationString($authParameters->getId(), $authParameters->getData())){
	            if($auth2rtrn->hasIdentity()) $auth2rtrn->clearIdentity();
	        }
	    } catch (\Exception $e) {
	        #die($e->getTraceAsString().PHP_EOL.PHP_EOL.$e->getMessage());
	        $checkToken=true;
	        
	    }
	    if(true==$checkToken){
	        $token=$this->getTokenFromHeader();
	        if(!is_null($token)){
	            try{
	                if(false==$auth2rtrn->setToken($token)){
	                    if($auth2rtrn->hasIdentity()) $auth2rtrn->clearIdentity();
	                }
	                #if($auth2rtrn->hasIdentity()) die('@'.__LINE__.': valid $token: '.$token);
	            }catch(\Exception $e){
	                #die('@'.__LINE__.': '.__FILE__.' - '.$e->getMessage().'<pre>'.$e->getTraceAsString());
	                if($auth2rtrn->hasIdentity()) $auth2rtrn->clearIdentity();
	            }
	        }
	    }
	    if($this->isPreflightCorsRequested()) $auth2rtrn->setAsPreflightCorsRequest();
	    #elseif(is_null($token) && $auth2rtrn->hasIdentity()) $auth2rtrn->clearIdentity();
	    return $auth2rtrn;
	}
}

