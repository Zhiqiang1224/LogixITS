<?php

/**
 * Signs dashboard class
 *
 * @module     Signs
 * @category   Controller
 * @since      Class available since Release 2.0
 * @deprecated  --
 */

class Signs_DashboardController extends wd_Controller
{
   
    private $off_message = '';
    private $speed_label = '';

    /**
     * Check for authorization  befor start the class
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
            ->addActionContext('getcommdata', 'json') 
            //->addActionContext('getfreelocations', 'json') 
            ->initContext('json');
        
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'index', 'default');
        }
    }

    /**
     * Show list of schedules
     *
     * @module     Signs
     * @category   action 
     */
    public function indexAction()
    {   
        if (Zend_Auth::getInstance()->getIdentity()->access_level < 3) {
            $this->redirect('/');
        }
	
        $this->view->modal = TRUE;
        $this->view->state = 'ok';
        $this->view->back_link = "/signs/tools/index";
        
        $company_id = Zend_Auth::getInstance()->getIdentity()->company_id;
        $signsRadars = new Signs_Model_SignsRadars();
        $locations = new Application_Model_DbTable_Locations();
	
        $locTypeTable = new Signs_Model_LocationType();
	
        $paramSets = new Signs_Model_SignsParamsets();
        
        $signConfig = new Signs_Model_SignsConfigs();
        
        $SettinsTable = new Signs_Model_SignsSignparams();
        $RecMatrixTable = new Signs_Model_SignsMatrixMessage();
        $alertsTable = new Signs_Model_SignsRecsetpoint();
        
        $recSchedules = new Signs_Model_SignsParamsets();
        $SchedulesList = $recSchedules->getScheduleList();
        
        $no_messages_models = array(100,250,400,450,475,500,550,625);
        
        $LocationsList = $signsRadars->getListOfRaidars($company_id);
        foreach( $LocationsList as &$row) {
            if ($location = $locations->getLocation($row['location_id'])) {
                $row['location_id'] = $location['id'];
                $row['location_name'] = $location['name'];
            } else {
                $row['location_id'] = 0;
                $row['location_name'] = '';
            }
            
            if (in_array($row['sign_model'],$no_messages_models)) {
                $row['messages'] = false;
            } else {
                $row['messages'] = true;
            }
                    
            $schedule = $signConfig->getCurrent($row['location_id']);
            
           /* $row['schedule_id'] = ($schedule == NULL)?'type_2':$schedule['sid'];
            //$row['schedule_name'] = ($schedule == NULL)?'No schedule':(($schedule['sidtype']=='2')?'Express mode':$schedule['schedulename']);
            $row['schedule_name'] = ($schedule == NULL)?'Not Assigned':(($schedule['sidtype']=='2')?'Express mode':'Schedule');*/
            
          
            if (!empty($schedule['sid'])) {
                $row['schedule_id'] = $schedule['sid'];
                if($schedule['sidtype']=='2'){
                    $row['schedule_name'] = 'Express mode'; 
                } else {
                    $row['schedule_name'] = 'Schedule'; 
                }
            } else {
                $row['schedule_id'] = 'type_2';
                $row['schedule_name'] = 'Express mode';
            }
	    
	        if($row['sign_model']!=800){
                if (!empty($schedule['mid'])) {
                    $row['message_id'] = $schedule['mid'];
                    $row['message_name'] = 'Assigned';
                } else {
                    $row['message_id'] = 'type_6';
                    $row['message_name'] = 'Not Assigned';
                }
            } else {
                  if (!empty($schedule['mid']) && empty($schedule['admid'])) {
                     $row['message_id'] = $schedule['mid'];
                     $row['message_name'] = 'Assigned';
                     $row['message_bid'] = 'type_8';
                     $row['message_bname'] ='Not Assigned';
                  } else if(empty($schedule['mid']) && !empty($schedule['admid'])){
                     $row['message_id'] = 'type_6';
                     $row['message_name'] = 'Not Assigned';
                     $row['message_bid'] = $schedule['admid'];
                     $row['message_bname'] = 'Assigned';
                  } else if(!empty($schedule['mid']) && !empty($schedule['admid'])){
                     $row['message_id'] = $schedule['mid'];
                     $row['message_name'] = 'Assigned';
                     $row['message_bid'] = $schedule['admid'];
                     $row['message_bname'] = 'Assigned';
                  } else {
                     $row['message_id'] = 'type_6';
                     $row['message_name'] = 'Not Assigned';
                     $row['message_bid'] = 'type_8';
                     $row['message_bname'] = 'Not Assigned';
                  }
            }
	    
           if($row['sign_model']!=800 && !empty($schedule['mid'])){
	            $srl = $RecMatrixTable->getRecordsBySetId($schedule['mid']);
                if ($srl[0]['desc']!='EmptySmallMessage') {
                    $row['message_id'] = $schedule['mid'];
                    $row['message_name'] = 'Assigned';
                } else {
                    $row['message_id'] = 'type_6';
                    $row['message_name'] = 'Not Assigned';
                }	
            } else {
	          if(!empty($schedule['mid']) && !empty($schedule['admid'])){
	            $srl = $RecMatrixTable->getRecordsBySetId($schedule['mid']);
                $brl = $RecMatrixTable->getRecordsBySetId($schedule['admid']);
                  if ($srl[0]['desc']!='EmptySmallMessage' && $brl[0]['desc']=='EmptyBigMessage') {
                     $row['message_id'] = $schedule['mid'];
                     $row['message_name'] = 'Assigned';
                     $row['message_bid'] = 'type_8';
                     $row['message_bname'] ='Not Assigned';
                  } else if($srl[0]['desc']=='EmptySmallMessage' && $brl[0]['desc']!='EmptyBigMessage'){
                     $row['message_id'] = 'type_6';
                     $row['message_name'] = 'Not Assigned';
                     $row['message_bid'] = $schedule['admid'];
                     $row['message_bname'] = 'Assigned';
                  } else if($srl[0]['desc']!='EmptySmallMessage' && $brl[0]['desc']!='EmptyBigMessage'){
                     $row['message_id'] = $schedule['mid'];
                     $row['message_name'] = 'Assigned';
                     $row['message_bid'] = $schedule['admid'];
                     $row['message_bname'] = 'Assigned';
                  } else {
                     $row['message_id'] = 'type_6';
                     $row['message_name'] = 'Not Assigned';
                     $row['message_bid'] = 'type_8';
                     $row['message_bname'] = 'Not Assigned';
                  }
	            }
		
            }
	  
           
        
        //$view = new Zend_View();
        //$this->view->setScriptPath(APPLICATION_PATH . '/modules/signs/views/scripts/tools');
        $re = $this->getRequest()->getParam("re");
        $hexToken = dechex($re);
        $token_key = md5($hexToken . date("Ymd"));
        $this->view->token = $token_key;
        
        $this->view->locations = $LocationsList;
        $this->view->schedulesList = $SchedulesList;
        $this->view->locationsList = $all_locations;
        
        //$this->view = $view;
        //$this->view->testView = $view->render('getsignsscheduleslist.phtml');
    }

    /**
     * Get Location data (schedule, beacon, messages, etc...)
     *
     * @module     Signs
     * @category   AJAX dashboard.js
     */
    public function getcommdataAction()
    {
        $this->_helper->layout->disableLayout();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $set_id = $this->getRequest()->getParam('id');
	        $lid = $this->getRequest()->getParam('loc_id');
	
            $Paramsets_model = new Signs_Model_SignsParamsets();
            $Type_sinq = $Paramsets_model->getSinqType($set_id);
            if ($Type_sinq['type']) {
                $this->view->type = $Type_sinq['type'];
            } else {
                $this->view->type = 2;
            }
	    
	     $locTypeTable = new Signs_Model_LocationType();
	     $loc_type = $locTypeTable->getLocationType($lid);
	     $this->view->loctype = $loc_type['type'];
        }
    }
    
    /**
     * Show schedule for location
     *
     * @module     Signs
     * @category   AJAX dashboard.js
     */
    public function showscheduleAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $lid = isset($_GET['lid']) ? $_GET['lid'] : NULL;
        $locTypeTable = new Signs_Model_LocationType();
        $loc_type= $locTypeTable->getLocationType($lid);
        $this->view->loctype = $loc_type['type'];

        $utils  = new Signs_Model_Utilities();
        $Paramsets_model = new Signs_Model_SignsParamsets();
        $ScheduleList = $Paramsets_model->getScheduleListById($set_id);
        $SpeedClass = new Ventrill_Speed();
        $RecscheduleTable = new Signs_Model_SignsRecschedule();
    
      
        $ScheduleSelectList = $Paramsets_model->getScheduleList();
        
        foreach ($ScheduleSelectList as $key => &$item) {
            $item['selected'] = ($item['id']==$set_id)?'selected':'';
            $formData_type = $RecscheduleTable->getSignsType($item['id']);  
            $item['rec_mode'] = $formData_type['rec_mode'];
            $ScheduleSelectList[$key]['rec_mode'] = $item['rec_mode'] % 16;  
        }

        if (count($ScheduleList)) {
            $ScheduleIds = array();
            foreach ($ScheduleList as $ScheduleOne) {
                $ScheduleIds[] = $ScheduleOne['id'];
            }
            $RecscheduleTable = new Signs_Model_SignsRecschedule();
            $RulesList = $RecscheduleTable->getScheduleRules($ScheduleIds);

            //$this->decodeRulesListS($RulesList, $SpeedClass);
            $utils->decodeRulesListS($RulesList, $SpeedClass);

            $ScheduleList = $utils->reformScheduleList($ScheduleList);
            $utils->appendingRulesToScheduleList($ScheduleList, $RulesList);
        } else {
            $ScheduleIds = array();
            $ScheduleIds[] = $set_id;
            $RecscheduleTable = new Signs_Model_SignsRecschedule();
            $RulesList = $RecscheduleTable->getScheduleRules($ScheduleIds);

            //$this->decodeRulesListS($RulesList, $SpeedClass);
            $utils->decodeRulesListS($RulesList, $SpeedClass);

            $ScheduleList = $utils->reformScheduleList($ScheduleList);
            $utils->appendingRulesToScheduleList($ScheduleList, $RulesList);
        }
      
        $this->view->ScheduleList = $ScheduleList;
        $this->view->ScheduleSelectList = $ScheduleSelectList;
        $this->_helper->layout->setLayout('ajax');
    }
    
    /**
     * Show Express Mode schedule for location
     *
     * @module     Signs
     * @category   AJAX dashboard.js
     */
    public function showexpressmodeAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $location_id = isset($_GET['lid']) ? $_GET['lid'] : NULL;
        $locTypeTable = new Signs_Model_LocationType();
        $loc_type = $locTypeTable->getLocationType($location_id);

        if(empty($loc_type)) {
          $formData = array();
          $formData['type'] = 1;
          $formData['lid'] = $location_id;
          $locTypeTable->appendNewLocType($formData);
          $loc_type['type'] = $formData['type'];
        }
        $this->view->loctype = $loc_type['type'];

        $utils  = new Signs_Model_Utilities();
        $SpeedClass = new Ventrill_Speed();
        $mph = ($SpeedClass->getSpeedUnitValue() == 1);
        $locationMessages = Ventrill_Definition::$display_messages;
        $Paramsets_model = new Signs_Model_SignsParamsets();

        /**
         * TODO: suddenly New Zealand
         */
/*        if (!empty($set_id)) {
            $paramset = $Paramsets_model->getSchedule($set_id, true);
            if ($paramset->type <> 2) {
                $set_id = 'type_2';
            }
        }
*/
        
        $RadarTable = new Signs_Model_SignsRadars();
        $radar = $RadarTable->getRadarForLocation($location_id);
        
        $formData = array();
        $formData['display_is_always_on'] = '';
        
        if ($mph) {
            $min_speed = 3;
            $max_speed = 100;
            $def_speed = 30;
        } else { //KMH
            $min_speed = 5;
            $max_speed = 160;
            $def_speed = 50;
        } //else

        $formData['display_range_slider_min'] = $min_speed;
        $formData['display_range_slider_max'] = $max_speed;
        $formData['speed_limit'] = $def_speed;
        $formData['tolerated_speed'] = $def_speed;
        $formData['flash_message_speed'] = $def_speed;
        $formData['flash_digits_slider_min'] = $min_speed;
        $formData['flash_digits_slider_max'] = $max_speed;
        $formData['flash_digits_speed'] = $def_speed;
        $formData['strobe_on_speed_slider_min'] = $min_speed;
        $formData['strobe_on_speed_slider_max'] = $max_speed;
        $formData['strobe_on_speed'] = $def_speed;
        $formData['sliders_min'] = $min_speed;
        $formData['sliders_max'] = $max_speed;

        if (!empty($radar)) {
            $this->view->assigned = true;
            $formData['sign_type'] = $RadarTable->getRadarTypeByModel($radar['sign_model']);
            
        } else {
            $this->view->assigned = false;
            $formData['sign_type'] = 0;
        }
        
        $configTable = new Signs_Model_SignsConfigs();
        $config = $configTable->getCurrent($location_id);
        $customMessageID = $config['mid'];
        if (!empty($customMessageID)) {
            $cmTable = new Signs_Model_SignsMatrixMessage();
            $messagesSet = $Paramsets_model->getSchedule($customMessageID, true);
            if ($messagesSet['type'] == '6') {
                $messagesList = $cmTable->getRecordsBySetId($customMessageID);
                foreach ($messagesList as $customMessage) {
                    switch ($customMessage['slot']) {
                        case '001':
                            $locationMessages[5] = $locationMessages[5]. ' ( '.$customMessage['desc'].' )';
                            break;
                        case '002':
                            $locationMessages[6] = $locationMessages[6]. ' ( '.$customMessage['desc'].' )';
                            break;
                        case '003':
                            $locationMessages[7] = $locationMessages[7]. ' ( '.$customMessage['desc'].' )';
                            break;
                        case '004':
                            $locationMessages[8] = $locationMessages[8]. ' ( '.$customMessage['desc'].' )';
                            break;
                    } //switch
                } //for
            } //if
        } //if

        $customMessageID = $config['admid'];
        if (!empty($customMessageID)) {
            $cmTable = new Signs_Model_SignsMatrixMessage();
            $messagesSet = $Paramsets_model->getSchedule($customMessageID, true);
            if ($messagesSet['type'] == '8') {
                $messagesList = $cmTable->getRecordsBySetId($customMessageID);
                foreach($messagesList as $customMessage) {
                    switch ($customMessage['slot']) {
                        case '001':
                            $locationMessages[10] = $locationMessages[10]. ' ( '.$customMessage['desc'].' )';
                            break;
                        case '002':
                            $locationMessages[11] = $locationMessages[11]. ' ( '.$customMessage['desc'].' )';
                            break;
                        case '003':
                            $locationMessages[12] = $locationMessages[12]. ' ( '.$customMessage['desc'].' )';
                            break;
                        case '004':
                            $locationMessages[13] = $locationMessages[13]. ' ( '.$customMessage['desc'].' )';
                            break;
                    }
                }
            } 
        }
        
        //$scheduleSet = $Paramsets_model->getScheduleListById($set_id);

        $RecscheduleTable = new Signs_Model_SignsRecschedule();
        $rules = $RecscheduleTable->getScheduleRules($set_id);

        $utils->decodeSpeedValueTemp($rules, $SpeedClass);
      
        if (!empty($rules)) {
            $rule = $rules[0];
            if ($rule['min_speed'] == "OFF") {
               $rule['min_speed'] = $min_speed;
            }
            if ($rule['max_speed'] == "OFF") {
                $rule['max_speed'] = $mph ? 100 : 160;
            }
            $formData['display_range_slider_min'] = $rule['min_speed'];
            $formData['display_range_slider_max'] = $rule['max_speed'];
            $formData['speed_limit'] = $rule['speed_limit'];
            $formData['tolerated_speed'] = $rule['speed_tolerated'];
            
            $rec_mode = (int) $rule['rec_mode'];
            if ($rec_mode > 15) {
                $formData['display_is_always_on'] = Ventrill_Definition::testbit($rec_mode, 4);
                $formData['strobe_off_above_max_check'] = Ventrill_Definition::testbit($rec_mode, 5);
                $formData['switch_to_stealth'] = Ventrill_Definition::testbit($rec_mode, 6);
            }
            
            if (isset($rule['blink_dig_speed']) && ((int) $rule['blink_dig_speed']) > 0) {
                $formData['flash_digits_speed_check'] = TRUE;
                $formData['flash_digits_speed'] = $rule['blink_dig_speed']; 
            }
            
            if (isset($rule['speed_strobe']) && ((int) $rule['speed_strobe']) > 0) {
                $formData['strobe_on_speed_check'] = TRUE;
                $formData['strobe_on_speed'] = $rule['speed_strobe']; 
            }
            
            if (isset($rule['blink_msg_speed']) && ((int) $rule['blink_msg_speed']) > 0) {
                $formData['flash_message_check'] = TRUE;
                $formData['flash_message_speed'] = $rule['blink_msg_speed']; 
            }
            
            if ($formData['sign_type'] == 0) {
                $formData['sign_type'] = $rule['rec_mode'] % 16;
            }
            
            $formData['message_minimum'] = $rule['msg_above_min'];
            $formData['message_tolerated'] = $rule['msg_above_limit'];
            $formData['message_maximum'] = $rule['msg_above_tolerated'];
            $formData['message_above_maximum'] = $rule['msg_above_max'];
        }
        
        $ScheduleSelectList = $Paramsets_model->getScheduleList();

        foreach ($ScheduleSelectList as $key => &$item) {
            $item['selected'] = ($item['id']==$set_id)?'selected':'';
            $formData_type = $RecscheduleTable->getSignsType($item['id']);  
            $item['rec_mode'] = $formData_type['rec_mode'];
            $ScheduleSelectList[$key]['rec_mode'] = $item['rec_mode'] % 16;  
        }

        $this->view->formData = $formData;
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $this->view->signs = Signs_Model_Radars::getRadarTypes();
        $this->view->messages = $locationMessages;
        $this->view->off_speed_message = Ventrill_Definition::$off_speed_message;
        $this->view->ScheduleSelectList = $ScheduleSelectList;
        $this->_helper->layout->setLayout('ajax');
    }
 
     /**
     * Show SELECT of locations which has no Radar Sign assigned yet
     *
     * @module     Signs
     * @category   AJAX dashboard.js
     */
    public function getfreelocationsAction()
    {   
        $id = $this->getRequest()->getParam('radar_id'); 
        $locTypeTable = new Signs_Model_LocationType();   
        $radars  = new Signs_Model_SignsRadars();
        $locationList = $radars->getFreeLocations();
        if($id){
            $radarInfo = $radars->getRadarModel($id);
            $rec_mode = $radars->getRadarTypeByModel($radarInfo['sign_model']);
        }
        foreach ($locationList as $key => $value) {
            $locType= $locTypeTable->getLocationType($value['id']);
          
            if(empty($locType)) {
              $value['signs_type'] = 1;
            } else {
              $value['signs_type'] = $locType['type'];
            }
            
             $locationList[$key]['signs_type'] = $value['signs_type'];
        }
           
        $this->view->radar_mode = $rec_mode; 
        $this->view->locationList = $locationList;
        $this->_helper->layout->setLayout('ajax');
    }

    /**
     * Show beacons schedule for location
     *
     * @module     Signs
     * @category   AJAX dashboard.js
     */
    public function showbscheduleAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $utils  = new Signs_Model_Utilities();
        $Paramsets_model = new Signs_Model_SignsParamsets();
        $BeaconsList = $Paramsets_model->getBeaconsListById($set_id);
        
        $beaconsSelectList = $Paramsets_model->getBeaconsList();
        foreach ($beaconsSelectList as &$item) {
            $item['selected'] = ($item['id']==$set_id)?'selected':'';
        }

        if (count($BeaconsList)) {
            $BeaconsIds = array();
            foreach ($BeaconsList as $BeaconsOne) {
                $BeaconsIds[] = $BeaconsOne['id'];
            }
            $RecbeaconsTable = new Signs_Model_SignsRecbschedule();
            $RulesList = $RecbeaconsTable->getScheduleRules($BeaconsIds);
            $utils->decodeRulesListB($RulesList);
            $BeaconsList = $utils->reformBeaconsList($BeaconsList);
            $utils->appendingRulesToBeaconsList($BeaconsList, $RulesList);
        }
        $this->view->BeaconsList = $BeaconsList;
        $this->view->beaconsSelectList = $beaconsSelectList;
        $this->_helper->layout->setLayout('ajax');
    }
    
    /**
     * Show advanced settings for location
     *
     * @module     Signs
     * @category   AJAX dashboard.js
     */
    public function advancedsettingsAction() {
        $settings=array();
        $location_id = $this->getRequest()->getParam("location_id");
        $settings['location_id'] = $location_id;
        
        $locationInfo = wd_API::getLocationInfo($location_id);
        $data['location'] = $locationInfo;
        $this->view->location = $data;

        $SettinsTable = new Signs_Model_SignsSignparams();
        $AdvSettins = $SettinsTable->getAdvancedSettings($location_id);

        if ($AdvSettins ===  null) {
            $AdvSettins = $SettinsTable->getDefaultValues();
        };

        $bLow = $AdvSettins['brightness_byte'] & 15;
        $bHigh = ($AdvSettins['brightness_byte'] & 240) >> 4;
        $settings['radar_slider_min'] = (floor(round((($bHigh * 100) / 16)) / 10) + 1) * 10;
        $settings['radar_slider_max'] = (floor(round((($bLow * 100) / 16)) / 10) + 1) * 10;

        $bLedFlashingSpeed = $AdvSettins['dig_blink_mode'] & 15;
        if ($bLedFlashingSpeed <= 6) {
            $settings['led_flashing'] = 38;
        } else {
            if ($bLedFlashingSpeed <= 10) {
                $settings['led_flashing'] = 74;
            } else {
                $settings['led_flashing'] = 110;
            }
        }

        $bLow = $AdvSettins['strobe_blink_mode'] & 15;
        $bHigh = ($AdvSettins['strobe_blink_mode'] & 240) >> 4;

        if ($bLow <= 2) {
            $settings['strobe_flashing'] = 2;
        } else {
            if ($bLow <= 8) {
                $settings['strobe_flashing'] = 8;
            } else {
                $settings['strobe_flashing'] = 14;
            }
        }

        if ($bHigh === 4) {
            $settings['strobe_series'] = 64;
        } else {
            if ($bHigh === 6) {
                $settings['strobe_series'] = 96;
            } else {
                if ($bHigh === 8) {
                    $settings['strobe_series'] = 128;
                } else {
                    $settings['strobe_series'] = 160;
                }
            }
        }

        $settings['detection_mode'] = $AdvSettins['head_target_mode'];
        $settings['head_power'] = $AdvSettins['head_power'];
        $settings['id'] = isset($AdvSettins['id'])?$AdvSettins['id']:'';
        $settings['location_id'] = $location_id;
        
        $this->view->settings = $settings;
        
        $this->view->sMin = $settings['radar_slider_min'];
        $this->view->sMax = $settings['radar_slider_max'];
        $this->view->sHead = $settings['head_power'];
        $this->_helper->layout->setLayout('ajax');
    }
    
    /**
     * Show alerts for location
     *
     * @module     Signs
     * @category   AJAX dashboard.js
     */
    public function alertsAction() {
        $settings = array();
        $location_id = $this->getRequest()->getParam("location_id");
        
        $location_params = array();
        $location_params['max'] = 'OFF';
        $location_params['min'] = 'OFF';
        $location_params['batt'] = 'OFF';
        
	    $SpeedClass = new Ventrill_Speed();
        $Recsetpoint_model = new Signs_Model_SignsRecsetpoint();
        $AlertsList = $Recsetpoint_model->getAlertSetPointParams($location_id);

        $param_set_id = $Recsetpoint_model->getParamSetID($location_id);
        $contactsTypes = wd_API::getContactType($param_set_id);

        if ($contactsTypes) {
            foreach ($contactsTypes as $key => $value) {
                $userContact[$key] = wd_API::getAlertContactParamets($value['id']);
            }
            foreach ($contactsTypes as $key => $value) {
                //$alertInfo = array();
                $alertInfo = wd_API::getContact($value['contact_id']);
                if (is_null($alertInfo)) //no contacts with this contact_id
                  continue;
                $alertInfo['max'] = '0';
                $alertInfo['min'] = '0';
                $alertInfo['batt'] = '0';
                if ($userContact[$key]) {
                    foreach ($userContact[$key] as $value) {
                        switch ($value['type']) {
                            case 1:
                                $alertInfo['batt'] = '1';
                                break;
                            case 2:
                                $alertInfo['max'] = '1';
                                break;
                            case 3:
                                $alertInfo['min'] = '1';
                                break;
                        } //switch
                    } //for
                } //if
                $tmpArr = array();
                $tmpArr['param_type'] = '0';
                $tmpArr['set_point'] = $alertInfo;
                $AlertsList[] = $tmpArr;
            } //for
        } //if

        //$AlertsList[{"param_type":"1","set_point":"3"},{"param_type":"2","set_point":"1"},{"param_type":"3","set_point":"2"}]}
        foreach ($AlertsList as $param) {
            if ($param['param_type'] == 2) {
	            $SpeedClass->convertSpeedValueToShow($param['set_point']);
                $location_params['max'] = $param['set_point'];
            } elseif ($param['param_type'] == 3) {
	            $SpeedClass->convertSpeedValueToShow($param['set_point']);
                $location_params['min'] = $param['set_point'];
            } elseif ($param['param_type'] == 1) {    
                $location_params['batt'] = $param['set_point'];
            }
        }
        
        $this->view->params = $location_params;
        $this->view->data = !empty($AlertsList) ? $AlertsList : array();
        
        $companyContacts = wd_API::getAllUsersContacts();
        $this->view->contacts = !empty($companyContacts) ? $companyContacts : array();
        
        $this->view->location_id = $location_id;
        $this->_helper->layout->setLayout('ajax');
    }
    
    /**
     * Show matrix messages list 
     *
     * @module     Signs
     * @category   AJAX dashbaoard.js
     */
    public function showmessagesAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $location_id = isset($_GET['location_id']) ? $_GET['location_id'] : NULL;
        $set_type = isset($_GET['set_type']) ? $_GET['set_type'] : 6;
        
        $configTable = new Signs_Model_SignsConfigs();
        $config = $configTable->getCurrent($location_id);
        $small_set_id = $config['mid'];
        $full_set_id = $config['admid'];
        $Paramsets_model = new Signs_Model_SignsParamsets();
        
        $messagesSelectList = $Paramsets_model->getMatrixsList(6); //small messages
        foreach ($messagesSelectList as &$item) {
            $item['selected'] = ($item['id']==$small_set_id)?'selected':'';
        }
        
        $bigMessagesSelectList = $Paramsets_model->getMatrixsList(8); //full screen messages
        foreach ($bigMessagesSelectList as &$item) {
            $item['selected'] = ($item['id']==$full_set_id)?'selected':'';
        }
	
        $RecMatrixTable = new Signs_Model_SignsMatrixMessage();
	
        $messageBig = '00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
        $messageSmall = '00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
 	
         $sid = $Paramsets_model-> getMessageId('EmptySmallMessage', 6);
    	 if(!empty($sid)){
    	     $paramset_sid = $sid['id'];
    	 } else {
             $paramset_sid = $Paramsets_model->addScheduleWithType('EmptySmallMessage', 6);       
             $RecMatrixTable->addEmptyRuls($paramset_sid, $messageSmall, 1, $animation_speed = 2, 'EmptySmallMessage');
    	 }
        
        $imgSmall=array('','','','');
        if (!empty($small_set_id)) {
            $RecMatrixTable = new Signs_Model_SignsMatrixMessage();
            $RulesList = $RecMatrixTable->getRecordsBySetId($small_set_id);
            if (!empty($RulesList)){

                for ($i=0; $i<4; $i++){
                    if ($RulesList[$i]['animation_frame']=='1'){
                        $film = "(<img alt='' src='/img/service/film.png' />)";
                    } else {
                        $film = "";  
                    }
                    $imgSmall[$i]="<img  onerror='$(this).attr(\"src\",\"/img/service/empty.png\")' alt='' src='/signs/matrix/show?id=".$RulesList[$i]['id']."' /><br>".$RulesList[$i]['desc'].$film."<br>"; 
                }
            };
        }
	

         $bid = $Paramsets_model-> getMessageId('EmptyBigMessage', 8);
    	 if(!empty($bid)){
    	     $paramset_bid = $bid['id'];
    	 } else {
             $paramset_bid = $Paramsets_model->addScheduleWithType('EmptyBigMessage', 8);       
             $RecMatrixTable->addEmptyRuls($paramset_bid, $messageBig, 1, $animation_speed = 2, 'EmptyBigMessage');
    	 }
	 
        $imgFull=array('','','','');
        $this->view->FullMessages = true;
        $full_messages_models = array(0,800);
        $radarsTable = new Signs_Model_SignsRadars();
        $radar = $radarsTable->getRadarForLocation($location_id);
        if (!empty($radar) || (!empty($full_set_id) && empty($radar))) {
            if ((!empty($full_set_id) && empty($radar)) || in_array($radar['sign_model'],$full_messages_models)) {
                if (!empty($full_set_id)) {
                    $RecMatrixTable = new Signs_Model_SignsMatrixMessage();
                    $RulesList = $RecMatrixTable->getRecordsBySetId($full_set_id);
                    if (!empty($RulesList)){
                        for ($i=0; $i<4; $i++){
                            if ($RulesList[$i]['animation_frame']=='1'){
                                $film = "(<img alt='' src='/img/service/film.png' />)";
                            } else {
                                $film = "";  
                            }
                            $imgFull[$i]="<img  onerror='$(this).attr(\"src\",\"/img/service/empty.png\")' alt='' src='/signs/matrix/show?id=".$RulesList[$i]['id']."' /><br>".$RulesList[$i]['desc'].$film."<br>"; 
                        }
                    };
                };
            } else {
                $this->view->FullMessages = false;
            }
        }
        
        $this->_helper->layout->setLayout('ajax');
        $this->view->messagesSelectList = $messagesSelectList;
        $this->view->bigMessagesSelectList = $bigMessagesSelectList;
        $this->view->imgSmall = $imgSmall;
        $this->view->imgFull = $imgFull;
    	$this->view->paramset_sid = $paramset_sid;
    	$this->view->paramset_bid = $paramset_bid;
    }
    
    /**
     * Show matrix small messages list set_type=6
     *
     * @module     Signs
     * @category   AJAX dashbaoard.js
     */
    public function showsmallmessagesAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $set_type = 6;
        
        $imgSmall=array('','','','');
        if (!empty($set_id)) {
            $RecMatrixTable = new Signs_Model_SignsMatrixMessage();
            $RulesList = $RecMatrixTable->getRecordsBySetId($set_id);
            if (!empty($RulesList)){

                for ($i=0; $i<4; $i++){
                    if ($RulesList[$i]['animation_frame']=='1'){
                        $film = "(<img alt='' src='/img/service/film.png' />)";
                    } else {
                        $film = "";  
                    }
                    $imgSmall[$i]="<img  onerror='$(this).attr(\"src\",\"/img/service/empty.png\")' alt='' src='/signs/matrix/show?id=".$RulesList[$i]['id']."' /><br>".$RulesList[$i]['desc'].$film."<br>"; 
                }
            };
        }
        $this->_helper->layout->setLayout('ajax');
        $this->view->imgSmall = $imgSmall;
        
    }
    
    /**
     * Show matrix full messages list set_type=8
     *
     * @module     Signs
     * @category   AJAX dashbaoard.js
     */
    public function showfullmessagesAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $set_type = 8;
        
        $imgFull=array('','','','');
        if (!empty($set_id)) {
            $RecMatrixTable = new Signs_Model_SignsMatrixMessage();
            $RulesList = $RecMatrixTable->getRecordsBySetId($set_id);
            if (!empty($RulesList)){

                for ($i=0; $i<4; $i++){
                    if ($RulesList[$i]['animation_frame']=='1'){
                        $film = "(<img alt='' src='/img/service/film.png' />)";
                    } else {
                        $film = "";  
                    }
                    $imgFull[$i]="<img  onerror='$(this).attr(\"src\",\"/img/service/empty.png\")' alt='' src='/signs/matrix/show?id=".$RulesList[$i]['id']."' /><br>".$RulesList[$i]['desc'].$film."<br>"; 
                }
            };
        }
        $this->_helper->layout->setLayout('ajax');
        $this->view->imgFull = $imgFull;
        
    }
    
     /**
     * Show calendars schedule list
     * @module     Signs
     * @category   AJAX dashboard.js
     */
    public function showcalendarAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        
        $Paramsets_model = new Signs_Model_SignsParamsets();
        
        $calendarsSelectList = $Paramsets_model->getHolidaysList();
        
        foreach ($calendarsSelectList as &$item) {
            $item['selected'] = ($item['id']==$set_id)?'selected':'';
        }
        
        $utils  = new Signs_Model_Utilities();
        
        $HolidaysList = $Paramsets_model->getHolidaysListById($set_id);

        if (count($HolidaysList)) {
            $HolidaysIds = array();
            foreach ($HolidaysList as $HolidaysOne) {
                $HolidaysIds[] = $HolidaysOne['id'];
            }
            $RecholidaysTable = new Signs_Model_SignsReccalendar();
            $RulesList = $RecholidaysTable->getScheduleRules($HolidaysIds);
            $utils->decodeRulesListH($RulesList);
            $HolidaysList = $utils->reformHolidaysList($HolidaysList);
            $utils->appendingRulesToHolidaysList($HolidaysList, $RulesList);
        }
        $this->view->HolidaysList = $HolidaysList;
        $this->view->calendar_id = $set_id;
        $this->view->calendarsSelectList = $calendarsSelectList;
        
        $this->_helper->layout->setLayout('ajax');
    }
}
