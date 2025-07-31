<?php
namespace AuthV2\Factory\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AuthV2\Controller\RacsIndexController;

class RacsIndexControllerServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
	    $loginHandler=$serviceLocator->getServiceLocator()->get('AuthV2\\UserLoginHandler');
	    /** @var \ProjectCore\User\LoggedInUserHandler $loginHandler **/
	    if(empty($loginHandler)) throw new \Exception('Unable to find login handler!!!');
	    if(false==$loginHandler->isRegisteredUserLoggedIn()) throw new \Exception('User NOT Logged In');
		#die('i am in factory : ');
	    return new RacsIndexController($loginHandler);
	}
}

