<?php
namespace AuthV2\Model;


/**
 * \AuthV2\Model\AuthenticationUserTableX
 * @deprecated
 * @author shkhan
 *        
 */
class AuthenticationUserTableX extends \Lib3rdParty\Helper\Db\Table implements \Lib3rdParty\Authentication\TableInterface {
    public static function getMyTableName(){ return UserX::getMyName(); }
    public function getIdentityColumn(){ return \AuthV2\AuthV2BaseModule::TBLSTRU_COL__ID; }
    public function getCredentialColumn(){ return \AuthV2\AuthV2BaseModule::TBLSTRU_COL__PASS; }
    /**
     * @return UserX
     * {@inheritDoc}
     * @see \Lib3rdParty\Authentication\TableInterface::getUserToLogin()
     */
    public function getUserToLogin($pId){
        $cndn=array($this->getIdentityColumn()=>$pId);
        $rs=$this->getTableGateway()->select($cndn);
        if($rs->count()<=0) throw new \Exception('user NOT found!');
        return $rs->current();
    }
}

