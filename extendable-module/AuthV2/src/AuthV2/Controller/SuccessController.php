<?php
namespace AuthV2\Controller;

#use Lib3rdParty\Zf2\Mvc\Controller\AbstractActionController;
use Lib3rdParty\Zend\Mvc\Controller\AbstractActionController;
#use Zend\Mvc\Controller\AbstractActionController;

class SuccessController extends AbstractActionController {
    private $loggedInUserHandler;
    public function __construct(\ProjectCore\User\LoggedInUserHandler $loggedInUserHandler) {
        $this->loggedInUserHandler=$loggedInUserHandler;
    }
	public function indexAction() {
		return $this->getAcceptableViewModel(array('auth'=>$this->getServiceLocator()->get('AuthenticationService'), 'user'=>$this->loggedInUserHandler->getUserObject()));
	}
    
}

