<?php
namespace AuthV2\Controller;

use Lib3rdParty\Soap\TfwSoapRequestHandler;
use Lib3rdParty\Zend\Authentication\AuthenticationService;
use Lib3rdParty\Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AuthenticationController extends AbstractActionController {

	/**
	 * @var \Lib3rdParty\Authentication\SystemOwnerInterface
	 */
	private $curCompany;
	private function getCurrentActiveCompany(){
		if(!isset($this->curCompany)){
                    $this->curCompany=$this->getServiceLocator()->get('AuthV2\\ActiveCompany');
                    if(!($this->curCompany instanceof \Lib3rdParty\Authentication\SystemOwnerInterface)) throw new \Exception('please provide system owner company as \\Lib3rdParty\\Authentication\\SystemOwnerInterface provided '.get_class($this->curCompany));
                }
		return $this->curCompany;
	}
	/**
	 * @var \Lib3rdParty\Authentication\ContentTrackerInterface
	 */
	private $wmsContentTracker;
	private function getContentTracker(){
		if(!isset($this->wmsContentTracker)){
			if(!$this->getServiceLocator()->has('AuthV2\\ContentTracker')) throw new \Exception('content tracker NOT exists!');
			$this->wmsContentTracker=$this->getServiceLocator()->get('AuthV2\\ContentTracker');
		}
		return $this->wmsContentTracker;
	}
	private function isContentTrackerExists(){
		if(isset($this->wmsContentTracker)) return true;
		return $this->getServiceLocator()->has('AuthV2\\ContentTracker');
	}
	/**
	 * @var AuthenticationService
	 */
	protected $authService;
	//we will inject authService via factory
	public function __construct(AuthenticationService $authService){ $this->authService = $authService; }
	/*private $userTable;
	public function getUserTable() {
		if (!$this->userTable) $this->userTable = $this->getServiceLocator()->get('Registration\Model\UserTable');
		return $this->userTable;
	}*/
	private $anonymousLoginFl;
	private function isAnonymousLoginEnabled(){
	    if(!isset($this->anonymousLoginFl)){
	        try {
	            $this->anonymousLoginFl=$this->getCurrentActiveCompany()->isAnonymousLoginEnabled();
	        } catch (\Exception $e) {
	            $this->anonymousLoginFl=true;
	        }
	    }
		return $this->anonymousLoginFl;
	}
	private $anonUserInfoNeedMailToAdmin;
	private function isAnonUserInfoNeedMailToAdmin(){
	    if(!isset($this->anonUserInfoNeedMailToAdmin)){
	        try {
	            $this->anonUserInfoNeedMailToAdmin=$this->getCurrentActiveCompany()->isAnonUserInfoNeedMailToAdmin();
	        } catch (\Exception $e) {
	            $this->anonUserInfoNeedMailToAdmin=false;
	        }
	    }
		return $this->anonUserInfoNeedMailToAdmin;
	}

	/**
	private $configData;
	 * ..
	 * forgotPasswordUrl<br />
	 * urlAfterLoginSuccess<br />
	 * login -> mailAnonUserInfoToAdmin
	private function getConfigData(){
		if(!isset($this->configData)) $this->configData=$this->getServiceLocator()->get('Config');
		return $this->configData;
	}
	private $loginConfigData;
	private function getLoginConfigData(){
		if(!isset($this->loginConfigData)){
			$config=$this->getConfigData();
			$this->loginConfigData=array();
			if(array_key_exists('login', $config)) $this->loginConfigData=$config['login'];
		}
		return $this->loginConfigData;
	}
	 */
        private $forgotPasswordLink;
        private function getForgotPasswordLink(){
            if(!isset($this->forgotPasswordLink)){
                if(!$this->getServiceLocator()->has('AuthV2\\ForgotPasswordLink')) throw new \Exception('forgot password link not provided...');
                $frgtPassLnk=$this->getServiceLocator()->get('AuthV2\\ForgotPasswordLink');
                if(empty($frgtPassLnk)) throw new \Exception('forgot password link empty...');
                $this->forgotPasswordLink=$frgtPassLnk;
            }
            return $this->forgotPasswordLink;
        }
        private $registrationLink;
        private function getRegistrationLink(){
            if(!isset($this->registrationLink)){
                if(!$this->getServiceLocator()->has('AuthV2\\RegistrationLink')) throw new \Exception('registration link not provided...');
                $frgtPassLnk=$this->getServiceLocator()->get('AuthV2\\RegistrationLink');
                if(empty($frgtPassLnk)) throw new \Exception('registration link empty...');
                $this->registrationLink=$frgtPassLnk;
            }
            return $this->registrationLink;
        }
	public function ajaxPingAction(){
		return new JsonModel(array('status'=>'PONG'));
	}

	public function indexAction() {
		/* echo 'begin: '.REQUEST_MICROTIME.' @'.__LINE__.'<br />';
		die('@'.__LINE__.': '.__FILE__); */
#/*
		$request = $this->getRequest();
		$server  = $request->getServer();
		#if($this->requestHasContentTypeX($request)) { Debug::dump($request->getHeaders()->get('content-type')); exit(); }
		#if($request->isXmlHttpRequest()) die('yes here is isXmlHttpRequest()');
		if ($request->isXmlHttpRequest()){ #$this->requestHasContentTypeX($request)) {
			#die('yes this is isXmlHttpRequest()');
			$viewModel = new JsonModel();
		}else{
			$viewModel = new ViewModel();
		}
		#die('... '.get_class($viewModel).' @'.__LINE__.': '.__FILE__);
		$viewModel->setVariable('canonical', strval(\ProjectCore\AbstractModuleBase::getCurrentUrl($this->getRequest())));
		$viewModel->setVariable('whoAmI', get_class($viewModel));
#*/
		#$viewModel = new ViewModel();
/*
		$config = $this->getConfigData();
		$getUrlFronConfig = function($pConfig){
			$testClosure = function(){};
			return (($pConfig instanceof $testClosure)?$pConfig($this->plugin('url')):$pConfig);
		};
		if(array_key_exists('forgotPasswordUrl', $config)) $forgotPasswordUrl=$getUrlFronConfig($config['forgotPasswordUrl']);
		if(array_key_exists('login_config', $config) && array_key_exists('urls', $config['login_config'])){
			#die('<pre>'.print_r($config['login_config'], true));
			if(array_key_exists('forgot', $config['login_config']['urls'])) $forgotPasswordUrl=$getUrlFronConfig($config['login_config']['urls']['forgot']);
			if(array_key_exists('registration', $config['login_config']['urls'])) $registrationUrl=$getUrlFronConfig($config['login_config']['urls']['registration']);
		}
		if(isset($forgotPasswordUrl)) $viewModel->setVariable('forgotPasswordUrl', $forgotPasswordUrl);
		if(isset($registrationUrl)) $viewModel->setVariable('registrationUrl', $registrationUrl);
                # */
                try{
                    $viewModel->setVariable('forgotPasswordUrl', $this->getForgotPasswordLink());
                } catch (\Exception $ex) {

                }
                try{
                    $viewModel->setVariable('registrationUrl', $this->getRegistrationLink());
                } catch (\Exception $ex) {

                }
        try {
            $viewModel->setVariable('activeCompany', $this->getCurrentActiveCompany());
        } catch (\Exception $e) {
        }
		#echo 'current: '.microtime(true).' @'.__LINE__.'<br />';
        $sessSaveHandler=$this->authService->getStorage()->getSessionManager()->getSaveHandler();
        if(is_null($sessSaveHandler) || !is_object($sessSaveHandler)){
            $this->flashMessenger()->addErrorMessage('Session save handler NOT found!!! Unable to proceed for authentication');
            return $this->redirect()->toRoute('login', array('action' => 'invalid-session'));
        }
        if ($sessSaveHandler->read($this->authService->getStorage()->getSessionId()) && $this->authService->hasIdentity()) {
                    #die('@'.__LINE__.': '.$this->authService->getStorage()->getSessionManager()->getSaveHandler()->read($this->authService->getStorage()->getSessionId()));
			//redirect to success controller...
			return $this->redirect()->toRoute('login', array('action' => 'welcome'));
		}
		#echo 'current: '.microtime(true).' @'.__LINE__.'<br />';
		if($this->authService->isAnonymousUserLoggedIn()){
                    #die('anon logged in!!!!!! @'.__LINE__.': '.__FILE__);
			if($this->isAnonymousLoginEnabled()){
				return $this->redirect()->toRoute('login', array('action' => 'anonymous-login'));
			}
			$this->authService->clearIdentity();
		}
		#echo 'current: '.microtime(true).' @'.__LINE__.'<br />';
		$viewModel->setVariable('anonymousLoginEnabled', $this->isAnonymousLoginEnabled());


		#echo 'current: '.microtime(true).' @'.__LINE__.'<br />';
		$form = $this->getServiceLocator()->get('FormElementManager')->get('AuthV2\Form\LoginForm');
		//initialize error...
		$fpMessage='';
		$fpMessageX=$this->flashMessenger()->getMessages();
		if(!empty($fpMessageX)) $fpMessage=reset($fpMessageX);
		$viewModel->setVariable('error', $fpMessage);

		//authentication block...
		$authResponse=$this->authenticate($form, $viewModel);
		if($authResponse instanceof Response) return $authResponse;
 
		$viewModel->setVariable('form', $form);
		#$this->layout()->setVariable('withLeftMenu', false);
		#echo 'current: '.microtime(true).' @'.__LINE__.'<br />';
		return $viewModel;
	}
	 
	/** this function called by indexAction to reduce complexity of function */
	private function authenticate($form, $viewModel) {
	    $debug=false;
		if(true==$debug) echo 'current: '.microtime(true).' @'.__LINE__.'<br />'.PHP_EOL;
		$request = $this->getRequest();
                $myurl=$this->plugin('url');
                $urlAfterLoginSuccess=$this->getUrlAfterLoginSuccess($myurl->fromRoute('login', array(
                            'action' => 'welcome',
                    ), array('force_canonical' => true)));
		#$request = $this->getRequest();
		$getQuery = $request->getUri()->getQueryAsArray();
		if(array_key_exists('url-after-login-success', $getQuery)){
		    $urlAfterLoginSuccess=urldecode($getQuery['url-after-login-success']);
		    $this->authService->setAfterLoginUrl($urlAfterLoginSuccess);
		}
		if ($request->isPost()) {
		    if(true==$debug) echo 'current: '.microtime(true).' @'.__LINE__.'<br />'.PHP_EOL;
			$form->setData($request->getPost());
			if ($form->isValid()) {
			    if(true==$debug) echo 'current: '.microtime(true).' @'.__LINE__.'<br />'.PHP_EOL;
				$dataform = $form->getData();
				#var_dump($dataform); exit();
				 
				#$this->authService->getAdapter()->setIdentity($dataform['loginId'])->setCredential($dataform['loginPass']);
				#echo 'get_class($this->authService->authenticate()): '.get_class($this->authService->authenticate()); exit();
				if ($this->authService->isValid($dataform['loginId'], $dataform['loginPass'])) {
				    if(true==$debug) echo 'current: '.microtime(true).' @'.__LINE__.'<br />'.PHP_EOL;
					//authentication success
					/*$getQuery = $request->getUri()->getQueryAsArray();
					if(array_key_exists('url-after-login-success', $getQuery)){
						$urlAfterLoginSuccess=$getQuery['url-after-login-success'];
						$this->authService->setAfterLoginUrl($getQuery['url-after-login-success']);
					}*/
					#die('logout url: '.$myurl->fromRoute('login', array('action' => 'logout'), array('force_canonical' => true)));
					$this->authService->setLogoutUrl($myurl->fromRoute('login', array('action' => 'logout'), array('force_canonical' => true)));
					$this->authService->setLoggedIn($request->getServer('REMOTE_ADDR'), $request->getServer('HTTP_USER_AGENT'));
					if($this->isContentTrackerExists()) $this->getContentTracker()->loggedInAsRegisteredUser($dataform['loginId']);

					#if(empty($urlAfterLoginSuccess)) $urlAfterLoginSuccess=$myurl->fromRoute('login', array('action' => 'welcome'), array('force_canonical' => true));
					#$config = $this->getServiceLocator()->get('Config');
					#if(!empty($config['urlAfterLoginSuccess'])) $urlAfterLoginSuccess=(self::isClosure($config['urlAfterLoginSuccess'])?$config['urlAfterLoginSuccess']($myurl):trim($config['urlAfterLoginSuccess']));
					#echo $urlAfterLoginSuccess.'<pre>'.print_r($config['urlAfterLoginSuccess'], true); exit();
					#$urlAfterLoginSuccess=$this->authService->getAfterLoginUrl($urlAfterLoginSuccess);
					$this->authService->setAfterLoginUrl();
					try {
					    $postLoginRoutine=$this->getServiceLocator()->get('AuthV2\\PostLoginRoutine');
					    $postLoginRoutine($this->authService);
					} catch (\Exception $e) {
					    if(true==$debug) die('@'.__LINE__.': '.__FILE__.'<br />ERR: '.$e->getMessage().'<pre>'.$e->getTraceAsString().'</pre>');
					}
					if(true==$debug){
					    if ($this->authService->getStorage()->getSessionManager()->getSaveHandler()->read($this->authService->getStorage()->getSessionId()) && $this->authService->hasIdentity()) echo 'logged in sess id: '.$this->authService->getSessionId().' @'.__LINE__.': '.__FILE__.'<br />'.PHP_EOL;
					    die('current: '.microtime(true).' @'.__LINE__.'<br />'.$urlAfterLoginSuccess);
					}
					#$urlAfterLoginSuccess='/favicon.ico';
					/* if(strpos($urlAfterLoginSuccess, 'favicon')!==false || strpos($urlAfterLoginSuccess, 'xavicon')!==false){
					    $bkTrace=function ($stack) {
					        $output = '';
					        
					        $stackLen = count($stack);
					        for ($i = 1; $i < $stackLen; $i++) {
					            $entry = $stack[$i];
					            
					            $func = (array_key_exists('class', $entry)?$entry['class'].'\\':'').$entry['function'] . '(';
					            $argsLen = count($entry['args']);
					            for ($j = 0; $j < $argsLen; $j++) {
					                $my_entry = $entry['args'][$j];
					                if (is_string($my_entry)) {
					                    $func .= $my_entry;
					                }
					                if ($j < $argsLen - 1) $func .= ', ';
					            }
					            $func .= ')';
					            
					            $entry_file = 'NO_FILE';
					            if (array_key_exists('file', $entry)) {
					                $entry_file = $entry['file'];
					            }
					            $entry_line = 'NO_LINE';
					            if (array_key_exists('line', $entry)) {
					                $entry_line = $entry['line'];
					            }
					            $output .= $entry_file . ':' . $entry_line . ' - ' . $func . PHP_EOL;
					        }
					        return $output;
					    };
					    echo '<pre>'.$bkTrace(debug_backtrace());
					    die('@'.__LINE__.': urlAfterLoginSuccess: '.$urlAfterLoginSuccess.' | '.__FILE__);
					} */
					return $this->plugin('redirect')->toUrl($urlAfterLoginSuccess);
				} else {
				    if(true==$debug) echo 'current: '.microtime(true).' @'.__LINE__.'<br />'.PHP_EOL;
					$viewModel->setVariable('error', 'Email or password did not match. Please try again.');
				}
			}elseif(true==$debug) die('form NOT valid! @'.__LINE__.': '.__FILE__);
		}
		return $this;
	}
	static function isClosure($arg) {
		$test = function(){};
		return $arg instanceof $test;
	}
	private function redir2home(){
		return $this->redirect()->toRoute('home');
	}
	public function invalidSessionAction() {
	    return new ViewModel(array('authService'=>$this->authService));
	}
    public function welcomeAction() {
    	if($this->authService->isAnonymousUserLoggedIn()){
    		return $this->redir2home();
    	}
    	if(!$this->authService->getStorage()->getSessionManager()->getSaveHandler()->read($this->authService->getStorage()->getSessionId())) {
    		//redirect to success controller...
    		return $this->redirect()->toRoute('login');
    	}
		return $this->redir2home();
    	#die('welcome buddy!');
    	#$this->authService->makeThisActionRestricted($this, false);
    	#return $this->redir2home();
    	#if(!$this->authService->hasIdentity()) return $this->redirect()->toRoute('login');
		return new ViewModel(array('user'=>$this->authService->getIdentity(), 'authService'=>$this->authService));
    }
	public function anonymousLoginAction(){
		if ($this->authService->getStorage()->getSessionManager()->getSaveHandler()->read($this->authService->getStorage()->getSessionId()) && $this->authService->hasIdentity()) {
			//redirect to success controller...
			return $this->redirect()->toRoute('login', array('action' => 'welcome'));
		}
		if(!$this->isAnonymousLoginEnabled()){
			if($this->authService->isAnonymousUserLoggedIn()) $this->authService->clearIdentity();
			return $this->redirect()->toRoute('login');
		}
		#die('url: '.$this->authService->getAfterLoginUrl().' @'.__LINE__.': '.__FILE__);

		$arr2pass=array();
		if($this->authService->isAnonymousUserLoggedIn()){
			#$arr2pass['identity']=$this->authService->getAnonymousIdentity();
			return $this->redir2home();
		}else{
                    /*
			$config = $this->getConfigData();
			$getUrlFronConfig = function($pConfig){
				$testClosure = function(){};
				return (($pConfig instanceof $testClosure)?$pConfig($this->plugin('url')):$pConfig);
			};
			if(array_key_exists('login_config', $config) && array_key_exists('urls', $config['login_config'])){
				#die('<pre>'.print_r($config['login_config'], true));
				if(array_key_exists('registration', $config['login_config']['urls'])) $registrationUrl=$getUrlFronConfig($config['login_config']['urls']['registration']);
			}
			if(isset($registrationUrl)) $arr2pass['registrationUrl']=$registrationUrl;
                        # */
                        try{
                            $arr2pass['registrationUrl']=$this->getRegistrationLink();
                        } catch (\Exception $ex) {

                        }
			$myurl=$this->plugin('url');
			$urlAfterLoginSuccess=$this->getUrlAfterLoginSuccess($myurl->fromRoute('login', array(
					'action' => 'anonymous-login',
				), array('force_canonical' => true)));
			$form = $this->getServiceLocator()->get('FormElementManager')->get(current(explode('\\', __NAMESPACE__)).'\\Form\\AnonymousForm');
			/** @var \TfwAuth\Form\AnonymousForm $form **/
			//initialize error...
			$fpMessage='';
			$fpMessageX=$this->flashMessenger()->getMessages();
			if(!empty($fpMessageX)) $fpMessage=reset($fpMessageX);
			$arr2pass['error']=$fpMessage;
			$request = $this->getRequest();
			if ($request->isPost()) {
				$postData=$request->getPost()->toArray();
				$form->setData($postData);
				if ($form->isValid()) {
					#die('valid @'.__LINE__.': '.__FILE__);
					$dataform = $form->getData();
					if($this->authService->isIdentityExists($dataform['userEmail'])){
						#die('valid @'.__LINE__.': '.__FILE__);
						#$arr2pass['error']='Email ('.$dataform['userEmail'].') are using by our respected user. If you forgot your password, you can request it to reset from our system.';
						$arr2pass['error']='Account with this email address already exists.  Please login or provide a different email address.';
					}else{
						#die('valid @'.__LINE__.': '.__FILE__);
						//authentication success
						$this->authService->setLogoutUrl($myurl->fromRoute('login', array('action' => 'logout'), array('force_canonical' => true)));
						$this->authService->setAnonymousUserLoggedIn($dataform['userFirstName'], $dataform['userLastName'], $dataform['userEmail']);
						if($this->isContentTrackerExists()) $this->getContentTracker()->loggedInAsGuestUser($dataform['userEmail'], $dataform['userFirstName'], $dataform['userLastName']);
						if($this->isAnonUserInfoNeedMailToAdmin()) $this->getCurrentActiveCompany()->sendAnonUserLoggedInNotice($dataform['userEmail'], $dataform['userFirstName'], $dataform['userLastName']);
						$this->authService->setAfterLoginUrl();
						return $this->plugin('redirect')->toUrl($urlAfterLoginSuccess);
					}
				}else{
					#foreach($form->getMessages() as $idx=>$elmnt) $data2show[$idx]=$form->get($idx)->getLabel().': '.implode(' | ', $elmnt);
					#if(!empty($data2show)) $arr2pass['error']=implode(' || ', $data2show);
					#$this->flashMessenger()->addMessage(implode(' || ', $data2show));
					#die('not valid! @'.__LINE__.': '.__FILE__.'<pre>'.print_r($data2show, true));
					#return $this->plugin('redirect')->toRoute('login', array('action'=>'anonymous-login'));
					#foreach($form as $elmnt){
						#$elmnt->setValue($postData[$elmnt->getName()]);
						#echo '$postData[$elmnt->getName()]: '.$postData[$elmnt->getName()].'<br />';
					#}
					#die('<pre>'.print_r($form->getElements(), true));
				}
			}
			$arr2pass['form']=$form;
		}
		return new ViewModel($arr2pass);
	}
	private function getUrlAfterLoginSuccess($pUrlAfterLoginSuccess=NULL){
            if($this->getServiceLocator()->has('AuthV2\\AfterLoginSuccessLink')){
                $data=$this->getServiceLocator()->has('AuthV2\\AfterLoginSuccessLink');
                if(!empty($data)) $pUrlAfterLoginSuccess=$data;
            }
            /*
		$config = $this->getConfigData();
		if(!empty($config['urlAfterLoginSuccess'])){
			$pUrlAfterLoginSuccess=(self::isClosure($config['urlAfterLoginSuccess'])?$config['urlAfterLoginSuccess']($this->plugin('url')):trim($config['urlAfterLoginSuccess']));
			#echo '$pUrlAfterLoginSuccess: '.$pUrlAfterLoginSuccess.'<br />';
		}
                # */
		$sessurl=$pUrlAfterLoginSuccess=$this->authService->getAfterLoginUrl($pUrlAfterLoginSuccess);
		$getQuery = $this->getRequest()->getUri()->getQueryAsArray();
		if(array_key_exists('url-after-login-success', $getQuery)) $url2compr=$pUrlAfterLoginSuccess=$getQuery['url-after-login-success'];
		$postData=$this->getRequest()->getPost()->getArrayCopy();
		if(array_key_exists('redir2url', $postData) && !empty($postData['redir2url'])){
			$url2compr=$pUrlAfterLoginSuccess=$postData['redir2url'];
			#echo 'post: '.$pUrlAfterLoginSuccess.'<br />';
		}
		#die('$url2compr: '.$url2compr.', $pUrlAfterLoginSuccess: '.$pUrlAfterLoginSuccess.', $sessurl: '.$sessurl.' @'.__LINE__.': '.__FILE__);
		if(isset($url2compr) && $sessurl!=$url2compr) $this->authService->setAfterLoginUrl($url2compr);
		return $pUrlAfterLoginSuccess;
	}
    public function logoutAction() {
    	#echo 'begin: '.REQUEST_MICROTIME.' @'.__LINE__.': '.__FILE__.'<br />';
    	#echo 'current: '.microtime(true).' @'.__LINE__.': '.__FILE__.'<br />';
    	$this->authService->clearIdentity();
    	#echo 'current: '.microtime(true).' @'.__LINE__.': '.__FILE__.'<br />';
    	if($this->isContentTrackerExists()) $this->getContentTracker()->notLoggedIn();
    	#echo 'current: '.microtime(true).' @'.__LINE__.': '.__FILE__.'<br />'; exit();
    	return $this->redirect()->toRoute('login');
    }
    public function toRegistrationAction() {
        $urlAfterRegCompleted=$this->getUrlAfterLoginSuccess();
        $getQuery = $this->getRequest()->getUri()->getQueryAsArray();
        if(array_key_exists('url-after-registration-completed', $getQuery)) $urlAfterRegCompleted=$getQuery['url-after-registration-completed'];
        #die('$urlAfterRegCompleted: '.$urlAfterRegCompleted.' @'.__LINE__.': '.__FILE__);
        $this->authService->setAfterLoginUrl($urlAfterRegCompleted);
        # todo registration url at config
        $regUrl=$this->getRegistrationLink();
        if(empty($regUrl) || '#'==$regUrl) throw new \Exception('registration url NOT set yet!!!');
        return $this->redirect()->toUrl($regUrl);
    }

    
    # http://wms.shopping.shahadat.web/login/password/shkhan/nabIl321
    /**
     * 
     * https://www.elementsearch.com/wvm-admin/login/password/shkhan/n@bIl321
     * 
     * http://wms.shopping.burtprocess.com/oacs/password/shkhan/n@bIl321
     * shopping.burtprocess.com[shkhan n@bIl321]: f7b35e877b4a6fdbe9164d4085225de31c95375419d0a7a0bdb17bf0ad2195ecMTIzNDU2Nzg5MDEyMzQ1NtarfWQwAMdiPiiBVJp5kWo=
     * su.shopping.shahadat.web[shkhan.su n@bIl321]: 1f04b777566a8b712030153fec01e840db72e283e076fc05602c260085b0a33fMTIzNDU2Nzg5MDEyMzQ1NnJuduqvegVoWs/6EhxRzDA=
     *
     * die('password: '.$this->getServiceLocator()->get('Oacs\Model\OacsUserTable')->getUserToLogin($this->params()->fromRoute('param1'))->getEncryptedPassword($this->params()->fromRoute('param2')));
     */
    public function passwordAction(){
        #die('schema: '.$this->getServiceLocator()->get('Oacs\Model\OacsUserTable')->getTableGateway()->getAdapter()->getDriver()->getConnection()->getCurrentSchema());
        #die('$this->getCompanyTextId(): '.$this->getCompanyTextId());
        try {
            die('$this->getCompanyTextId(): '.$this->getCurrentActiveCompany()->getTextId().' | password: '.$this->getServiceLocator()->get('AuthV2\\User\\UserTable')->getUserToLogin($this->params()->fromRoute('param1'))->getEncryptedPassword($this->params()->fromRoute('param2')));
        } catch (\Exception $e) {
            die('$e->getMessage(): '.$e->getMessage().' | password: '.$this->getServiceLocator()->get('AuthV2\\User\\UserTable')->getUserToLogin($this->params()->fromRoute('param1'))->getEncryptedPassword($this->params()->fromRoute('param2')));
        }
        return $this->redirect()->toRoute('login');
//         if($this->isNotLoggedIn()) return $this->redirect()->toRoute('login');
//         $managerLoggedIn=$this->getOacsUserTable()->isSetLoggedInManager();
//         if(false==$managerLoggedIn){
//             $this->flashMessenger()->addMessage('please login as manager to get password!');
//             return $this->redirect()->toRoute('oacs');
//         }
//         die('password: '.$this->getOacsUserTable()->getUserToLogin($this->params()->fromRoute('param1', $this->getOacsUserTable()->getLoggedInManager()->textId))->getEncryptedPassword($this->params()->fromRoute('param2', 'NULL')));
    }

    

    public function soapAction() {
    	$response = $this->getResponse();
    	$request = $this->getRequest();
    	switch ($request->getMethod()) {
    		case 'GET':
    			return $this->redirect()->toRoute('login');
    			break;
    		case 'POST':
    			$urlHndlr=$this->plugin('url');
    			$this->authService->setLogoutUrl($urlHndlr->fromRoute('login', array(
    					'action' => 'logout',
    				), array('force_canonical' => true)));
    			#$wsdlUrl=$urlHndlr->fromRoute('login', array('action' => 'soap',), array('force_canonical' => true));
    			$srh=new TfwSoapRequestHandler($urlHndlr->fromRoute('login', array('action' => 'soap',), array('force_canonical' => true)), $this->authService, $request);
    			#$soapServer = new SOAPServer();
    			#$soapServer->setUri($wsdlUrl); # 'uri' option is required in nonWSDL mode
    			#$spReqCntrlr=new TfwSoapRequestController();
    			#$spReqCntrlr->setRequestInfo($request);
    			#$this->authService->setSoapController($spReqCntrlr);
    			#$soapServer->setClass($this->authService);
    			#$request->getHeaders('myAppEncryptedSecretCode');
    			#if(true==$lgnStatus)
    			#$this->soap->setDebugMode(true);
    			#die('i have a server');
    			#try{
    				#$soapServer->setReturnResponse(true);
    				#$soapResponse = $soapServer->handle();
    				#if ($soapResponse instanceof \SoapFault) {
    					#$soapResponse = (string) $soapResponse;
    				#}
    				#$requestedHeaders=$request->getHeaders();
    				#$hdReply=' .. ';
    				#if($requestedHeaders->has('myAppEncryptedSecretCode')) $hdReply=$requestedHeaders->get('myAppEncryptedSecretCode')->getFieldValue();
    				#$response->getHeaders()->addHeaderLine('tstFromRazon', 'get back header [myAppEncryptedSecretCode] - '.$hdReply.' -- $lgnStatus - '.var_export($lgnStatus, true));
    			#}catch(\Exception $e){
    				#die('error in server... '.$e->getMessage());
    				#$soapResponse = (string) $soapResponse;
    			#}
    			#$response->getHeaders()->addHeaderLine('Content-Type', 'text/xml; charset=utf-8');
				#$response->setContent($srh->getSoapResponse());
    			$response=$srh->setSoapResponse($response);
    			break;
    		default:
    			$response->setStatusCode(405);
    			$response->getHeaders()->addHeaderLine('Allow', 'GET,POST');
    	}
    	return $response;
    }




}