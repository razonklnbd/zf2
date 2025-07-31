<?php
namespace AuthV2\Factory\Authentication;

use Zend\ServiceManager\ServiceLocatorInterface;
use AuthV2\Api\StatelessAuthService;

/**
 * @method \AuthV2\Api\StatelessAuthService __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
 * @author shkhan
 */
class StatelessAuthenticationServiceFactory extends AbstractAuthenticationServiceFactory {
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
    } */


}