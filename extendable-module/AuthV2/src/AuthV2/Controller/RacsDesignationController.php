<?php
namespace AuthV2\Controller;

use Lib3rdParty\Mvc\Controller\AbstractCrudController;
use Racs\Model\AccessLevel\Designation;
use Lib3rdParty\Helper\Db\AbstractTableData;

class RacsDesignationController extends AbstractCrudController {
    #protected function isDebugEnabled() { return true; }
    use AuthV2RegisteredUserControllerTrait;
    private $racsDesignationTable;
    /**
     * @return \Racs\Model\AccessLevel\DesignationTable
     */
    private function getRacsDesignationTable(){
        if(!isset($this->racsDesignationTable)) $this->racsDesignationTable=$this->getServiceLocator()->get('AuthV2\\RacsDesignationTableClosure');
        return $this->racsDesignationTable;
    }
    protected function getRouterName(){ return 'login-racs/designation'; }
    protected function getModuleTitle(){ return 'Designation'; }
    protected function getPrimaryFieldName(){ return 'textId'; }
    private $tgRacsDesignation;
    /**
     * {@inheritDoc}
     * @see \Lib3rdParty\Mvc\Controller\AbstractCrudController::getThisTg()
     */
    protected function getThisTg(){
        if(!isset($this->tgRacsDesignation)) $this->tgRacsDesignation=$this->getRacsDesignationTable()->getTableGateway();
        return $this->tgRacsDesignation;
    }
    private function getParentIdColumnName() { return 'id'; }
    private function getParentColumnName() { return 'parent'; }
    private function getParentParamName() { return 'parent'; }
    private function getNullParentId() { return 'null'; }
    private function getRootParentId() { return null; }
    private $selectedParent;
    /**
     * @throws \Exception
     * @return \Racs\Model\AccessLevel\Designation
     */
    private function getParent(){
        if(!isset($this->selectedParent)){
            $this->selectedParent=new Designation();
            if($this->isRootParent(true)){
                #die('@'.__LINE__.': '.__FILE__);
                try {
                    $this->selectedParent=$this->getRootParent();
                } catch (\Exception $e) {
                }
            }else{
                $pid=$this->params($this->getParentParamName());
                if(!empty($pid) && $pid!=$this->getNullParentId()) $this->selectedParent=$this->getParentX($pid);
            }
        }
        return $this->selectedParent;
    }
    /**
     * @throws \Exception
     * @return \Racs\Model\AccessLevel\Designation
     */
    private function getParentX($pid){
        if(empty($pid)) throw new \Exception('Parent Identifier Required!!!');
        $cndn=$this->getRacsDesignationTable()->getBaseCondition();
        $cndn[$this->getParentIdColumnName()]=$pid;
        #die('<pre>'.print_r($cndn, true));
        $rs=$this->getThisTg()->select($cndn);
        if($rs->count()<=0) throw new \Exception('Parent ['.$pid.'] NOT Found');
        return $rs->current();
    }
    private $rootParent;
    /**
     * @throws \Exception
     * @return \Racs\Model\AccessLevel\Designation
     */
    private function getRootParent(){
        if(!isset($this->rootParent)){
            $pid=$this->getRootParentId();
            if(empty($pid)) throw new \Exception('Root Parent Identifier NOT Provided!!!');
            $this->rootParent=$this->getParentX($pid);
        }
        return $this->rootParent;
    }
    private function isRootParent($ignoreCheckingDb=false){
        $pid=$this->params($this->getParentParamName());
        if(empty($pid) || $this->getNullParentId()==$pid) return true;
        if(is_null($this->getRootParentId())) return false;
        if(true==$ignoreCheckingDb) return $this->getRootParentId()==$pid;
        if(!$this->getParent()->isValid()) return true;
        $parent=$this->getParent()->getArrayCopy();
        return $parent[$this->getParentIdColumnName()]==$this->getRootParentId();
    }
    protected function getDefaultParamsAtRouter() {
        if($this->isRootParent()) return array($this->getParentParamName()=>$this->getNullParentId());
        $parent=$this->getParent()->getArrayCopy();
        return array($this->getParentParamName()=>$parent[$this->getParentIdColumnName()]);
    }
    protected function getBaseCondition() {
        $b2c=$this->getRacsDesignationTable()->getBaseCondition();
        $b2c[$this->getParentColumnName()]=($this->getParent()->isValid() ? $this->getParent()->getTextId(true) : null);
        return $b2c;
    }
    protected function getClosureForCustomLink(){
        $modTitle=$this->getModuleTitle();
        $routeName=$this->getRouterName();
        $pidColNm=$this->getParentIdColumnName();
        $prntParamName=$this->getParentParamName();
        return function($url, $id, $row) use($modTitle, $routeName, $pidColNm, $prntParamName){
            $data=$row->getArrayCopy();
            if(!array_key_exists($pidColNm, $data)) return array();
            return array(
                'Manage '.$modTitle=>$url($routeName, array($prntParamName=>$data[$pidColNm])),
            );
        };
    }
    protected function isUpdateAllowed($directRequest=null, $restRequest=null) { return false; }
    protected function getPageTitle($format) {
        $rtrn=parent::getPageTitle($format);
        if('create'==$this->getExecutingAction()) $rtrn.=' @'.($this->isRootParent() ? 'root' : '{'.$this->getParent()->getFullPath('&gt;').'}');
        return $rtrn;
    }
    protected function getHomeLinkHref(){ return $this->url()->fromRoute('login-racs'); }
    protected function getHomeLinkLabel(){ return 'Access Control'; }
    protected function getBreadcrumbElements($title=null) {
        $rtrn=parent::getBreadcrumbElements($title);
        if(!$this->isRootParent() && ('index'==$this->getExecutingAction() || 'search'==$this->getExecutingAction())){
            $nRtrn=array('home'=>$rtrn['home']);
            unset($rtrn['home']);
            if(array_key_exists('dashboard', $rtrn)){
                $nRtrn['dashboard']=$rtrn['dashboard'];
                unset($rtrn['dashboard']);
            }
            $routeName=$this->getRouterName();
            $nRtrn['list.root']='<li><a href="'.$this->url()->fromRoute($routeName).'"><i class="fa fa-list"></i>Root '.$this->getModuleTitle().'(s)</a></li>';
            try {
                $parents=$this->getParent()->getParentsArrayObject();
                $pidColNm=$this->getParentIdColumnName();
                $prntParamName=$this->getParentParamName();
                foreach($parents as $prnt){
                    $parent=new Designation();
                    $parent->exchangeArray($prnt);
                    $nRtrn['list.'.$prnt[$pidColNm]]='<li><a href="'.$this->url()->fromRoute($routeName, array($prntParamName=>$prnt[$pidColNm])).'"><i class="fa fa-list"></i>'.$parent->getTitle().'</a></li>';
                }
            } catch (\Exception $e) {
            }
            if(array_key_exists('list', $rtrn)) unset($rtrn['list']);
            unset($rtrn['tail']);
            if(!empty($rtrn)){
                foreach($rtrn as $k=>$li) $nRtrn[$k]=$li;
            }
            $nRtrn['tail']='<li><i class="fa fa-list"></i>'.$this->getModuleTitle().'(s) Under "'.$this->getParent()->getTitle().'"</li>';
            return $nRtrn;
        }
        return $rtrn;
    }
    protected function getCreateUpdateFormControlConfig() {
        $cndn=$this->getBaseCondition();
        #$cndn['adminExistsValidatorOptions']=$this->getLoggedInUser()->getAdminExistsValidatorOptions();
        #$cndn['handlerAdminTextId']=$this->getLoggedInUser()->getTextId();
        #$cndn['adapter']=$this->getUserTable()->getAdapter();
        $cndn['updating']=$this->isUpdating();
        $cndn['updatingData']=($this->isUpdating() ? $this->getSelectedRow()->getArrayCopy() : array());
        #$cndn['id']=($this->isUpdating() ? $this->getSelectedRow()->id : null);
        #if($this->isUpdating()) $cndn['handlerAdminTextId']=$this->getSelectedRow()->getHandlerAdminTextId();
        #$cndn['requiredFields']['qmsProductImageData']=true;
        #$cndn['requiredFields']['qmsProductImageData']=(($this->isUpdating() && $this->getSelectedRow()->isPrimaryImageOriginalImageExists()) ? false : true);
        $frmCnfg=$this->getRacsDesignationTable()->getCreateUpdateFormControlConfig($cndn);
        if(array_key_exists($this->getParentColumnName(), $frmCnfg) && ($this->isCreating() || $this->isUpdating())) unset($frmCnfg[$this->getParentColumnName()]);
        return $frmCnfg;
    }
    protected function getPreCreateUpdateFilteredPostData(array $pPostData, AbstractTableData $pObjData=null){
        /** nothing to execute here, need to inherit **/
        $rtrn=parent::getPreCreateUpdateFilteredPostData($pPostData, $pObjData);
        $rtrn[$this->getParentColumnName()]=($this->getParent()->isValid() ? $this->getParent()->getTextId(true) : null);
        return $rtrn;
    }
}

