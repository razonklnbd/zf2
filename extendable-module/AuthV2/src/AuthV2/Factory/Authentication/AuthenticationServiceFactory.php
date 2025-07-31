<?php
namespace AuthV2\Factory\Authentication;

#use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
#use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter as DbTableAuthAdapter;
use AuthV2\Api\AuthenticationService;
use AuthV2\Api\StatelessAuthService;

/**
 * @method \AuthV2\Api\StatelessAuthService __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
 * @author shkhan
 */
class AuthenticationServiceFactory extends AbstractAuthenticationServiceFactory {


	/* private $userTable;
	protected function getUserTable(){
		if(!isset($this->userTable)) $this->userTable=$this->getServiceLocator()->get('AuthV2\\User\\UserTable');
		return $this->userTable;
	}
	private function getClosureForIdentityArray(){
		return $this->getServiceLocator()->get('AuthV2\\ClosureForIdentityArray');
	}
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$this->setServiceLocator($serviceLocator);
		#die('here we try to create service! @'.__LINE__.': '.__FILE__);
		#$dbTableAuthAdapter = new DbTableAuthAdapter($serviceLocator->get('Zend\Db\Adapter\Adapter'), 'User', 'loginId', 'password', 'password(?)');
		#die('just stop here @'.__LINE__.': '.__FILE__);
		#$authService = new TfwAuthenticationService($serviceLocator->get('AuthStorage'), $dbTableAuthAdapter);
		#$authService = new AuthenticationService($serviceLocator->get('AuthStorage'), $serviceLocator->get('TfwAuthDatabase'));
        AuthenticationService::setStaticSystemPrefix($serviceLocator->get('AuthV2\\SysPrefix'));
        $authStorage=$serviceLocator->get('AuthStorage');
		#die('fount authstorate @'.__LINE__.': '.__FILE__);
        $vldtblAuthAdptr=$serviceLocator->get('AuthV2\\ValidatableAuthAdapter');
        $checkToken=$need2clear=false;
        $auth2rtrn=new StatelessAuthService($authStorage, $vldtblAuthAdptr, $this->getUserTable());
        $auth2rtrn->setClosureForIdentityArray($this->getClosureForIdentityArray());
        try {
            $authParameters=$this->getAuthorizationParametersFromHeader();
            #die('id: '.$authParameters->getId().PHP_EOL.print_r($authParameters->getArrayCopy(), true).PHP_EOL.'@'.__LINE__.': '.__FILE__.PHP_EOL);
            if(false==$auth2rtrn->setEncryptedAuthorizationString($authParameters->getId(), $authParameters->getData())){
                if($auth2rtrn->hasIdentity()) $auth2rtrn->clearIdentity();
            }
            #if(false==$auth2rtrn->setEncryptedAuthorizationString($authParameters->getId(), $authParameters->getData())) $checkToken=true;
        } catch (\Exception $e) {
            #die($e->getTraceAsString().PHP_EOL.PHP_EOL.$e->getMessage());
            $checkToken=true;
        }
        if(true==$checkToken){
            $token=$this->getTokenFromHeader();
            #die('is it ok? @'.__LINE__.': '.__FILE__);
    		if(!is_null($token)){
    		    #die('$token: '.$token.' @'.__LINE__.': '.__FILE__);
    			
    			#$auth2rtrn->setClosureForIdentityArray($this->getClosureForIdentityArray());
    			try{
    			    if(false==$auth2rtrn->setToken($token)){
    			        if($auth2rtrn->hasIdentity()) $auth2rtrn->clearIdentity();
    			    }
    				#die('token set done!!! @'.__LINE__.': '.__FILE__.PHP_EOL.PHP_EOL);
    			}catch(\Exception $e){
    			    #die($e->getMessage().'<pre>'.$e->getTraceAsString().PHP_EOL.PHP_EOL);
    				#$need2clear=true;
    				if($auth2rtrn->hasIdentity()) $auth2rtrn->clearIdentity();
    				#$auth2rtrn = new AuthenticationService($authStorage, $vldtblAuthAdptr, $usrTbl);
    			}
    		} # else $auth2rtrn = new AuthenticationService($authStorage, $vldtblAuthAdptr, $usrTbl);
        }
		#if(true==$need2clear && $auth2rtrn->hasIdentity()) $auth2rtrn->clearIdentity();
		#die('just stop here @'.__LINE__.': '.__FILE__);
		if($this->isPreflightCorsRequested()) $auth2rtrn->setAsPreflightCorsRequest();
		return $auth2rtrn;
	} */
}

