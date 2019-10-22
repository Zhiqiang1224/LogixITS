<?php

/**
 * Controller  "Tools"
 *
 * @module     Signs
 * @category   Controller
 * @since      Class available since Release 2.0
 * @deprecated  --
 */
class Signs_ToolsController extends wd_Controller
{

    /**
     * Check for authorization  befor start the class
     * Init Ajax content
     * 
     * @module     Signs
     * @category   Init function 
     */
    public function init()
    {
        parent::init();
        if (isset($_SESSION['pwd_expired'])) {
            $this->_redirect('Auth/renew');
        }
        
        $this->_helper->AjaxContext()
                ->addActionContext('syncsign', 'json')
                ->addActionContext('getsignsscheduleslist', 'json')
                ->addActionContext('getscheduleslist', 'json')
                ->addActionContext('updatesettings', 'json')
                ->addActionContext('rebootsign', 'json')
                ->initContext('json');
        
        $this->_helper->AjaxContext()
                ->addActionContext('getalertslist', 'json')
                ->addActionContext('getcontactslist', 'json')
                ->addActionContext('savealert', 'json')
                ->addActionContext('createpublicpage', 'json')
                ->initContext('json');
        
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'index', 'default');
        }
         $this->translate = Zend_Registry::get('Zend_Translate');
    }
    
    public function publicpageAction()
    {
        $cid = Zend_Auth::getInstance()->getIdentity()->company_id;
        $this->view->locations = wd_API::getLocationsList(null, false, $cid);
    }
        
    public function deletepublicAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);       
        $id = (int)$this->getRequest()->getParam('id', 0);
        (new Application_Model_DbTable_PublicPage)->delete('id = ' . $id);
    }
    
    public function publicpagelistAction()
    {
        $publicPageModel = new Application_Model_DbTable_PublicPage;
        $this->view->pageModel = $publicPageModel;
        $this->view->data = $publicPageModel->getPagesForDisplaying();
        $this->view->host = $this->getRequest()->getHttpHost();
    }

    public function createpublicpageAction()
    {
        $cid = Zend_Auth::getInstance()->getIdentity()->company_id;
        $settings = $this->getRequest()->getPost();
        $publicPageModel = new Application_Model_DbTable_PublicPage;
        if ($publicPageModel->validate($settings)){
            $publicPageId = $publicPageModel->create($settings, $cid, 'signs');
            $relativeLink = $publicPageModel->createLink($publicPageId);
            $this->view->link = $relativeLink ? $this->getRequest()->getHttpHost() . $relativeLink : '';
        } else {
            $this->getResponse()->setHttpResponseCode(400);
            $this->view->errors = $publicPageModel->errors;
        }
    }
    
    /**
     * Show list tools
     * 
     * @module     Signs
     * @category   action
     */
    public function indexAction()
    {   
        if (Zend_Auth::getInstance()->getIdentity()->access_level < 3) {
	        $this->redirect('/');
        }
	
        $this->view->back_link = '/';
        
        $company_id = Zend_Auth::getInstance()->getIdentity()->company_id;
        $companies = new Application_Model_DbTable_Companies();
        
        if ($companies->companyExists($company_id)) {
            $companyModules = explode(',', $companies->getCompanyModules($company_id)[0]['modules']);
            if (count($companyModules)>1) {
                $this->view->back_link = '/?fn=goToToolsView';
            } 
        } 
        $this->view->calendar = TRUE;

    }

    /**
     * Show  biacon tool
     * 
     * @module     Signs
     * @category   action
     */
    public function biaconsAction()
    {
        $this->view->calendar = TRUE;

    }

    /**
     * Show  holidays tool
     * 
     * @module     Signs
     * @category   action
     */
    public function holidaysAction()
    {
        $this->view->calendar = TRUE;

    }

    /**
     * Show  schedule tool
     * 
     * @module     Signs
     * @category   action
     */
    public function sheduleAction()
    {
        $this->view->calendar = TRUE;

    }

    /**
     * Show  messages tool
     * 
     * @module     Signs
     * @category   action
     */
    public function messagesAction()
    {
        $this->view->calendar = TRUE;

    }

    /**
     * Show  sysparams tool
     * 
     * @module     Signs
     * @category   action
     */
    public function sysparamsAction()
    {   
        if (Zend_Auth::getInstance()->getIdentity()->access_level <= 3) {
	        $this->redirect('/');
        }
	
        $this->view->calendar = TRUE;


        $sysparams = new Signs_Model_SignsSysparams();
        $readingIntervalOptions =  Ventrill_Definition::$system_parameters[10];
        $readingInterval = $sysparams->getReadingsInterval();
        $trafficStatInterval = $sysparams->getFastStat();
        $this->view->readingIntervalOptions = $readingIntervalOptions['select'];
        $this->view->readingInterval = $readingInterval['value'];
        $this->view->trafficStatInterval = $trafficStatInterval['value'];
    }
    
    /**
     * Update system settings (table sys_params of Signs DB
     * 
     * @module     Signs
     * @category   action
     */
    public function updatesettingsAction()
    {
        $sysparams = new Signs_Model_SignsSysparams();
        
        $fast_stat = $this->getRequest()->getParam('stat');
        $readings_interval = $this->getRequest()->getParam('interval');
        
        $this->view->state = 'error';
        $this->view->message = '';
        if ($fast_stat==='0' || ($fast_stat && $readings_interval)) {
            if ($update1 = $sysparams->updateSettings($readings_interval)){
                $this->view->message .= 'readings_interval updated;';
            } else {
                $this->view->message .= 'readings_interval update failed;';
            }
            if ($update2 = $sysparams->updateSettings($fast_stat, 'fast_stat')) {
                $this->view->message .= 'fast_stat updated;';
            } else {
                $this->view->message .= 'fast_stat update failed;';
            }
            if ($update1 && $update2) $this->view->state = 'ok';
        }      
    }

    /**
     * Show  alerts tool
     * 
     * @module     Signs
     * @category   action
     */
    public function alertsAction()
    {


        $LocationsList = wd_API::getLocationsList();
        $this->view->locations = $LocationsList;
    }

    /**
     * Show  alerts List for location
     * 
     * @module     Signs
     * @category   AJAX
     */
    public function getalertslistAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $lid = $this->getRequest()->getParam('lid');
            $Recsetpoint_model = new Signs_Model_SignsRecsetpoint();
            $AlertsList = $Recsetpoint_model->getAlertSetPointParams($lid);
            
            $param_set_id = $Recsetpoint_model->getParamSetID($lid);
            if (!empty($param_set_id) ) {
                //$contactsTypes = wd_API::getContact($param_set_id);
                $contactsTypes = wd_API::getContactType($param_set_id);
            } else {
                $contactsTypes = false;
            }
            
            //var_dump($param_set_id);
            //var_dump($contactsTypes);
            //exit();
            if ($contactsTypes) {
                
                foreach ($contactsTypes as $key => $value) {
                    $userContact[$key] = wd_API::getAlertContactParamets($value['id']);
                }
                foreach ($contactsTypes as $key => $value) {
                    $alertInfo = array();
                    $alertInfo = wd_API::getContact($value['contact_id']);
                    $alertInfo['max'] = '0';
                    $alertInfo['min'] = '0';
                    $alertInfo['batt'] = '0';
                    if ($userContact[$key]) {
                        foreach ($userContact[$key] as $value) {
                            switch ($value['type']) {
                                case 1: $alertInfo['batt'] = '1';
                                    break;
                                case 2: $alertInfo['max'] = '1';
                                    break;
                                case 3: $alertInfo['min'] = '1';
                                    break;
                            }
                        }
                    }
                    $tmpArr = array();
                    $tmpArr['param_type'] = '0';
                    $tmpArr['set_point'] = $alertInfo;
                    $AlertsList[] = $tmpArr;
                }
            }



            $this->view->data = !empty($AlertsList) ? $AlertsList : 'none';
        }
    }

     /**
     * Save alerts Settings
     * 
     * @module     Signs
     * @category   AJAX
     */
    public function savealertAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $lid = $this->getRequest()->getParam('location_id');
            $set_points = $this->getRequest()->getParam('set_points');
	   
            $SpeedClass = new Ventrill_Speed();
            $mph = ($SpeedClass->getSpeedUnitValue() == 1);
            // prepaire array for saving
            foreach ($set_points as $key => $value) {
                if ($value['value'] == 'OFF') {
                    $set_points[$key]['value'] = 0;
                } 
		
		        if($value['attr'] == 2 || $value['attr'] == 3 ) {
    		      if($mph == 1 && is_numeric($value['value'])) {
    			      $value['value'] = round($value['value'] * 1.609344);
    		       }
	            }	 
		        $set_points[$key]['value'] =  $value['value'];
            }

            // get set point id
            $configsTable = new Signs_Model_SignsConfigs();
            $Recsetpoint_model = new Signs_Model_SignsRecsetpoint();
            $Paramset_model = new Signs_Model_SignsParamsets();



            //get data to clean DB
            $param_set_id = $Recsetpoint_model->getParamSetID($lid);
            
            if ($param_set_id !== null) {
                $contactsTypes = wd_API::getContactType($param_set_id);
                $contactsAlertsIDs = array();
                foreach ($contactsTypes as $value) {
                    $contactsAlertsIDs[] = $value['id'];
                }
                // Clean DB
                wd_API::delCleanAlertType($contactsAlertsIDs);
                $Recsetpoint_model->delSetPointId($param_set_id);
                wd_API::delCleanAlerts($param_set_id);
            } 
            
            // if $set_point_id exist then delete all records for this id and remember this id
            
            if (($param_set_id <> NULL) and ($param_set_id <> '0')) {

                $res = $Recsetpoint_model->setAlertsParameters($param_set_id, $set_points);
            } else { // else create new  $set_point_id and write it in config table
                $param_set_id = $Paramset_model->addScheduleWithType('Set Points', 7);
                $configsTable->updateSetPointID($lid, $param_set_id);
                $res = $Recsetpoint_model->setAlertsParameters($param_set_id, $set_points);
            }

            foreach ($set_points as $key => $val) {
                if ($val['attr'] == '0') {
                    $ContactAlerts_id = wd_API::setContactType($param_set_id, $val);
                    $set_points[$key]['value'] = $ContactAlerts_id;

                    if ($val['min'] != 0) {
                        wd_API::setAlertsTypeParameters($ContactAlerts_id, 3);
                    }
                    if ($val['max'] != 0) {
                        wd_API::setAlertsTypeParameters($ContactAlerts_id, 2);
                    }
                    if ($val['batt'] != 0) {
                        wd_API::setAlertsTypeParameters($ContactAlerts_id, 1);
                    }
                }
            }
            
            $this->view->data = isset($res) ? $res : 'none';
        }
    }

     /**
     * Show  Contact List for Alerts
     * 
     * @module     Signs
     * @category   AJAX
     */
    public function getcontactslistAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $script_action = $this->getRequest()->getParam('GiveMe');

            if ($script_action == 1) {
                
            }
            $usersContact = wd_API::getAllUsersContacts();
            $this->view->data = !empty($usersContact) ? $usersContact : 'none';
        }
    }
    
    public function syncAction()
    {

        $this->view->back_link = "/signs/tools/index";
        //$LocationsList = wd_API::getLocationsList();
        $company_id = Zend_Auth::getInstance()->getIdentity()->company_id;
        $signsRadars = new Signs_Model_SignsRadars();
        
        $syncTable = new Signs_Model_SignsServicereq();
        $locations = new Application_Model_DbTable_Locations();
        $LocationsList = $signsRadars->getListOfRaidars($company_id);
        foreach( $LocationsList as &$row) {
            $row['location_name'] = '';
            $row['last_sync'] = '';
            if ($location = $locations->getLocation($row['location_id'])) {
                $row['location_name'] = $location['name'];
                $lastSync = $syncTable->getLastSync($row['location_id']);
                if (!empty($lastSync['time_proc']) && $lastSync['time_proc'] != '0000-00-00 00:00:00') {
                    $row['last_sync'] = $lastSync['time_proc'];
                } else {
                    $row['last_sync'] = '';
                }
            }

        }
        $this->view->locations = $LocationsList;
        
    }
    
    public function getsignsscheduleslistAction() {

        $this->view->state = 'ok';
        
        $company_id = Zend_Auth::getInstance()->getIdentity()->company_id;
        $signsRadars = new Signs_Model_SignsRadars();
        $locations = new Application_Model_DbTable_Locations();
        $signConfig = new Signs_Model_SignsConfigs();
        
        $recSchedules = new Signs_Model_SignsParamsets();
        $SchedulesList = $recSchedules->getScheduleList();
        
        $LocationsList = $signsRadars->getListOfRaidars($company_id);
        foreach( $LocationsList as &$row) {
            if ($location = $locations->getLocation($row['location_id'])) {
                $row['location_id'] = $location['id'];
                $row['location_name'] = $location['name'];
            } else {
                $row['location_id'] = 0;
                $row['location_name'] = '';
            }
            $schedule = $signConfig->getCurrent($row['location_id']);
            
            $row['schedule_id'] = ($schedule == NULL)?'':$schedule['sid'];
            $row['schedule_name'] = ($schedule == NULL)?'':(($schedule['sidtype']=='2')?'Express mode':$schedule['schedulename']);
        }
        
        $with_radar = array_column($LocationsList,'location_id');
        $all_locations = $locations->getLocations();
        foreach ($all_locations as $loc) {
            if (!in_array($loc['lid'], $with_radar)) {
                $r = array();
                $r['id'] = '';
                $r['location_id'] = $loc['lid'];
                $r['location_name'] = $loc['name'];
                $r['serial']  = '';
                //var_dump($loc);
                //exit;
                $schedule = $signConfig->getCurrent($r['location_id']);
                $r['schedule_id'] = ($schedule == NULL)?'':$schedule['sid'];
                $r['schedule_name'] = ($schedule == NULL)?'':(empty(trim($schedule['schedulename']))?'Express mode':$schedule['schedulename']);
                array_push($LocationsList, $r);
            }
        }
        
        $view = new Zend_View();
        $view->setScriptPath(APPLICATION_PATH . '/modules/signs/views/scripts/tools');
        $view->locations = $LocationsList;
        $view->schedulesList = $SchedulesList;
        $view->locationsList = $all_locations;
        $re = $this->getRequest()->getParam("re");
        $hexToken = dechex($re);
        $token_key = md5($hexToken . date("Ymd"));
        $view->token = $token_key;
        
        $this->view->testView = $view->render('getsignsscheduleslist.phtml');
        
    }
    
    public function getscheduleslistAction() {

        $this->view->state = 'ok';

        $company_id = Zend_Auth::getInstance()->getIdentity()->company_id;
        
        $recSchedules = new Signs_Model_SignsParamsets();
        
        $SchedulesList = $recSchedules->getScheduleList();
        
        $this->view->schedules = $SchedulesList;
        
    }

    public function syncsignAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $lid = $this->getRequest()->getParam('lid');
            
            $locationsTable = new Application_Model_DbTable_Locations();
            $timeOffset = $locationsTable->getLocationTimeOffset($lid);
            
            $ServicereqTable = new Signs_Model_SignsServicereq();
            
            $res = $ServicereqTable->setSyncTime($lid, $timeOffset);
            $this->view->data = isset($res) ? $res : 'none';
            
        }
        
    }
    
    public function rebootsignAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            
            $ServicereqTable = new Signs_Model_SignsServicereq();
            $lid = $this->getRequest()->getParam('lid');
            
            $locationsTable = new Application_Model_DbTable_Locations();
            $timeOffset = $locationsTable->getLocationTimeOffset($lid);
            
            $res = $ServicereqTable->rebootSign($lid, $timeOffset);
            $this->view->data = isset($res) ? $res : 'none';
            
        }
        
    }
}
