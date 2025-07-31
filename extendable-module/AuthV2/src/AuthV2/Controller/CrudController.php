<?php
namespace AuthV2\Controller;

#use Lib3rdParty\Zf2\Mvc\Controller\AbstractActionController;
#use Lib3rdParty\Zend\Mvc\Controller\AbstractActionController;
use Lib3rdParty\Mvc\Controller\AbstractCrudController;
use Lib3rdParty\Helper\Db\AbstractTableData;
use ProjectCore\User\LoggedInUserHandler;
#use Zend\Mvc\Controller\AbstractActionController;

class CrudController extends AbstractCrudController {
    use AuthV2RegisteredUserControllerTrait;
    private $userTable;
    /**
     * @return \Lib3rdParty\Authentication\TableUsedInCrudControllerInterface
     */
    private function getUserTable() {
        if(!isset($this->userTable)) $this->setUserTable($this->getServiceLocator()->get('AuthV2\\User\\UserTable'));
        return $this->userTable;
    }
    private function setUserTable(\Lib3rdParty\Authentication\TableUsedInCrudControllerInterface $userTable) {
        $this->userTable=$userTable;
        $this->userTable->setLoggedInUser($this->getLoggedInUser());
        return $this;
    }
    protected function getRouterName(){ return 'login-crud'; }
    protected function getModuleTitle(){ return 'Admin'; }
    protected function getPrimaryFieldName(){ return $this->getUserTable()->getIdentityColumn(); }
    private $tgUser;
    /**
     * {@inheritDoc}
     * @see \Lib3rdParty\Mvc\Controller\AbstractCrudController::getThisTg()
     */
    protected function getThisTg(){
        if(!isset($this->tgUser)) $this->tgUser=$this->getUserTable()->getTableGateway();
        return $this->tgUser;
    }
    protected function getBaseCondition() { return $this->getUserTable()->getBaseCondition(); }
    protected function getCreateUpdateFormControlConfig(){
        #$cndn=$this->getBaseCondition();
        #$cndn['adminExistsValidatorOptions']=$this->getLoggedInUser()->getAdminExistsValidatorOptions();
        #$cndn['handlerAdminTextId']=$this->getLoggedInUser()->getTextId();
        #$cndn['adapter']=$this->getUserTable()->getAdapter();
        $cndn['updating']=$this->isUpdating();
        $cndn['updatingData']=($this->isUpdating() ? $this->getSelectedRow()->getArrayCopy() : array());
        #$cndn['id']=($this->isUpdating() ? $this->getSelectedRow()->id : null);
        #if($this->isUpdating()) $cndn['handlerAdminTextId']=$this->getSelectedRow()->getHandlerAdminTextId();
        #$cndn['requiredFields']['qmsProductImageData']=true;
        #$cndn['requiredFields']['qmsProductImageData']=(($this->isUpdating() && $this->getSelectedRow()->isPrimaryImageOriginalImageExists()) ? false : true);
        return $this->getUserTable()->getCreateUpdateFormControlConfig($cndn);
    }
    protected function fixPreUpdateFormData(AbstractTableData $selected, \Zend\Form\Form $form, array $postData=array()){
        #die('@'.__LINE__.': '.__FILE__.'<pre>'.get_class($form));
        try {
            parent::fixPreUpdateFormData($selected, $form, $postData);
        } catch (\Exception $e) {
        }
        $this->getUserTable()->fixPreUpdateFormData($selected, $form);
        return $this;
    }
    protected function getPreCreateFilteredArray(array $arrayToExchange, array $postData){
        return $this->getUserTable()->getPreCreateFilteredArray(parent::getPreCreateFilteredArray($arrayToExchange, $postData), $postData);
    }
    protected function getPreUpdateFilteredArray(array $arrayToExchange, array $postData){
        return $this->getUserTable()->getPreUpdateFilteredArray(parent::getPreUpdateFilteredArray($arrayToExchange, $postData), $postData);
    }
    protected function isDashboardEnabled() { return true; }
    private function isCrudAllowed(){
        return $this->getUserTable()->isAdminCrudAllowed($this->getLoggedInUser());
    }
    protected function isListAllowed($directRequest=null, $restRequest=null){ return $this->isCrudAllowed(); }
    protected function isCreateAllowed($directRequest=null, $restRequest=null){ return $this->isCrudAllowed(); }
    protected function isReadAllowed($directRequest=null, $restRequest=null){ return $this->isCrudAllowed(); }
    protected function isUpdateAllowed($directRequest=null, $restRequest=null){ return $this->isCrudAllowed(); }
    protected function isDeleteAllowed($directRequest=null, $restRequest=null){ return $this->isCrudAllowed(); }
    public function indexAction() {
        $loggedInUser=$this->getLoggedInUser()->markAsNotDeletable();
        $this->setSelectedRow($loggedInUser);
        $isCrudAllowed=$this->isCrudAllowed();
        if(!is_null($this->getQueryAsArray('cmd')) && 'update'==$this->getQueryAsArray('cmd')) {
            $this->setAsUpdating();
            if($this->getRequest()->isPost()){
                $postData=$this->getRequest()->getPost()->toArray();
                if(!array_key_exists($this->getUserTable()->getCredentialColumn(), $postData)){
                    $this->flashMessenger()->addErrorMessage('Password NOT Provided, Unable to Update Profile.');
                    return $this->redirect()->toRoute('login-crud', array(), array('query'=>array('cmd'=>'update')));
                }
                if(strlen($postData[$this->getUserTable()->getCredentialColumn()])<=0){
                    $this->flashMessenger()->addErrorMessage('Empty Password!!! Unable to Update Profile.');
                    return $this->redirect()->toRoute('login-crud', array(), array('query'=>array('cmd'=>'update')));
                }
                $clsr2check=$this->getUserTable()->getPasswordCheckingClosure();
                if(false==$clsr2check($loggedInUser->getArrayCopy(), $postData[$this->getUserTable()->getCredentialColumn()])){
                    $this->flashMessenger()->addErrorMessage('Password NOT Matched!!! Unable to Update Profile.');
                    return $this->redirect()->toRoute('login-crud', array(), array('query'=>array('cmd'=>'update')));
                }
            }
            $usrTblClosure=$this->getUserTable()->getCreateUpdateCurdCrontrollerClosure($loggedInUser);
            return $this->createUpdate($loggedInUser, function($action, $param1=null, $param2=null, $param3=null, $param4=null)use($usrTblClosure, $isCrudAllowed){
                if('post-create-update-redirect'==$action){
                    #die('@'.__LINE__.': '.__FILE__);
                    return $param2->toRoute('login-crud');
                }
                if('cancel-button'==$action) return ' <a href="'.$param2->fromRoute('login-crud').'" class="btn btn-default">Cancel</a>';
                if('back2list'==$action) return (true==$isCrudAllowed ? ' <a href="'.$param2->fromRoute('login-crud', array('action'=>'search')).'" class="btn btn-default btn-info"><span class="glyphicon glyphicon-list"></span> Users</a>' : '');
                if('dashboard-link'==$action) return '';
                if('back-links'==$action) return '';
                return $usrTblClosure($action, $param1, $param2, $param3, $param4);
            });
        }
        return $this->readDelete($loggedInUser, false, function($action, $param1=null, $param2=null, $param3=null, $param4=null)use($isCrudAllowed){
            if('create-link'==$action && false==$isCrudAllowed) return '';
            if('back2list'==$action) return (true==$isCrudAllowed ? ' <a href="'.$param2->fromRoute('login-crud', array('action'=>'search')).'" class="btn btn-default btn-info"><span class="glyphicon glyphicon-list"></span> Users</a>' : '');
            if('update-link'==$action) return ' <a href="'.$param2->fromRoute('login-crud', array(), array('query'=>array('cmd'=>'update'))).'" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span> Update</a>';
            if('dashboard-link'==$action) return ' <a href="'.$param2->fromRoute('login-crud', array('action'=>'change-password')).'" class="btn btn-default btn-warning"><span class="icon_key"></span> Change Password</a>';
            if('back-links'==$action) return '';
            return $param1;
        });
    }
    public function changePasswordAction(){
        $loggedInUser=$this->getLoggedInUser()->markAsNotDeletable();
        $this->setSelectedRow($loggedInUser);
        $cntnt=$this->getHeaderHtml('Change Password');
        $cntnt.='<form method="post">
<p class="row"><label class="col-lg-2 col-md-2" for="pass">Password: </label><span class="col-lg-4 col-md-4"><input type="password" name="oPass" id="pass" class="col-lg-12 col-md-12" /></span></p>
<p class="row"><label class="col-lg-2 col-md-2" for="npass">New Password: </label><span class="col-lg-4 col-md-4"><input type="password" name="nPass" id="npass" class="col-lg-12 col-md-12" /></p>
<p class="row"><label class="col-lg-2 col-md-2" for="cpass">Confirm Password: </label><span class="col-lg-4 col-md-4"><input type="password" name="cPass" id="cpass" class="col-lg-12 col-md-12" /></span></p>
<p class="row"><label class="col-lg-2 col-md-2">&nbsp;</label><span class="col-lg-2 col-md-2"><button class="col-lg-12 col-md-12 btn btn-default btn-info">Change</button></span><span class="col-lg-4 col-md-4"><a href="'.$this->url()->fromRoute('login-crud').'" class="btn btn-default">Cancel</a></span></p>
</form>';
        if($this->getRequest()->isPost()){
            $anyErrorOccured=false;
            $postData=$this->getRequest()->getPost()->toArray();
            $fields=array('oPass'=>'Password', 'nPass'=>'New Password', 'cPass'=>'Confirm Password');
            foreach($fields as $fld=>$fName){
                if(!array_key_exists($fld, $postData) || strlen(trim($postData[$fld]))<=0){
                    $anyErrorOccured=true;
                    $this->flashMessenger()->addErrorMessage($fName.' Empty OR NOT Set!!!');
                }
            }
            if(false==$anyErrorOccured){
                if($postData['nPass']!=$postData['cPass']){
                    $anyErrorOccured=true;
                    $this->flashMessenger()->addErrorMessage('New Password and Confirm Password Mismatched!!!');
                }
            }
            $clsr2check=$this->getUserTable()->getPasswordCheckingClosure();
            if(array_key_exists('oPass', $postData) && strlen(trim($postData['oPass']))>0 && false==$clsr2check($loggedInUser->getArrayCopy(), $postData['oPass'])){
                $anyErrorOccured=true;
                $this->flashMessenger()->addErrorMessage('Current Password NOT Matched!!! Unable to change.');
            }
            if(true==$anyErrorOccured) return $this->redirect()->toRoute('login-crud', array('action'=>'change-password'));
            $this->getUserTable()->changePassword($loggedInUser, $postData['nPass']);
            $this->flashMessenger()->addSuccessMessage('Password Changed');
            return $this->redirect()->toRoute('login-crud');
        }
        return $this->getLayoutResponse($cntnt);
    }
}

