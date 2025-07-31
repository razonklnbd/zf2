<?php

namespace AuthV2\Model;

use Lib3rdParty\Helper\Db\AbstractTableData;

/**
 * \AuthV2\Model\UserX
 * @author shkhan
 *
 */
class UserX extends AbstractTableData implements \ProjectCore\User\AuthenticatedUserInterface {
    protected function getReadOnlyFields(){ return array(\AuthV2\AuthV2BaseModule::TBLSTRU_COL__ID, \AuthV2\AuthV2BaseModule::TBLSTRU_COL__PASS); }
    public static function getMyName(){ return \AuthV2\AuthV2BaseModule::TBLSTRU_NAME; }
    function getFirstName(){ return $this->getName(); }
    function getLastName(){ return ''; }
    function getTextId(){ return $this->getSelectedData(\AuthV2\AuthV2BaseModule::TBLSTRU_COL__ID); }
    function getName(){ return $this->getTextId(); }
    function getEmailAddress(){ return 'nobody__'.$this->getTextId(); }
    function getCredential(){ return $this->getSelectedData(\AuthV2\AuthV2BaseModule::TBLSTRU_COL__PASS); }
    public function getEncryptedPassword($pPlainPassToEncypt){ return md5($pPlainPassToEncypt); }
}
