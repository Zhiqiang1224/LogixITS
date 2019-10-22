<?php

/**
 * Controller  "Beacons Schedules"
 *
 * @module     Signs
 * @category   Controller
 * @since      Class available since Release 2.0
 * @deprecated  --
 */
class Signs_BeaconsController extends wd_Controller
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
        $this->view->activ = 'config';
        $this->view->menu_line = Ventrill_Definition::getMenuLine('configurations');
        $this->view->menu_dispalay = TRUE;
        $this->_helper->AjaxContext()
                ->addActionContext('checksheduledelete', 'json')
                ->addActionContext('delete', 'json')
                ->initContext('json'); 
        
        $this->translate = Zend_Registry::get('Zend_Translate');
        
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'index', 'default');
        }
    }

    /**
     * Show list of Beacons Schedules
     * 
     * @module     Signs
     * @category   action
     */
    public function indexAction()
    {   
        if (Zend_Auth::getInstance()->getIdentity()->access_level < 3) {
            $this->redirect('/');
        }
	
        $this->view->modal = true;

        $ParamsetsTable = new Signs_Model_SignsParamsets();
        $BeaconsList = $ParamsetsTable->getBeaconsList();

        if (count($BeaconsList)) {
            $BeaconsIds = array();
            foreach ($BeaconsList as $BeaconsOne) {
                $BeaconsIds[] = $BeaconsOne['id'];
            }

            $RecbeaconsTable = new Signs_Model_SignsRecbschedule();
            $RulesList = $RecbeaconsTable->getScheduleRules($BeaconsIds);
            $this->decodeRulesList($RulesList);
            $BeaconsList = $this->reformBeaconsList($BeaconsList);
            $this->appendingRulesToBeaconsList($BeaconsList, $RulesList);
        }
        $this->view->BeaconsList = $BeaconsList;
    }

    /**
     * Associate Rules with Schedule
     * 
     * @module     Signs
     * @category    private function
     */
    private function appendingRulesToBeaconsList(&$BeaconsList, $RulesList)
    {
        foreach ($RulesList as $Rule) {
            $BeaconsList[$Rule['param_set_id']]['rules'][] = $Rule;
        }
    }

    /**
     * Reform List of Shedules to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function reformBeaconsList($BeaconsList)
    {
        $newBeaconsList = array();

        foreach ($BeaconsList as $BeaconsOne) {
            $newBeaconsList[$BeaconsOne['id']] = array(
                'id' => $BeaconsOne['id'],
                'name' => $BeaconsOne['name']
            );
        }
        return $newBeaconsList;
    }

    /**
     * Decode ruls of Shedules to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function decodeRulesList(&$RulesList)
    {
        $this->decodeSDayRulesList($RulesList);
        $this->decodeSTime($RulesList);
    }

    /**
     * Decode Day parametr in schedule to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function decodeSDayRulesList(&$RulesList)
    {
        foreach ($RulesList as &$rule) {
            $daycode = $rule['s_day'];
            if ($daycode <= 127) { // normal day
                $rule['date_binstr'] = decbin($daycode);
                $rule['MO'] = Ventrill_Definition::testbit($daycode, 0);
                $rule['TU'] = Ventrill_Definition::testbit($daycode, 1);
                $rule['WE'] = Ventrill_Definition::testbit($daycode, 2);
                $rule['TH'] = Ventrill_Definition::testbit($daycode, 3);
                $rule['FR'] = Ventrill_Definition::testbit($daycode, 4);
                $rule['ST'] = Ventrill_Definition::testbit($daycode, 5);
                $rule['SU'] = Ventrill_Definition::testbit($daycode, 6);
            } else { // special day sets
                $rule['MO'] = false;
                $rule['TU'] = false;
                $rule['WE'] = false;
                $rule['TH'] = false;
                $rule['FR'] = false;
                $rule['ST'] = false;
                $rule['SU'] = false;
            }
        }
    }

    /**
     * Decode Time parametr in schedule to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function decodeSTime(&$RulesList)
    {
        // time decode
        foreach ($RulesList as &$Rule) {
            $hours = floor($Rule['s_time_on'] / 60);
            if (strlen($hours) < 2) {
                $hours = '0' . $hours;
            }
            $minutes = ($Rule['s_time_on'] % 60);
            if (strlen($minutes) < 2) {
                $minutes = '0' . $minutes;
            }
            $Rule['s_time_formated_on'] = $hours . ':' . $minutes;

            $hours = floor($Rule['s_time_off'] / 60);
            if (strlen($hours) < 2) {
                $hours = '0' . $hours;
            }
            $minutes = ($Rule['s_time_off'] % 60);
            if (strlen($minutes) < 2) {
                $minutes = '0' . $minutes;
            }
            $Rule['s_time_formated_off'] = $hours . ':' . $minutes;
        }
    }

    /**
     * Check if schedule can be deleted
     * 
     * @module     Signs
     * @category   action
     */
    public function checksheduledeleteAction()
    {
        // action body
        $this->_helper->layout->disableLayout();

        $id = $this->_getParam('id');
        $RecscheduleTable = new Signs_Model_SignsRecbschedule();
        $CountRules = $RecscheduleTable->isEpmtySchedule($id);

        $locationsList = wd_API::getLocationsList();

        $ConfigsTable = new Signs_Model_SignsConfigs();
        $Locations = $ConfigsTable->getLidsFromBSchedual($locationsList, $id);

        if ($Locations) {
            $Radars = new Signs_Model_SignsRadars();
            $CountRadars = $Radars->getLocationsWithRadar($Locations);
        } else {
            $CountRadars = 0;
        }
        $CountLocations = count($Locations);
        if (!$CountRules) {
            $this->view->state = 'ok';
            $this->view->errors = $this->translate->_("Are you sure you want to delete this schedule?<br>");
            $this->view->title = $this->translate->_("Delete this schedule?");
        } else {
            $this->view->errors = $this->translate->_("Are you sure you want to delete this schedule?<br>") .
                    $this->translate->_("<br>Number of location(s) using this schedule = ") . $CountLocations ;
                   
            $this->view->title = $this->translate->_("Delete this schedule?");
        }
    }

    /**
     * Delete Beacon Schedule
     * 
     * @module     Signs
     * @category   action
     */
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $id = $this->getRequest()->getParam('id');
        
        //delete schedule from signs.param_sets:
        $ParamsetsTable = new Signs_Model_SignsParamsets();
        $ParamsetsTable->deleteSchedule($id);
        
        //add a dummy record to signs.param_sets, then create a sync request with that record's id:
        $paramsetsTable = new Signs_Model_SignsParamsets();
        $paramset_id = $paramsetsTable->addScheduleWithType('Delete Beacon Schedule', 30);
        
        //find all locations that use this schedule:
        $configsTable = new Signs_Model_SignsConfigs();
        $locations_arr = $configsTable->getLidsFromBSchedual(false, $id);
        if ($paramset_id && is_array($locations_arr)) {
          $locations = array();
          foreach ($locations_arr as $loc) {
            $locations[] = $loc['location_id'];
          }
          
          //add sync. request:
          $syncreqTable = new Signs_Model_SignsSyncReq();
          $saveData = array(
              'formdata'      => $locations,
              'sign_schedule' => $paramset_id
          );
          $syncreqTable->appendNewReqforSched($saveData);

          //unassign schedule from location(s):
          $updData = array(
              'formdata'      => $locations,
              'sign_schedule' => 0
          );
          $configsTable->setLocationsForBeacon($updData);
        } //if
        
        $state = 'ok';
        $this->view->state = $state;
    }
}
