<?php
namespace AuthV2\Api;


use ProjectCore\User\LoggedInUserInterface;
use Zend\Authentication\AuthenticationServiceInterface;

/**
 * \AuthV2\Api\AuthV2LoggedInUserHandler
 * @author shkhan
 *
 */
class AuthV2LoggedInUserHandler {
    private static $objSelf;
    public static function getOnce(AuthenticationServiceInterface $pAuthService=null){
        if(false==self::isExists()) self::$objSelf=new static($pAuthService);
        return self::$objSelf;
        
    }
    public static function isExists(){ return isset(self::$objSelf); }
    private $authService;
    function __construct(AuthenticationServiceInterface $loggedInUserHandler=null) {
        $this->authService=$loggedInUserHandler;
    }
    /**
     * @return AuthenticationServiceInterface
     * @return \Lib3rdParty\Authentication\AnonymousServiceProviderInterface
     * @return \Lib3rdParty\Zend\Authentication\AuthenticationService
     */
    private function getAuthenticationService(){ return $this->authService; }
    public function isRegisteredOrAnonymousUserLoggedIn(){ return $this->getAuthenticationService()->isRegisteredOrAnonymousUserLoggedIn(); }
}

