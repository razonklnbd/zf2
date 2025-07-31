<?php
namespace AuthV2\Controller;

trait AuthV2RegisteredUserControllerTrait {
    /**
     * @var \ProjectCore\User\LoggedInUserHandler
     */
    private $loggedInUserHandler;
    public function __construct(\ProjectCore\User\LoggedInUserHandler $loggedInUserHandler) {
        $this->loggedInUserHandler=$loggedInUserHandler;
    }
    /**
     * @throws \Exception
     * @return \Lib3rdParty\Authentication\AuthenticatedUserInterface|\Lib3rdParty\Helper\Db\AbstractTableData
     */
    protected function getLoggedInUser() {
        if(false==$this->loggedInUserHandler->isRegisteredUserLoggedIn()) throw new \Exception('User NOT Logged In');
        return $this->loggedInUserHandler->getUserObject();
    }
}

