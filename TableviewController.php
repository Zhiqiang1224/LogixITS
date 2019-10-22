<?php
/**
 * Controller  "Table View"
 *
 * @module     Signs
 * @category   Controller
 * @since      Class available since Release 2.0
 * @deprecated  --
 */
class Signs_TableviewController extends wd_Controller {

    use Wd_ToForm;

    private $translate;
    protected $_redirector = null;

    private $modelList = array(
        1 => 'SP400',
        2 => 'SP500',
        3 => 'SP600'
    );

    /**
     * Check for authorization  befor start the class
     * Init Ajax content
     * 
     * @module     Signs
     * @category   Init function 
     */
    public function init() {
        parent::init();
         if (isset($_SESSION['pwd_expired'])) {
            $this->_redirect('Auth/renew');
        }
        $this->_helper->AjaxContext()
                ->addActionContext('getdata', 'json')
                ->initContext('json');
        $this->view->activ = 'locations';
        $this->view->menu_line = Ventrill_Definition::getMenuLine('locations');
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'index', 'default');
        }
        $this->view->menu_dispalay = TRUE;
        $this->translate = Zend_Registry::get('Zend_Translate');
        
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    
     /**
     * Refresh data in Table View
     * 
     * @module     Signs
     * @category   AJAX
     */
    public function getdataAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        $ParamSet_model = new Signs_Model_SignsParamsets();
        $ParamSet = $ParamSet_model->getSetPointID();

        if ($ParamSet <> NULL) {
            $SetPoint_model = new Signs_Model_SignsRecsetpoint();
            $SetPoint = $SetPoint_model->getSetPointParam($ParamSet['id']);
        }
        $Sysparams_model = new Signs_Model_SignsSysparams();
        $Sysparams = $Sysparams_model->getReadingsInterval();
        $invalidationInterval = $Sysparams['value'];
        
        //TODO rewrite as WD API  
        $SpeedClass = new Ventrill_Speed();
        // ----
        $speed_label = $SpeedClass->getSpeedLabel();
        
        $LocationsList = wd_API::getLocationsList();
        
        $timeSetPoint = date('U', time());
        $timeSetPoint = $timeSetPoint - $Sysparams['value'];
        $serverTimeOffset = Zend_Registry::get('ETime'); //OBSOLETE
        
        foreach ($LocationsList as $key => $value) {
            $current_time_at_location = date('U', strtotime(wd_API::getCurrentTimeAtLocation($value['lid'])));
            $tableCurData = new Signs_Model_SignsCurData();
            $cur_data = $tableCurData->getCurrentData($value['lid']);
            if ($cur_data === null)
              $timeSign = '';
            else
              $timeSign = $cur_data['time'];
            
            if (isset($value['time'])) {
                //$timeSign = date('U', strtotime($value['time']));
                $strTimeValue = $value['time'];
            } else {
                $strTimeValue = '';
            }

            if ($timeSign == '') {
                $strTime = '---';
            } else {
                if ($current_time_at_location - $timeSign < $invalidationInterval) {
                    $strTime = "<span id=\"batt_is_ok\">" . $strTimeValue . "</span>";
                } else {
                    $strTime = "<span id=\"batt_is_not_ok\">" . $strTimeValue . "</span>";
                }
            }
            
            $value['time'] = $strTime;
            
            if (isset($value['batt_voltage'])) {
                $strBatt_voltage = $value['batt_voltage'];
            } else {
                $strBatt_voltage = '';
            }
            
            if ($strBatt_voltage == '') {
                $strBatt_voltage = '---';
            } else {
                if ((isset($SetPoint) && $value['batt_voltage'] > $SetPoint['set_point'])
                    || (!isset($SetPoint) && $value['batt_voltage'] > 11)) {
                    $strBatt_voltage = "<span id=\"batt_is_ok\">" . $value['batt_voltage'] . "</span>";
                } else {
                    $strBatt_voltage = "<span id=\"batt_is_not_ok\">" . $value['batt_voltage'] . "</span>";
                }
            } //else
            $value['batt_voltage'] = $strBatt_voltage;
            $LocationsList[$key] = $value;
        } //foreach $LocationsList

        $stat_model = new Signs_Model_SignsStatRecords();
        //$stat_model = new Signs_Model_SignsCurData();
     
        $fast_stat = $Sysparams_model->getFastStat();
        switch ($fast_stat['value']) {
            case '0': $time = 5;
                //$timename = $this->translate->_('5 minutes');
                break;
            case '1': $time = 10;
                //$timename = $this->translate->_('10 minutes');
                break;
            case '2': $time = 15;
                //$timename = $this->translate->_('15 minutes');
                break;
            case '3': $time = 30;
                //$timename = $this->translate->_('30 minutes');
                break;
            case '4': $time = 60;
                //$timename = $this->translate->_('1 hour');
                break;
            case '5': $time = 120;
                //$timename = $this->translate->_('2 hours');
                break;
            case '6': $time = 1440;
                //$timename = $this->translate->_('24 hours');
                break;
        } //switch

        foreach ($LocationsList as  $value) {
            $lids[] = $value['lid'];
        }
      
        $stat_data = $stat_model->getAllLocattionsData($lids, $time);
        
        foreach ($stat_data as $key => $value) {
            $signs_stat_data[$value['location_id']] = $value;
        }
        
       /* //DEBUG:
        echo '<pre>';
        print_r($signs_stat_data);
        echo '</pre>';*/
        
       foreach ($LocationsList as $record) {
            /*
            if (!empty($record['timezone_id'])) {
                $time_shift = (int)$record['timezone_id'];
            } else {
                $time_shift = 0;
            }
            */
            $currentDataTable = new Signs_Model_SignsCurData();
            //$stat = $stat_model->getLastRecord($record['lid']);
            $stat = $currentDataTable->getCurrentData($record['lid']);
            //$stat = $stat_model->getCurrentData($record['lid']);
            
            if (isset($stat['time'])) {
                $record['time'] = $stat['time'];
            } else {
                $record['time'] = '';
            }

            if (isset($stat['batt_voltage'])) {
                $record['batt_voltage'] = $stat['batt_voltage'] . " V";
            } else {
                $record['batt_voltage'] = '';
            }

            // $stat_data = $stat_model->getLocattionsData($record['lid'], $time, $time_shift-$serverTimeOffset);
            
            $data = array();
            if (isset($signs_stat_data[$record['lid']])){
                $data = $signs_stat_data[$record['lid']];
            }



            /*foreach ($stat_data as $key => $value) {
                $data = $value;
                $data['curent'] = $timename;
                $data['avg_speed'] = round($data['avg_speed']);
            }*/
           

            if (isset($data['avg_speed85'])) {
                $SpeedClass->convertSpeedValueToShow($data['avg_speed85']);
                $avg_speed85 = round($data['avg_speed85']) . " " . $speed_label;
            } else {
                $avg_speed85 = '';
            }
           


            if (isset($data['avg_speed'])) {
                $SpeedClass->convertSpeedValueToShow($data['avg_speed']);
                $avg_speed = round($data['avg_speed']) . " " . $speed_label;
            } else {
                $avg_speed = '';
            }
            

           
            if (isset($data['max_speed'])) {
                $SpeedClass->convertSpeedValueToShow($data['max_speed']);
                $max_speed = $data['max_speed'] . " " . $speed_label;
            } else {
                $max_speed = '';
            }
            

           
            if (isset($data['min_speed'])) {
                $SpeedClass->convertSpeedValueToShow($data['min_speed']);
                $min_speed = $data['min_speed'] . " " . $speed_label;
            } else {
                $min_speed = '';
            }
           

           

            /*
            $timeSetPoint = date('U', time());
            $timeSetPoint = $timeSetPoint - $Sysparams['value'];
            */

            if ($avg_speed == '') {
                $avg_speed = '---';
            }
            if ($max_speed == '') {
                $max_speed = '---';
            }
            if ($min_speed == '') {
                $min_speed = '---';
            }

            //if (isset($data['count'])) {
            //    $Total_Vehicles = $data['count'];
            //   $currentDataTable = new Signs_Model_SignsCurData();
            //   $currentData = $currentDataTable->getCurrentData($record['lid']);
            
            if (isset($data['count'])) {
                $Total_Vehicles = $data['count'];
            } else {
                $Total_Vehicles = '---';
            }

            $strTime = $record['time'];
            
            if ($strTime == '') {
                $strTime = '---';
            } else {
                $timeSign = date('U', strtotime($record['time']));
                $current_time_at_location = date('U', strtotime(wd_API::getCurrentTimeAtLocation($record['lid'])));
                if ($current_time_at_location - $timeSign < $invalidationInterval) {
                    $strTime = "<span id=\"batt_is_ok\">" . $record['time'] . "</span>";
                } else {
                    $strTime = "<span id=\"batt_is_not_ok\">" . $record['time'] . "</span>";
                }
            }

            $strBatt_voltage = $record['batt_voltage'];
            if ($strBatt_voltage == '') {
                $strBatt_voltage = '---';
            } else {
                if ((isset($SetPoint) && $record['batt_voltage'] > $SetPoint['set_point']) 
                    || (!isset($SetPoint) && $record['batt_voltage'] > 11)) {
                    $strBatt_voltage = "<span id=\"batt_is_ok\">" . $record['batt_voltage'] . "</span>";
                } else {
                    $strBatt_voltage = "<span id=\"batt_is_not_ok\">" . $record['batt_voltage'] . "</span>";
                }
            }

            $ParametrsList[$record['lid']] = array(
                'lid' => $record['lid'],
                'Last_connect' => $strTime,
                'batt_voltage' => $strBatt_voltage,
                'avg_speed' => $avg_speed,
                'avg_speed85' => $avg_speed85,
                'max_speed' => $max_speed,
                'min_speed' => $min_speed,
                'count' => $Total_Vehicles
            );
        } //foreach $LocationsList

        $this->view->data = $ParametrsList;
    }

   
    /**
     * Show list of locations with Signs
     * 
     * @module     Signs
     * @category   Action
     */
    public function indexAction() {

        //  $this->view->metro = true;
        $this->view->tableview = true;
        $this->view->dataTables2 = true;

        $Sysparams_model = new Signs_Model_SignsSysparams();

        $locGroup_list = wd_API::getGroupInfo();

        $ParamSet_model = new Signs_Model_SignsParamsets();
        $ParamSet = $ParamSet_model->getSetPointID();

        $SetPoint_model = null;
        $SetPoint = null;
        if ($ParamSet <> NULL) {
            $SetPoint_model = new Signs_Model_SignsRecsetpoint();
            $SetPoint = $SetPoint_model->getSetPointParam($ParamSet['id']);
        }

        $Sysparams = $Sysparams_model->getReadingsInterval();

        $GroupList = array();
        foreach ($locGroup_list as $value) {
            $GroupList[$value['id']] = array(
                'id' => $value['id'],
                'name' => $value['name']
            );
        }

        $lids = array();
        $LocationsList = wd_API::getLocationsList();
        foreach ($LocationsList as $key => $value) {
            $lids[] = $value['lid'];
        }

        $settingsTable = new Signs_Model_SignsSysparams();
        $invalidationInterval = $settingsTable->getReadingsInterval(Zend_Auth::getInstance()->getIdentity()->company_id);
        $invalidationInterval = $invalidationInterval['value'];
        if (!$invalidationInterval) {
            $invalidationInterval = 300;
        }
        
        $alertArray = array();
        
        if (!empty($lids)) {
            $alerts = wd_API::getAlertsStatusForLids($lids, 'signs');
            foreach ($alerts as $value) {
                $alertArray[$value['lid']] = $value['count'];
            }
        }

        $signsCurDataTable = new Signs_Model_SignsCurData();
        
        $dataArray = array();
        
        if (!empty($lids)) {
            $data = $signsCurDataTable->getCurrentDataForLids($lids);
            foreach ($data as $value) {
                if (isset($value['time'])) {
                    $value['connected'] = 1;
                    $timeSign = date('U', strtotime($value['time']));
                    $location_id = $value['location_id'];
                    $current_time_at_location = date('U', strtotime(wd_API::getCurrentTimeAtLocation($location_id)));
                    /*
                    $popravka = ($value['time_zone'] - Zend_Registry::get('ETime')) * 60;
                    $timeSetPoint = date('U', time());
                    $timeSetPoint = $timeSetPoint - $invalidationInterval - $popravka;
                    */

                    if ($current_time_at_location - $timeSign > $invalidationInterval) {
                        $value['battary'] = 1;
                    } else {
                        $value['battary'] = 0;
                    }
                    
                    if ((isset($alertArray[$value['location_id']])) && (!empty($alertArray[$value['location_id']]))) {
                        $value['alert'] = 0;
                    } else {
                        $value['alert'] = 1;
                    }
                } else {
                    $value['connected'] = 0;
                }
                $dataArray[$value['location_id']] = $value;
            }
        }

        $this->decodeDirection($LocationsList);

        $timeSetPoint = date('U', time());
        $timeSetPoint = $timeSetPoint - $Sysparams['value'];
        foreach ($LocationsList as $key => $value) {
            if (isset($dataArray[$value['lid']]['time'])) {
                $strTime = $dataArray[$value['lid']]['time'];
                $timeSign = date('U', strtotime($strTime) );
            }  else {
                $strTime = '';
                $timeSign = date('U', null);
            }
            
            if ($strTime == '') {
                $strTime = '---';
            } else {
                $location_id = $value['lid'];
                $current_time_at_location = date('U', strtotime(wd_API::getCurrentTimeAtLocation($location_id)));
                if ($current_time_at_location - $timeSign <= $invalidationInterval) {
                    $strTime = "<span id=\"batt_is_ok\">" . $dataArray[$value['lid']]['time'] . "</span>";
                } else {
                    $strTime = "<span id=\"batt_is_not_ok\">" . $dataArray[$value['lid']]['time'] . "</span>";
                }
            }

            $value['time'] = $strTime;
            if (isset($dataArray[$value['lid']]['batt_voltage']))
                $strBatt_voltage = $dataArray[$value['lid']]['batt_voltage'];
            else
                $strBatt_voltage = '';
            
            if ($strBatt_voltage == '') {
                $strBatt_voltage = '---';
            } else {
                if ($dataArray[$value['lid']]['batt_voltage'] > $SetPoint['set_point']) {
                    $strBatt_voltage = "<span id=\"batt_is_ok\">" . $dataArray[$value['lid']]['batt_voltage'] . "</span>";
                } else {
                    $strBatt_voltage = "<span id=\"batt_is_not_ok\">" . $dataArray[$value['lid']]['batt_voltage'] . "</span>";
                }
            }
            
            $value['batt_voltage'] = $strBatt_voltage;
            if (isset($dataArray[$value['lid']]['sign_model'])) 
                $value['model'] = "SP" . $dataArray[$value['lid']]['sign_model'];
            else
                $value['model'] = '';
            if (isset($dataArray[$value['lid']]['serial']))
                $value['r_serial'] = $dataArray[$value['lid']]['serial'];
            else
                $value['r_serial'] = '';
		
	    if (isset($dataArray[$value['lid']]['fw_ver']))
                $value['fw'] = $dataArray[$value['lid']]['fw_ver'];
            else
                $value['fw'] = '';
            
            $GroupList[$value['group_id']]['locations'][] = $value;
        }
        $this->view->GroupList = $GroupList;
    }

}
