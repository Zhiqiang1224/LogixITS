<?php

/**
 * Report Controller
 *
 * @module     Signs
 * @category   Controller
 * @since      Class available since Release 2.0
 * @deprecated  --
 */
class Signs_ReportsController extends wd_Controller
{

    private $translate;
    const WEEKEND_AVERAGE = 9;
    const WEEKDAY_AVERAGE = 8;


    /**
     * Decode ruls of Shedules to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function decodeRulesListH(&$RulesList)
    {
        $this->decodeSDayRulesListH($RulesList);
    }

    /**
     * Decode Day parametr in holidays schedule to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function decodeSDayRulesListH(&$RulesList)
    {
        // Decoding date
        foreach ($RulesList as &$rule) {
            $daycode = $rule['c_date'];
            $rule['c_date'] = date("m/d/Y", strtotime(date("d.m.Y", strtotime('2010-01-01')) . " +" . $daycode . " day"));
            $rule['c_date2'] = date("Y-m-d", strtotime(date("d.m.Y", strtotime('2010-01-01')) . " +" . $daycode . " day"));
            $daycode2 = $rule['c_len'];
            if ($daycode2 > 1) {
                $rule['c_len'] = date("m/d/Y", strtotime(date("d.m.Y", strtotime($rule['c_date2'])) . " +" . $daycode2 . " day"));
            }
            $day_mass = Ventrill_Definition::$days_code_name;
            $rule['day_type'] = $day_mass[$rule['day_type']]['name'];
        }
    }

    /**
     * Decode Day parametr in schedule to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function decodeRulesListS(&$RulesList, $SpeedClass)
    {
        $this->decodeSDayRulesList($RulesList);
        $this->decodeDisplayMessages($RulesList);
        $this->decodeSTimeS($RulesList);
        $this->decodeSpeedValue($RulesList, $SpeedClass);
    }

    /**
     * Decode Day parametr in beacons schedule to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function decodeRulesListB(&$RulesList)
    {
        $this->decodeSDayRulesList($RulesList);
        $this->decodeSTimeB($RulesList);
    }

    /**
     * Reform List of Shedules to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function reformHolidaysList($HolidaysList)
    {
        $newHolidaysList = array();
        foreach ($HolidaysList as $HolidaysOne) {
            $newHolidaysList[$HolidaysOne['id']] = array(
                'id' => $HolidaysOne['id'],
                'name' => $HolidaysOne['name']
            );
        }
        return $newHolidaysList;
    }

    /**
     * Associate Rules with Schedule
     * 
     * @module     Signs
     * @category    private function
     */
    private function appendingRulesToHolidaysList(&$HolidaysList, $RulesList)
    {
        foreach ($RulesList as $Rule) {
            $HolidaysList[$Rule['param_set_id']]['rules'][] = $Rule;
        }
    }

    /**
     * Decode Time parametr in schedule to Show
     * 
     * @module     Signs
     * @category    private function
     */
    private function decodeSTimeB(&$RulesList)
    {

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
            }
        }
    }

    /**
     * decode messages in the list
     *
     * @module     Signs
     * @category   private funcion 
     */
    private function decodeDisplayMessages(&$RulesList)
    {
        foreach ($RulesList as &$Rule) {
            $Rule['msg_above_min'] = Ventrill_Definition::$display_messages[$Rule['msg_above_min']];
            $Rule['msg_above_limit'] = Ventrill_Definition::$display_messages[$Rule['msg_above_limit']];
            $Rule['msg_above_tolerated'] = Ventrill_Definition::$display_messages[$Rule['msg_above_tolerated']];
            $Rule['msg_above_max'] = Ventrill_Definition::$display_messages[$Rule['msg_above_max']];
        }
    }

    /**
     * decode time in the list
     *
     * @module     Signs
     * @category   private funcion 
     */
    private function decodeSTimeS(&$RulesList)
    {

        foreach ($RulesList as &$Rule) {
            $hours = floor($Rule['s_time'] / 60);
            if (strlen($hours) < 2) {
                $hours = '0' . $hours;
            }
            $minutes = ($Rule['s_time'] % 60);
            if (strlen($minutes) < 2) {
                $minutes = '0' . $minutes;
            }
            $Rule['s_time_formated'] = $hours . ':' . $minutes;
        }
    }

    /**
     * decode a speed in the list
     *
     * @module     Signs
     * @category   private funcion 
     */
    private function decodeSpeedValue(&$RulesList, $SpeedClass)
    {
        foreach ($RulesList as &$rule) {
            $this->analisSpeedValue($rule['min_speed'], $SpeedClass);
            $this->analisSpeedValue($rule['max_speed'], $SpeedClass);
            $this->analisSpeedValue($rule['speed_limit'], $SpeedClass);
            $this->analisSpeedValue($rule['speed_tolerated'], $SpeedClass);
            $this->analisSpeedValue($rule['blink_dig_speed'], $SpeedClass);
            $this->analisSpeedValue($rule['blink_msg_speed'], $SpeedClass);
            $this->analisSpeedValue($rule['speed_strobe'], $SpeedClass);
        }
    }

    /**
     * analyse a speed in the list
     *
     * @module     Signs
     * @category   private funcion 
     */
    private function analisSpeedValue(&$speed, $SpeedClass)
    {
        $this->speed_label = $SpeedClass->getSpeedLabel();
        $this->off_message = Ventrill_Definition::$off_speed_message;
        $speed = (int) $speed;

        if ($speed == 0) {
            $speed = $this->off_message;
        } else {
            $SpeedClass->convertSpeedValueToShow($speed);
            $speed .= ' ' . $this->speed_label;
        }
    }

    /**
     * organize the rules in the list
     *
     * @module     Signs
     * @category   private funcion 
     */
    private function reformScheduleList($ScheduleList)
    {
        $newScheduleList = array();

        foreach ($ScheduleList as $ScheduleOne) {
            $newScheduleList[$ScheduleOne['id']] = array(
                'id' => $ScheduleOne['id'],
                'name' => $ScheduleOne['name']
            );
        }

        return $newScheduleList;
    }

    /**
     * Add rules to the list
     *
     * @module     Signs
     * @category   private funcion 
     */
    private function appendingRulesToScheduleList(&$ScheduleList, $RulesList)
    {
        foreach ($RulesList as $Rule) {
            $ScheduleList[$Rule['param_set_id']]['rules'][] = $Rule;
        }
    }

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
                ->addActionContext('getlocationdata', 'json')
                ->addActionContext('step2', 'json')
                ->addActionContext('savechart', 'json')
                ->initContext('json');

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_helper->redirector('index', 'index', 'default');
        }
        $this->translate = Zend_Registry::get('Zend_Translate');
    }

    /**
     * show sign param in
     * Synchronization Event Log Report
     *
     * @module     Signs
     * @category   AJAX (Sign Parametrs) reports.js
     */
    public function showsignparamAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $SettinsTable = new Signs_Model_SignsSignparams();
        $AdvSettins = $SettinsTable->getAdvancedSettingsBySet_id($set_id);
        $this->view->advansesettings = !empty($AdvSettins) ? $AdvSettins : NULL;
        //$this->_helper->layout->setLayout('reports');
        $this->_helper->layout->disableLayout();
    }

    /**
     * get communication data in
     * Synchronization Event Log Report
     *
     * @module     Signs
     * @category   AJAX reports.js
     */
    public function getcommdataAction()
    {
        $this->_helper->layout->disableLayout();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $set_id = $this->getRequest()->getParam('id');
            $Paramsets_model = new Signs_Model_SignsParamsets();
            $Type_sinq = $Paramsets_model->getSinqType($set_id);
            if ($Type_sinq['type']) {
                $this->view->name = $Type_sinq['name'];
                $this->view->type = $Type_sinq['type'];
            } else {
                $this->view->type = 2;
            }
        }
    }

    /**
     * show schedule list in
     * Synchronization Event Log Report
     *
     * @module     Signs
     * @category   AJAX reports.js
     */
    public function showscheduleAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $Paramsets_model = new Signs_Model_SignsParamsets();
        $ScheduleList = $Paramsets_model->getScheduleListById($set_id);

        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();

        if (count($ScheduleList)) {
            $ScheduleIds = array();
            foreach ($ScheduleList as $ScheduleOne) {
                $ScheduleIds[] = $ScheduleOne['id'];
            }
            $SpeedClass = new Ventrill_Speed();
            $RecscheduleTable = new Signs_Model_SignsRecschedule();
            $RulesList = $RecscheduleTable->getScheduleRules($ScheduleIds);

            $this->decodeRulesListS($RulesList, $SpeedClass);

            $ScheduleList = $this->reformScheduleList($ScheduleList);
            $this->appendingRulesToScheduleList($ScheduleList, $RulesList);
        } else {
          if($set_id!=0){
            $ScheduleIds = array();
            $ScheduleIds[] = $set_id;
            $SpeedClass = new Ventrill_Speed();
            $RecscheduleTable = new Signs_Model_SignsRecschedule();
            $RulesList = $RecscheduleTable->getScheduleRules($ScheduleIds);

            $this->decodeRulesListS($RulesList, $SpeedClass);

            $ScheduleList = $this->reformScheduleList($ScheduleList);
            $this->appendingRulesToScheduleList($ScheduleList, $RulesList);
           }else{
             unset($ScheduleList);
           }
        }

        $this->view->ScheduleList = $ScheduleList;
        //$this->_helper->layout->setLayout('reports');
        $this->_helper->layout->disableLayout();
    }

    /**
     * show beacons schedule list in
     * Synchronization Event Log Report
     *
     * @module     Signs
     * @category   AJAX reports.js
     */
    public function showbscheduleAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $Paramsets_model = new Signs_Model_SignsParamsets();
        $BeaconsList = $Paramsets_model->getBeaconsListById($set_id);

        if (count($BeaconsList)) {
            $BeaconsIds = array();
            foreach ($BeaconsList as $BeaconsOne) {
                $BeaconsIds[] = $BeaconsOne['id'];
            }
            $RecbeaconsTable = new Signs_Model_SignsRecbschedule();
            $RulesList = $RecbeaconsTable->getScheduleRules($BeaconsIds);
            $this->decodeRulesListB($RulesList);
            $BeaconsList = $this->reformBeaconsList($BeaconsList);
            $this->appendingRulesToBeaconsList($BeaconsList, $RulesList);
        }
        $this->view->BeaconsList = $BeaconsList;
        //$this->_helper->layout->setLayout('reports');
        $this->_helper->layout->disableLayout();
    }

    /**
     * show calendars schedule list in
     * Synchronization Event Log Report
     *
     * @module     Signs
     * @category   AJAX reports.js
     */
    public function showcalendarAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $Paramsets_model = new Signs_Model_SignsParamsets();
        $HolidaysList = $Paramsets_model->getHolidaysListById($set_id);

        if (count($HolidaysList)) {
            $HolidaysIds = array();
            foreach ($HolidaysList as $HolidaysOne) {
                $HolidaysIds[] = $HolidaysOne['id'];
            }
            $RecholidaysTable = new Signs_Model_SignsReccalendar();
            $RulesList = $RecholidaysTable->getScheduleRules($HolidaysIds);
            $this->decodeRulesListH($RulesList);
            $HolidaysList = $this->reformHolidaysList($HolidaysList);
            $this->appendingRulesToHolidaysList($HolidaysList, $RulesList);
        }
        $this->view->HolidaysList = $HolidaysList;
        $this->_helper->layout->setLayout('ajax');
    }

    /**
     * show  show messages list in
     * Synchronization Event Log Report
     *
     * @module     Signs
     * @category   AJAX reports.js
     */
    public function showmessagesAction()
    {
        $set_id = isset($_GET['id']) ? $_GET['id'] : NULL;
        $RecMatrixTable = new Signs_Model_SignsMatrixMessage();
        $RulesList = $RecMatrixTable->getRecordsBySetId($set_id);
        //$this->_helper->layout->setLayout('reports');
        $this->_helper->layout->disableLayout();
        $this->view->MatrixsList = $RulesList;
    }

    /**
     * Show weeks on selected date
     *
     * @module     Signs
     * @category   AJAX 
     */
    public function weeksAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //Show weeks on selected date
        if ((!isset($_POST['year']) || ((int) $_POST['year'] == 0)) || (!isset($_POST['month']) || ((int) $_POST['month'] == 0))) {
            echo 'Error';
        } else {
            $year = (int)$_POST['year'];
            $month = (int)$_POST['month'];

            if ($month == 12) {
                $finish_month = 1;
            } else {
                $finish_month = $month + 1;
            }

            $date = mktime(0, 0, 0, $month, 1, $year);

            if (date('D', $date) == 'Mon') {
                $start_date = date('m/d/Y', $date);
            } else {
                $start_date = date('m/d/Y', $date);
                while (date('D', strtotime($start_date)) != 'Mon') {
                    $start_date = date("m/d/Y", strtotime($start_date . " -1 day"));
                }
            }
            $count = 0;
            while (date('n', strtotime($start_date)) != $finish_month) {
                if (isset($s_week[$count])) {
                    if ($s_week[$count] == $start_date) {
                        echo '<input type="checkbox" checked="checked" class="weeks"  name="week[' . $count . ']" value="' . $start_date . '"/><span id="fweek' . $count . '">' . date("m/d/Y", strtotime($start_date)) . '</span> - <span id="tweek' . $count . '">' . date("m/d/Y", strtotime($start_date . " +6 day")) . '</span><br/>';
                    } else
                        echo '<input type="checkbox" class="weeks"  name="week[' . $count . ']" value="' . $start_date . '"/><span id="fweek' . $count . '">' . date("m/d/Y", strtotime($start_date)) . '</span> - <span id="tweek' . $count . '">' . date("m/d/Y", strtotime($start_date . " +6 day")) . '</span><br/>';
                } else
                    echo '<input type="checkbox" class="weeks"   name="week[' . $count . ']" value="' . $start_date . '"/><span id="fweek' . $count . '">' . date("m/d/Y", strtotime($start_date)) . '</span> - <span id="tweek' . $count . '">' . date("m/d/Y", strtotime($start_date . " +6 day")) . '</span><br/>';
                $count++;
                $start_date = date("m/d/Y", strtotime($start_date . " +7 day"));
            }

            // Script to check weeks
            echo '<script type="text/javascript">';
            echo '
            $("input[class=weeks]").click(function(){
                    if ($(this).attr("checked"))
                    {
                        var myRe = /(\d+)/ig;
                        var str = $(this).attr("name");
                        var myArray = myRe.exec(str);
                        var number = myArray[0];
                        var number1 = number-1;
                        var number2 = number-1+2;
                        var first = 1;
                        for (var i=0; i<' . $count . '; i++)
                        {
                            if (($("input[name=\"week["+i+"]\"]").attr("checked")) && (i!=number))
                            first = 0;
                        }
                        if (($("input[name=\"week["+(number1)+"]\"]").attr("checked")) || ($("input[name=\"week["+(number2)+"]\"]").attr("checked")) || (first==1)) {
                            first = 0;
                            
                        } else {
                        
                            ShowAlert("Report can only be generated with a contiguous date range!\nPlease select a contiguous date range without gaps between the first date and last date of the interval.","Warning");
                            $(this).attr("checked",false);
                        }

                    } else {
                        
                        var myRe = /(\d+)/ig;
                        var str = $(this).attr("name");
                        var myArray = myRe.exec(str);
                        var number = myArray[0];
                        var number1 = number-1;
                        var number2 = number-1+2;
                        if (($("input[name=\"week["+(number1)+"]\"]").attr("checked")) && ($("input[name=\"week["+(number2)+"]\"]").attr("checked"))) {
                            $(this).attr("checked",true);
                        }
                    };
                     CheckDataRange();
                })';
            echo '</script>';
        }
    }

    /**
     * Show 2 weeks on selected date
     *
     * @module     Signs
     * @category   AJAX
     */
    public function twoweeksAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //Show weeks on selected date
        if ((!isset($_POST['year']) || ((int) $_POST['year'] == 0)) || (!isset($_POST['month']) || ((int) $_POST['month'] == 0))) {
            echo 'Error';
        } else {
            $year = (int)$_POST['year'];
            $month = (int)$_POST['month'];

            if ($month == 12) {
                $finish_month = 1;
            } else {
                $finish_month = $month + 1;
            }

            $date = mktime(0, 0, 0, $month, 1, $year);

            if (date('D', $date) == 'Mon') {
                $start_date = date('m/d/Y', $date);
            } else {
                $start_date = date('m/d/Y', $date);
                while (date('D', strtotime($start_date)) != 'Mon') {
                    $start_date = date("m/d/Y", strtotime($start_date . " -1 day"));
                }
            }
            $count = 0;
            while (date('n', strtotime($start_date)) != $finish_month) {
                if (isset($s_week[$count])) {
                    if ($s_week[$count] == $start_date) {
                        echo '<input type="checkbox" checked="checked" class="twoweeks"  name="weeks[' . $count . ']" value="' . $start_date . '"/><span id="fweeks' . $count . '">' . date("m/d/Y", strtotime($start_date)) . '</span> - <span id="tweeks' . $count . '">' . date("m/d/Y", strtotime($start_date . " +6 day")) . '</span><br/>';
                    } else
                        echo '<input type="checkbox" class="twoweeks"  name="weeks[' . $count . ']" value="' . $start_date . '"/><span id="fweeks' . $count . '">' . date("m/d/Y", strtotime($start_date)) . '</span> - <span id="tweeks' . $count . '">' . date("m/d/Y", strtotime($start_date . " +6 day")) . '</span><br/>';
                } else
                    echo '<input type="checkbox" class="twoweeks"  name="weeks[' . $count . ']" value="' . $start_date . '"/><span id="fweeks' . $count . '">' . date("m/d/Y", strtotime($start_date)) . '</span> - <span id="tweeks' . $count . '">' . date("m/d/Y", strtotime($start_date . " +6 day")) . '</span><br/>';
                $count++;
                $start_date = date("m/d/Y", strtotime($start_date . " +7 day"));
            }

            // Script to check weeks
            echo '<script type="text/javascript">';
            echo '
            $("input[class=twoweeks]").click(function(){
                    if ($(this).attr("checked"))
                    {
                        var myRe = /(\d+)/ig;
                        var str = $(this).attr("name");
                        var myArray2 = myRe.exec(str);
                        var number = myArray2[0];
                        var number1 = number-1;
                        var number2 = number-1+2;
                        var first = 1;
                        for (var i=0; i<' . $count . '; i++)
                        {
                            if (($("input[name=\"weeks["+i+"]\"]").attr("checked")) && (i!=number))
                            first = 0;
                        }
                        if (($("input[name=\"weeks["+(number1)+"]\"]").attr("checked")) || ($("input[name=\"weeks["+(number2)+"]\"]").attr("checked")) || (first==1)) {
                            first = 0;
                          
                        } else {
                        ShowAlert("' . $this->translate->_("Report can only be generated with a contiguous date range!\nPlease select a contiguous date range without gaps between the first date and last date of the interval.") . '","' . $this->translate->_("Warning") . '");

                            $(this).attr("checked",false);
                        }

                    } else {
                        var myRe = /(\d+)/ig;
                        var str = $(this).attr("name");
                        var myArray2s = myRe.exec(str);
                        var number = myArray2[0];
                        var number1 = number-1;
                        var number2 = number-1+2;
                        if (($("input[name=\"weeks["+(number1)+"]\"]").attr("checked")) && ($("input[name=\"weeks["+(number2)+"]\"]").attr("checked"))) {
                            $(this).attr("checked",true);
                        }
                    }
                    CheckDataRange2();
                })';
            echo '</script>';
        }
    }

    /**
     * Show list of Report Types
     *
     * @module     Signs
     * @category   action
     */
    public function indexAction()
    {

    }

    /**
     * Start Weekly report 
     *  //signs/reports/weekly
     *
     * @module     Signs
     * @category   action
     */
    public function weeklyAction()
    {
        $this->view->reports_type = Ventrill_Definition::$reports_type;
        $this->view->reports = true;

        $locations = wd_API::getLocationsList();
        $this->view->locations = $locations;
        $this->view->data_types = Ventrill_Definition::$reports_columns;
    }

    /**
     * Start comparison report 
     *   //signs/reports/comparison
     *
     * @module     Signs
     * @category   action
     */
    public function comparisonAction()
    {
        $this->view->reports_type = Ventrill_Definition::$reports_type;
        $this->view->reports = true;

        $locations = wd_API::getLocationsList();
        $this->view->locations = $locations;
        $this->view->data_types = Ventrill_Definition::$reports_columns;
    }

    /**
     * Start power supply report 
     *   //signs/reports/power
     *
     * @module     Signs
     * @category   action
     */
    public function powerAction()
    {
        $this->view->reports_type = Ventrill_Definition::$reports_type;
        $this->view->reports = true;

        $locations = wd_API::getLocationsList();
        $this->view->locations = $locations;
        $this->view->data_types = Ventrill_Definition::$reports_columns;
    }

    /**
     * Start custom report 
     *   //signs/reports/custom
     *
     * @module     Signs
     * @category   action
     */
    public function customAction()
    {   

        //Ventrill_Definition::$reports_columns[1]['name'] =
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $this->view->reports_type = Ventrill_Definition::$reports_type;
        $this->view->reports = true;
        $this->view->headScript()->appendFile('/wd/js/notParse/jquery/table2CSV.js');
        $this->view->float = true;

        $locations = wd_API::getLocationsList();
        $this->view->locations = $locations;
        $this->view->data_types = Ventrill_Definition::$reports_columns;
        $this->view->back_link = '/signs/reports/index';
        
        //$this->view->bootstrap_css = true;
        //$this->view->bootstrap_js = true;
        
    }

    /**
     * Start Speed Range report 
     *    //signs/reports/srange
     *
     * @module     Signs
     * @category   action
     */
    public function srangeAction()
    {
        $this->view->reports_type = Ventrill_Definition::$reports_type;
        $this->view->reports = true;
        $this->view->headScript()->offsetSetFile(99, '/wd/js/notParse/jquery/table2CSV.js');
        $this->view->float = true;

        $locations = wd_API::getLocationsList();
        $this->view->locations = $locations;
        $this->view->data_types = Ventrill_Definition::$reports_columns;
    }

    /**
     * Start sync log report 
     *     //signs/reports/synclog
     *
     * @module     Signs
     * @category   action
     */
    public function synclogAction()
    {
        $this->view->reports_type = Ventrill_Definition::$reports_type;
        $this->view->reports = true;
        $this->view->headScript()->offsetSetFile(99, '/wd/js/notParse/jquery/table2CSV.js');
        $this->view->float = true;

        $locations = wd_API::getLocationsList();
        $this->view->locations = $locations;
        $this->view->data_types = Ventrill_Definition::$reports_columns;
    }

    //--------------------------------------------------------- START STEP 2 Actions   ----------------------------------------  
    /**
     * get available date range
     *
     * @module     Signs
     * @category   AJAX  Changed Location >> reports.js
     */
    public function getdaterangeAction()
    {                                                                          //  step2
        // 2 step - redirect to report type code
        $id = $this->getRequest()->getParam('lid');
        $type = $this->getRequest()->getParam('type');

        $report_type = (int) $_POST['report_type'];

        $_SESSION['Remember']['step1'] = $report_type;
        $_SESSION['reports']['location'] = (int) $id;
        switch ($type) {
            case 1:
                $this->_helper->redirector('step3w');
                break;
            case 2:
                $this->_helper->redirector('step3c');
                break;
            case 3:
                $this->_helper->redirector('step3cu');
                break;
            case 4:
                $this->_helper->redirector('step3bv');
                break;
            case 5:
                $this->_helper->redirector('step3sb');
                break;
            case 6:
                $this->_helper->redirector('step3ar');
                break;
            case 7:
                $this->_helper->redirector('step3cl');
                break;
            case 8:
                $this->_helper->redirector('step3cr');
                break;
            default:
                $this->_helper->redirector('step3w');
        }
        $this->_helper->layout->disableLayout();
        $this->view->type = $type;
    }

    //--------------------------------------------------------- START STEP 3 Actions   ----------------------------------------  
    /**
     * weekly report wizard
     *
     * @module     Signs
     * @category    AJAX  Choose Location >> reports.js >> getdaterangeAction
     */
    public function step3wAction()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $statRecords = new Signs_Model_SignsStatRecords();
        $DateInterval = $statRecords->getAvailableDateRange($_SESSION['reports']['location']);
        $temp_item = $statRecords->getRecordsYear($_SESSION['reports']['location']);
        $max_stat_records_year = $temp_item[0]['max_year'];
        $this->view->max_stat_records_year = $max_stat_records_year;
        $min_stat_records_year = $temp_item[0]['min_year'];
        $this->view->min_stat_records_year = $min_stat_records_year;
        $this->view->months_code_name = Ventrill_Definition::$months_code_name;
        $this->view->DateInterval = $DateInterval;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->data_types = Ventrill_Definition::$reports_data_types;
    }

    /**
     * Comparison report wizard
     *
     * @module     Signs
     * @category    AJAX  Choose Location >> reports.js >> getdaterangeAction
     */
    public function step3cAction()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $statRecords = new Signs_Model_SignsStatRecords();
        // 3 step - Comparison Report code
        $DateInterval = $statRecords->getAvailableDateRange($_SESSION['reports']['location']);
        $temp_item = $statRecords->getRecordsYear($_SESSION['reports']['location']);
        $max_stat_records_year = $temp_item[0]['max_year'];
        $this->view->max_stat_records_year = $max_stat_records_year;
        $min_stat_records_year = $temp_item[0]['min_year'];
        $this->view->min_stat_records_year = $min_stat_records_year;
        $this->view->months_code_name = Ventrill_Definition::$months_code_name;
        $this->view->DateInterval = $DateInterval;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->data_types = Ventrill_Definition::$reports_data_types;
    }

    /**
     * Power Supply Chart wizard
     *
     * @module     Signs
     * @category    AJAX  Choose Location >> reports.js >> getdaterangeAction
     */
    public function step3bvAction()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $statRecords = new Signs_Model_SignsStatRecords();
        $DateInterval = $statRecords->getAvailableDateRange($_SESSION['reports']['location']);
        $temp_item = $statRecords->getRecordsYear($_SESSION['reports']['location']);
        $max_stat_records_year = $temp_item[0]['max_year'];
        $this->view->max_stat_records_year = $max_stat_records_year;
        $min_stat_records_year = $temp_item[0]['min_year'];
        $this->view->min_stat_records_year = $min_stat_records_year;
        $this->view->months_code_name = Ventrill_Definition::$months_code_name;
        $this->view->DateInterval = $DateInterval;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
    }

    /**
     * Count by Speed Range Report wizard
     *
     * @module     Signs
     * @category    AJAX  Choose Location >> reports.js >> getdaterangeAction
     */
    public function step3sbAction()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $statRecords = new Signs_Model_SignsStatRecords();
        $DateInterval = $statRecords->getAvailableDateRange($_SESSION['reports']['location']);
        $temp_item = $statRecords->getRecordsYear($_SESSION['reports']['location']);
        $max_stat_records_year = $temp_item[0]['max_year'];
        $this->view->max_stat_records_year = $max_stat_records_year;
        $min_stat_records_year = $temp_item[0]['min_year'];
        $this->view->min_stat_records_year = $min_stat_records_year;
        $this->view->months_code_name = Ventrill_Definition::$months_code_name;
        $this->view->DateInterval = $DateInterval;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
    }

    /**
     * Custom Report wizard
     *
     * @module     Signs
     * @category    AJAX  Choose Location >> reports.js >> getdaterangeAction
     */
    public function step3cuAction()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $statRecords = new Signs_Model_SignsStatRecords();
        $DateInterval = $statRecords->getAvailableDateRange($_SESSION['reports']['location']);
        $temp_item = $statRecords->getRecordsYear($_SESSION['reports']['location']);
        $max_stat_records_year = $temp_item[0]['max_year'];
        $this->view->max_stat_records_year = $max_stat_records_year;
        $min_stat_records_year = $temp_item[0]['min_year'];
        $this->view->min_stat_records_year = $min_stat_records_year;
        $this->view->records_types = Ventrill_Definition::$reports_groups_records;
        $this->view->data_types = Ventrill_Definition::$reports_columns;
        $this->view->information_type = Ventrill_Definition::$reports_information_type;
        $this->view->months_code_name = Ventrill_Definition::$months_code_name;
        $this->view->DateInterval = $DateInterval;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        if (isset($_SESSION['reports']['data_type'])) {
            $this->view->data_type = $_SESSION['reports']['data_type'];
        } else {
            $this->view->data_type = null;
        }
        
        $sysparams_model = new Application_Model_DbTable_Sysparams();
        $units_type = $sysparams_model->getSpeedUnit();
        if ($units_type['value'] == 1) {
            $this->view->range_from = 10;
            $this->view->range_to = 55;
        } else {

            $this->view->range_from = 20;
            $this->view->range_to = 90;
        }
        $this->view->units_type = $units_type['value'];
    }

    /**
     * Synchronization Event Log Report wizard
     *
     * @module     Signs
     * @category    AJAX  Choose Location >> reports.js >> getdaterangeAction
     */
    public function step3clAction()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $syncreq = new Signs_Model_SignsSyncReq();
        $servicereq = new Signs_Model_SignsServicereq();
        $temp_item = $servicereq->getAvailableDateRange($_SESSION['reports']['location']);
        $temp_item2 = $syncreq->getAvailableDateRange($_SESSION['reports']['location']);

        if (((!isset($temp_item)) || empty($temp_item)) && ((!isset($temp_item2)) || empty($temp_item2))) {
            $temp_item['max_date'] = 0;
            $temp_item['min_date'] = 0;
        } else {
            if ($temp_item['min_date']==""){
                $temp_item['min_date'] = $temp_item2['min_date'];
            }
            if ($temp_item['max_date'] == "") {
                $temp_item['max_date'] = $temp_item2['max_date'];
            }

            if ($temp_item['max_date'] > $temp_item2['max_date']) {
                $temp_item['max_date'] = $temp_item['max_date'];
            } else {
                $temp_item['max_date'] = $temp_item2['max_date'];
            }
            if ($temp_item['min_date'] < $temp_item2['min_date']) {
                $temp_item['min_date'] = $temp_item['min_date'];
            } else {
                $temp_item['min_date'] = $temp_item2['min_date'];
            }
        }

        if (($temp_item['min_date'] > 0) && ($temp_item['max_date'] > 0)) {
            $max_stat_records_year = date('Y', strtotime($temp_item['max_date']));
            $this->view->max_stat_records_year = $max_stat_records_year;

            $min_stat_records_year = date('Y', strtotime($temp_item['min_date']));

            $this->view->min_stat_records_year = $min_stat_records_year;
        }

        $this->view->months_code_name = Ventrill_Definition::$months_code_name;
        $this->view->DateInterval = $temp_item;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
    }

//--------------------------------------------------------- START STEP 4 Actions   ----------------------------------------  
    /**
     * build Custom Report
     *
     * @module     Signs
     * @category   AJAX reports.js >> buildCustomReport() 
     */
    public function cubuildAction()
    {
        $this->_helper->layout->disableLayout();

        if ((isset($_POST)) && (!empty($_POST))) {
            $data_type = Array();

            for ($i = 1; $i <= 14; $i++) {
                if (isset($_POST['data_type_' . $i]) && ($_POST['data_type_' . $i]) == "true") {
                    $data_type[$i] = 1;
                } else {
                    $data_type[$i] = 0;
                }
            }
            $_SESSION['reports']['data_type'] = $data_type;
        }

        $_SESSION['reports']['group_records'] = $_POST['group_records'];
        $_SESSION['reports']['start_date'] = $_POST['start_date'];
        $_SESSION['reports']['end_date'] = $_POST['end_date'];
        $_SESSION['reports']['report_name'] = $_POST['report_name'];
        $_SESSION['reports']['information_type'] = $_POST['information_type'];
        $_SESSION['reports']['size'] = $_POST['size'];
        $_SESSION['reports']['range_from'] = $_POST['range_from'];
        $_SESSION['reports']['speed_limit'] = $_POST['speed_limit'];

        $sysparams_model = new Application_Model_DbTable_Sysparams();
        $units_type = $sysparams_model->getSpeedUnit();
        if ($units_type['value'] == 1) {
            $range_to = str_replace("km/h", "", $_POST['range_to']);
            $_SESSION['reports']['range_to'] = $range_to;
        } else {
            $range_to = str_replace("mph", "", $_POST['range_to']);
            $_SESSION['reports']['range_to'] = $range_to;
        }


        $Report = new Report_Custom([
            "locationID" => $_SESSION['reports']['location'],
            "startDate" => $_POST['start_date'],
            "endDate" => $_POST['end_date'],
            "type" => $data_type,
            "groupRecords" => $_POST['group_records'],
            "reportName" => $_POST['report_name'],
            "informationType" => $_POST['information_type'],
            "size" => $_POST['size'],
            "rangeFrom" => $_POST['range_from'],
            "rangeTo" => $range_to,
            "speedLimit" => $_POST['speed_limit'],
            "units" => $units_type['value']
        ]);
        $Report->build();


        $this->view->report = $Report;
        $Report->saveChart();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $this->view->data_type_name = Ventrill_Definition::$reports_groups_records[$_SESSION['reports']['group_records']]['name'];

        $configsTbl = new Signs_Model_SignsConfigs();
        $configs = $configsTbl->getScheduleByLID((int) $_SESSION['reports']['location']);

        $recsheduleTbl = new Signs_Model_SignsRecschedule();
       
        if(!empty($configs['r_param_set_id'])){
            $recshedule = $recsheduleTbl->getSpeedLimit($configs['r_param_set_id']);
        } else {
            $recshedule = '';
        }
        

        $this->view->status_result = 1;
        $this->view->schedual_speed_limit = $recshedule;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];
        $this->view->speed_limit = $_SESSION['reports']['speed_limit'];
    }

    /**
     * build Summary Comparison Report
     *
     * @module     Signs
     * @category   AJAX reports.js >> buildCustomReport() 
     */
    public function cbuildAction()
    {
       $this->_helper->layout->disableLayout();
         if ($_SESSION['SysParams']['speed_units']==1) {
             $speed_label = 'mph';
         }else{
             $speed_label = 'km/h';
         }
        
        if (isset( $_POST['data_type'])) {
            $this->view->data_types = Ventrill_Definition::$reports_data_types[(int) $_POST['data_type']]['name'];
        }
        if (isset($_POST['week'])) {
            foreach ($_POST['week'] as $key => $value) {
                if (!isset($first_key)) {
                    $start_date = $value;
                    $end_date = date("m/d/Y", strtotime($start_date . " +6 day"));
                } else {
                    if ($first_key == ($key - 1)) {
                        $end_date = date("m/d/Y", strtotime($value . " +6 day"));
                    } else {
                        $_SESSION['reports']['step4c']['err'] = 1;
                        $this->_helper->redirector('step3c');
                    }
                }
                $first_key = $key;
            }
        }

        if (isset($_POST['weeks'])) {
            foreach ($_POST['weeks'] as $key => $value) {
                if (!isset($first_key_2)) {
                    $start_date_2 = $value;
                    $end_date_2 = date("m/d/Y", strtotime($start_date_2 . " +6 day"));
                } else {
                    if ($first_key_2 == ($key - 1)) {
                        $end_date_2 = date("m/d/Y", strtotime($value . " +6 day"));
                    } else {
                        $this->_helper->redirector('step2c');
                    }
                }
                $first_key_2 = $key;
            }
        }

        $_SESSION['reports']['start_date'] = $start_date;
        $_SESSION['reports']['end_date'] = $end_date;
        $_SESSION['reports']['start_date_2'] = $start_date_2;
        $_SESSION['reports']['end_date_2'] = $end_date_2;
        $_SESSION['reports']['speed_limit'] = (int) $_POST['speed_limit'];

        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();

        $difference_in_days = $this->differenceInDaysCalculate($_SESSION['reports']['start_date'], $_SESSION['reports']['end_date']);
        $difference_in_days2 = $this->differenceInDaysCalculate($_SESSION['reports']['start_date_2'], $_SESSION['reports']['end_date_2']);

        $this->view->status_result = 1;
        $wdays = Ventrill_Definition::$wdays;
        $Report = new Report_Comparison($_SESSION['reports']['location'], $_SESSION['reports']['start_date'], $_SESSION['reports']['end_date']);
        $Report->compareTo($_SESSION['reports']['start_date_2'], $_SESSION['reports']['end_date_2']);

        $Report->setSpeedLimit($_POST['speed_limit']);

        $Report->build(round($difference_in_days / 7), round($difference_in_days2 / 7));

        foreach ($wdays as $key => $value) {
            $Report->comparisonDays->editCellTitle($key, $value);
        }

        for ($i = 1; $i <= 12; $i+=3) {
            $Report->comparisonDays->editLineTitle(1 + $i, $this->translate->_('Period 1'));
            $Report->comparisonDays->editLineTitle(2 + $i, $this->translate->_('Period 2'));
            $Report->comparisonDays->editLineTitle(3 + $i, $this->translate->_('Difference'));
            $Report->comparisonSpeed->editLineTitle(1 + $i, $this->translate->_('Period 1'));
            $Report->comparisonSpeed->editLineTitle(2 + $i, $this->translate->_('Period 2'));
            $Report->comparisonSpeed->editLineTitle(3 + $i, $this->translate->_('Difference'));
        }
        $Report->comparisonDays->editTopLineTitle(0, $this->translate->_('Day'));
        $Report->comparisonDays->editTopLineTitle(1, $this->translate->_('Average Vehicles Count'));
        $Report->comparisonDays->editTopLineTitle(2, $this->translate->_('Average Speed ('.$speed_label.')'));
        $Report->comparisonDays->editTopLineTitle(3, $this->translate->_('Average Number of Speed Violations'));
        $Report->comparisonDays->editTopLineTitle(4, $this->translate->_('% of Speed Violations'));
        $Report->comparisonSpeed->editTopLineTitle(0, $this->translate->_('Speed ('.$speed_label.')'));
        $Report->comparisonSpeed->editTopLineTitle(1, $this->translate->_('Total Vehicles Count'));
        $Report->comparisonSpeed->editTopLineTitle(2, $this->translate->_('% of Vehicles Count'));
        $Report->comparisonSpeed->editTopLineTitle(3, $this->translate->_('Total Speed Violations'));
        $Report->comparisonSpeed->editTopLineTitle(4, $this->translate->_('% of Speed Violations'));
        $Report->comparisonDays->editCellTitle(7, $this->translate->_('Total'));
        $Report->comparisonSpeed->editCellTitle($Report->comparisonSpeed->getMaxCountOfCell(), $this->translate->_('Total'));
        
        $this->view->report = $Report;

        $configsTbl = new Signs_Model_SignsConfigs();
        $configs = $configsTbl->getScheduleByLID((int) $_SESSION['reports']['location']);

        $recsheduleTbl = new Signs_Model_SignsRecschedule();
        $recshedule = isset($configs['r_param_set_id'])?$recsheduleTbl->getSpeedLimit($configs['r_param_set_id']):'';

        //   $minLimit = $recsheduleTbl->getMinLimit($configs['r_param_set_id']);

        $this->view->schedual_speed_limit = $recshedule;
        //$this->view->schedual_min_limit = $minLimit;

        $this->view->wdays = Ventrill_Definition::$wdays;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->difference_in_days = $difference_in_days;
        $this->view->difference_in_days2 = $difference_in_days2;
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];
        $this->view->start_date2 = $_SESSION['reports']['start_date_2'];
        $this->view->end_date2 = $_SESSION['reports']['end_date_2'];
        $this->view->speed_limit = $_SESSION['reports']['speed_limit'];

        //$this->view->data_type_name = Ventrill_Definition::$reports_data_types[$type]['name'];
    }

    /**
     * build Weekly Report - Statistics Summary Report
     *
     * @module     Signs
     * @category   AJAX reports.js >> buildCustomReport() 
     */
    public function wbuild1Action()
    {   
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();

        $configsTbl = new Signs_Model_SignsConfigs();
        $configs = $configsTbl->getScheduleByLID((int) $_SESSION['reports']['location']);

        $recsheduleTbl = new Signs_Model_SignsRecschedule();
        $recshedule = isset($configs['r_param_set_id'])?$recsheduleTbl->getSpeedLimit($configs['r_param_set_id']):'';
        
        $hours = Ventrill_Definition::$hours;
        $speed_limit = null;
        if (isset($_SESSION['reports']['speed_limit']) && !empty($_SESSION['reports']['speed_limit'])) {
            $this->view->speed_limit = $_SESSION['reports']['speed_limit'];
            $speed_limit = $_SESSION['reports']['speed_limit'];
        } else {
            $this->view->speed_limit = '';
            if (!empty($recshedule)) {
                if ($recshedule['max'] == $recshedule['min']) {
                    $speed_limit = $recshedule['max'];
                    $_SESSION['reports']['speed_limit'] = $speed_limit;
                }
            }
        }
        
        

        $Report = new Report_Summary($_SESSION['reports']['location'], $_SESSION['reports']['start_date'], $_SESSION['reports']['end_date'], $speed_limit);
        $Report->setSpeedLimit($speed_limit);
        foreach ($hours as $key => $value) {
            $Report->editCellTitle($key, $value);
        }
        $Report->editCellTitle(24, $this->translate->_('Summary'));

        $Report->editLineTitle(0, $this->translate->_('Hour'));
        $wdays = Ventrill_Definition::$wdays;
        for ($i = 1; $i < 7; $i++) {
            $Report->editLineTitle($i, $wdays[$i]);
        }

        $Report->editLineTitle(1, $this->translate->_('Total  <br/> Vehicles'));
        $Report->editLineTitle(2, $this->translate->_('Average <br/> Vehicles'));

        $Report->editLineTitle(3, $this->translate->_('Total  <br/> Violations'));
        $Report->editLineTitle(4, $this->translate->_('% <br/> Violations'));
        $Report->editLineTitle(5, $this->translate->_('Min. Speed <br/> ( '.$SpeedClass->getSpeedLabel().' )'));
        $Report->editLineTitle(6, $this->translate->_('Max. Speed <br/> ( '.$SpeedClass->getSpeedLabel().' )'));
        $Report->editLineTitle(7, $this->translate->_('Avg. Speed <br/> ( '.$SpeedClass->getSpeedLabel().' )'));
        $Report->editLineTitle(8, $this->translate->_('85% Speed <br/> ( '.$SpeedClass->getSpeedLabel().' )'));
        $Report->setSpeedReportTitles($this->translate->_('Speed'), $this->translate->_('Count'), $this->translate->_('Total'));

        $this->view->report = $Report;
        
        $this->view->schedual_speed_limit = $recshedule;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];
        $this->view->data_type_name = Ventrill_Definition::$reports_data_types[$_SESSION['reports']['data_type']]['name'];
/*****************************************************Statistics Summary Report******************************************************************************************/
        $base_dir = dirname(APPLICATION_PATH) .DIRECTORY_SEPARATOR. 'www'.DIRECTORY_SEPARATOR.'jpgraph'.DIRECTORY_SEPARATOR;
        include($base_dir.'jpgraph.php');
        include($base_dir.'jpgraph_bar.php');

        $Report = new Report_SpeedRange($_SESSION['reports']['location'], $_SESSION['reports']['start_date'], $_SESSION['reports']['end_date'], $this->translate->_('Total'));
        $Report->initByData();
        $Report->editLineTitle(0, $this->translate->_('Speed'));
        $Report->editLineTitle(1, $this->translate->_('Count'));
        $this->view->report2 = $Report;
        $this->view->result = 1;
        $speedrange = $_SESSION['chart']['speedrange'];
        $k = 0;
        $upperBound = !empty($keys = array_keys($speedrange))?max($keys):0;
        for ($i = 1; $i <= $upperBound; $i = $i + 5) {
            $labels[$k] = $i . '-' . ($i + 4);
            $speed = 0;
            for ($j = $i; $j <= $i + 4; $j++) {
                if (!isset($speedrange[$j])) {
                    $speedrange[$j] = 0;
                }
                $speed+= $speedrange[$j];
            }
            $data[$k] = $speed;
            $k++;
        }


        return 1;
    }

    /**
     * build Weekly Report 
     *
     * @module     Signs
     * @category  AJAX  buildWeeklyReport() >> reports.js 
     */
    public function wbuildAction()
    {
        $this->_helper->layout->disableLayout();
        $this->view->data_types = Ventrill_Definition::$reports_data_types[(int) $_POST['data_type']]['name'];
        $company_id = Zend_Auth::getInstance()->getIdentity()->company_id;
        if (isset($_POST['week'])) {
            foreach ($_POST['week'] as $key => $value) {
                if (!isset($first_key)) {
                    $start_date = $value;
                    $end_date = date("m/d/Y", strtotime($start_date . " +6 day"));
                } else {
                    if ($first_key == ($key - 1)) {
                        $end_date = date("m/d/Y", strtotime($value . " +6 day"));
                    } else {
                        $this->_helper->redirector('step3w');
                    }
                }
                $first_key = $key;
            }
        }
        $_SESSION['reports']['start_date'] = $start_date;
        $_SESSION['reports']['end_date'] = $end_date;
        if(!empty($_POST['speed_limit'])) {
            $_SESSION['reports']['speed_limit'] = $_POST['speed_limit'];
        } else {
            $configsTbl = new Signs_Model_SignsConfigs();
            $configs = $configsTbl->getScheduleByLID((int) $_SESSION['reports']['location']);

            $recsheduleTbl = new Signs_Model_SignsRecschedule();
            if (!empty($configs['r_param_set_id'])) {
                $recshedule = $recsheduleTbl->getSpeedLimit($configs['r_param_set_id'],'',false); //false = no speed unit label needed
                $_SESSION['reports']['speed_limit'] = $recshedule['max'];
            } else {
                // ToDo
                $recshedule = '';
            }
            
        }
        $_SESSION['reports']['data_type'] = (int) $_POST['data_type'];

        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();

        $difference_in_days = $this->differenceInDaysCalculate($_SESSION['reports']['start_date'], $_SESSION['reports']['end_date']);

        $wdays = Ventrill_Definition::$wdays;
        $hours = Ventrill_Definition::$hours;
        $Report = new Report_Weekly($_SESSION['reports']['location'], $_SESSION['reports']['start_date'], $_SESSION['reports']['end_date']);
        $offset = 0;
        foreach ($hours as $key => $value) {
            $Report->editCellTitle($key, $value);
        }
        $Report->editCellTitle(24, $this->translate->_('Summary'));
        switch ($_POST['data_type']) {
            case 1:
                $Report->buildAverageVehicleCount($difference_in_days / 7);
                break;
            case 2:
                $Report->buildTotalVehicleCount();
                break;
            case 3:
                $Report->buildAverageSpeed();
                //$offset = 1;
                $Report->editCellTitle(24, $this->translate->_('Average'));
                break;
            case 4:
                $Report->buildAverageNumberOfSpeedViolations($difference_in_days / 7);

                break;
            case 5:
                //if (isset($_POST['speed_limit'])) {
                //    $Report->buildTotalNumberOfSpeedViolations(1, 5, $_POST['speed_limit']);
                //} else {
                    $Report->buildTotalNumberOfSpeedViolations();
                //}
                break;
            case 6:
                if (isset($_POST['speed_limit'])) {
                    $Report->buildPercentageOfSpeedViolations($_POST['speed_limit']);
                } else {
                    $Report->buildPercentageOfSpeedViolations();
                }
                break;
        }

        $Report->editLineTitle(0, $this->translate->_('Hour'));
        for ($i = 1; $i < 7; $i++) {
            $Report->editLineTitle($i, $wdays[$i]);
        }
        $Report->editLineTitle(7, $wdays[0]);
        $Report->editLineTitle(self::WEEKDAY_AVERAGE, $this->translate->_('Weekday <br/> Average'));
        $Report->editLineTitle(self::WEEKEND_AVERAGE, $this->translate->_('Weekend <br/> Average'));
        if ($offset != 1) {
            $Report->editLineTitle(10, $this->translate->_('Week <br/> Average'));
        }
        $Report->editLineTitle(11 - $offset, $this->translate->_('Speed <br/> Average'));
        $Report->editLineTitle(12 - $offset, $this->translate->_('85% Speed'));
        $Report->saveChartData();
        $this->view->report = $Report;

        $configsTbl = new Signs_Model_SignsConfigs();
        $configs = $configsTbl->getScheduleByLID((int) $_SESSION['reports']['location']);
        
        $recsheduleTbl = new Signs_Model_SignsRecschedule();
        //$recshedule = $recsheduleTbl->getSpeedLimit($configs['r_param_set_id']);
         if (!empty($configs['r_param_set_id'])) {
            $recshedule = $recsheduleTbl->getSpeedLimit($configs['r_param_set_id']);
        } else {
            // ToDo
	        $recshedule = null;
        }

        $this->view->schedual_speed_limit = $recshedule;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];

        if (isset($_SESSION['reports']['speed_limit'])) {
            $this->view->speed_limit = $_SESSION['reports']['speed_limit'];
        }
    }

    /**
     * build Weekly Report - Hourly Values chart
     *
     * @module     Signs
     * @category  AJAX << wbuild.phtml 
     */
    public function wbuild3Action()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];
        if (isset($_SESSION['reports']['speed_limit'])) {
            $this->view->speed_limit = $_SESSION['reports']['speed_limit'];
        }
        $this->view->data_type_name = Ventrill_Definition::$reports_data_types[$_SESSION['reports']['data_type']]['name'];
        $result = $this->wbuild3graphAction();
        $this->view->status_result = $result;
    }

    /**
     * build Weekly Report - Daily Values chart 
     *
     * @module     Signs
     * @category  AJAX << wbuild.phtml 
     */
    public function wbuild4Action()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];
        if (isset($_SESSION['reports']['speed_limit'])) {
            $this->view->speed_limit = $_SESSION['reports']['speed_limit'];
        };
        $this->view->data_type_name = Ventrill_Definition::$reports_data_types[$_SESSION['reports']['data_type']]['name'];
        $result = $this->wbuild4graph();
        $this->view->status_result = $result;
    }

    /**
     * build Power Supply Values
     *
     * @module     Signs
     * @category  AJAX  buildBattVoltReport() >> reports.js 
     */
    public function bvbuild3Action()
    {
        $this->_helper->layout->disableLayout();
        $_SESSION['reports']['start_date'] = $_POST['start_date'];
        $_SESSION['reports']['end_date'] = $_POST['end_date'];
        $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->data_type_name = "Power Supply";
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];
        $result = $this->bvbuild3graph();
        $this->view->status_result = $result;
    }

    /**
     * Count by Speed Range Report 
     *
     * @module     Signs
     * @category  AJAX buildSpeedBinsReport() >> reports.js 
     */
    public function sbbuild3Action()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $_SESSION['reports']['start_date'] = $_POST['start_date'];
        $_SESSION['reports']['end_date'] = $_POST['end_date'];
        $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->data_type_name = $this->translate->_("Count by Speed Range Report");
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];
        $result = $this->sbbuild3graph();
        $this->view->status_result = $result;
    }

    /**
     * Comparison Chart (Hourly Values )
     *
     * @module     Signs
     * @category  AJAX << cbuild.phtml 
     */
    public function cbuild3Action()
    {
        $this->_helper->layout->disableLayout();
        $type = $this->getRequest()->getParam('type');
        $SpeedClass = new Ventrill_Speed(); 
        $this->view->speed_label = $SpeedClass->getSpeedLabel(); 
        if ($type > 0) {
            $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->id;
            $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
            $this->view->start_date = $_SESSION['reports']['start_date'];
            $this->view->end_date = $_SESSION['reports']['end_date'];
            $this->view->start_date2 = $_SESSION['reports']['start_date_2'];
            $this->view->end_date2 = $_SESSION['reports']['end_date_2'];
            if (isset($_SESSION['reports']['speed_limit'])) {
                $this->view->speed_limit = $_SESSION['reports']['speed_limit'];
            }
            $this->view->data_type_name = Ventrill_Definition::$reports_data_types[$type]['name'];
            $result = $this->cbuild3graphAction($type);
            $this->view->status_result = $result;
        } else {
            $this->view->empty = true;
        }
    }

    /**
     * Comparison Chart (Daily Values )
     *
     * @module     Signs
     * @category  AJAX << cbuild.phtml 
     */
    public function cbuild4Action()
    {
        $this->_helper->layout->disableLayout();
        $type = $this->getRequest()->getParam('type');
        $SpeedClass = new Ventrill_Speed(); 
        $this->view->speed_label = $SpeedClass->getSpeedLabel(); 
        if ($type > 0) {
            $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->id;
            $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
            $this->view->start_date = $_SESSION['reports']['start_date'];
            $this->view->end_date = $_SESSION['reports']['end_date'];
            $this->view->start_date2 = $_SESSION['reports']['start_date_2'];
            $this->view->end_date2 = $_SESSION['reports']['end_date_2'];
            if (isset($_SESSION['reports']['speed_limit'])) {
                $this->view->speed_limit = $_SESSION['reports']['speed_limit'];
            }
            $this->view->data_type_name = Ventrill_Definition::$reports_data_types[$type]['name'];
            $result = $this->cbuild4graphAction($type);
            $this->view->status_result = $result;
        } else {
            $this->view->empty = true;
        }
    }

    /**
     *  Custom Report 
     *
     * @module     Signs
     * @category  AJAX << cubuild.phtml  
     */
    public function cubuild3Action()
    {
        $this->_helper->layout->disableLayout();
        $SpeedClass = new Ventrill_Speed();
        $this->view->speed_label = $SpeedClass->getSpeedLabel();
        $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date =  date( "Y-m-d", strtotime($_SESSION['reports']['start_date'])) ;
        $this->view->end_date =  date( "Y-m-d", strtotime($_SESSION['reports']['end_date']));
        if (isset($_SESSION['reports']['speed_limit'])) {
            $this->view->speed_limit = $_SESSION['reports']['speed_limit'];
        }
        $this->view->data_type_name = Ventrill_Definition::$reports_groups_records[$_SESSION['reports']['group_records']]['name'];
        $this->view->data_type = $_SESSION['reports']['data_type'];
        $this->view->status_result = 1;
    }

    /**
     *  Synchronization Event Log Report 
     *
     * @module     Signs
     * @category  AJAX reports.js >> buildCommunicationReport()  
     */
    public function clbuild3Action()
    {
        $this->_helper->layout->disableLayout();
        $_SESSION['reports']['start_date'] = $_POST['start_date'];
        $_SESSION['reports']['end_date'] = $_POST['end_date'];
        $SignsSyncReqModel = new Signs_Model_SignsSyncReq();
        $commlog = $SignsSyncReqModel->getAllRecordsWithType($_SESSION['reports']['location'], $_SESSION['reports']['start_date'], $_SESSION['reports']['end_date']);
        $commlogArray = $commlog->toArray();

        $SignsServicereqModel = new Signs_Model_SignsServicereq();
        $commlogService = $SignsServicereqModel->getAllRecordsWithType($_SESSION['reports']['location'], $_SESSION['reports']['start_date'], $_SESSION['reports']['end_date']);
        $commlogServiceArray = $commlogService->toArray();

        $this->view->user_id = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->data_type_name = $this->translate->_("Synchronization Event Log Report");
        $this->view->location = wd_API::getLocationInfo($_SESSION['reports']['location']);
        $this->view->start_date = $_SESSION['reports']['start_date'];
        $this->view->end_date = $_SESSION['reports']['end_date'];
        $this->view->commlog = $commlogArray;
        $this->view->commlogService = $commlogServiceArray;

        if (empty($commlogArray)) {
            $this->view->status_result = $this->translate->_('There is no collected data available based on the parameters/dates you have selected at this time.');
        } else {
            $this->view->status_result = 1;
        }

        if (empty($commlogServiceArray)) {
            $this->view->service_status_result = $this->translate->_('There is no collected data available based on the parameters/dates you have selected at this time.');
        } else {
            $this->view->service_status_result = 1;
        }
    }

    /* --------------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */
    /* ------------------------------Graph methods-------------------------- */
    /* --------------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     *  draw Comparison Chart - Hourly Values chart
     *
     * @module     Signs
     * @category  AJAX << cbuild3Action 
     */
    public function cbuild3graphAction($type)
    {
        $base_dir = dirname(APPLICATION_PATH) .DIRECTORY_SEPARATOR. 'www'.DIRECTORY_SEPARATOR.'jpgraph'.DIRECTORY_SEPARATOR;
        include($base_dir.'jpgraph.php');
        include($base_dir.'jpgraph_line.php');
        
        switch ($type) {
            case 1:
            case 2:
                for ($i = 0; $i <= 23; $i++) {
                    if ((!isset($_SESSION['HChart2'][$i]['count'])) || ($_SESSION['HChart2'][$i]['count'] == $this->translate->_("n/a"))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['HChart2'][$i]['count'];
                    }
                    if ((!isset($_SESSION['HChart1'][$i]['count'])) || ($_SESSION['HChart1'][$i]['count'] == $this->translate->_("n/a"))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['HChart1'][$i]['count'];
                    }
                }
                break;
            case 3:
                for ($i = 0; $i <= 23; $i++) {
                    if ((!isset($_SESSION['HChart2'][$i]['speed'])) || ($_SESSION['HChart2'][$i]['speed'] == $this->translate->_("n/a"))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['HChart2'][$i]['speed'];
                    }
                    if ((!isset($_SESSION['HChart1'][$i]['speed'])) || ($_SESSION['HChart1'][$i]['speed'] == $this->translate->_("n/a"))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['HChart1'][$i]['speed'];
                    }
                }
                break;
            case 4:
                for ($i = 0; $i <= 23; $i++) {
                    if ((!isset($_SESSION['HChart2'][$i]['avgviolation'])) || ($_SESSION['HChart2'][$i]['avgviolation'] == $this->translate->_("n/a"))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['HChart2'][$i]['avgviolation'];
                    }
                    if ((!isset($_SESSION['HChart1'][$i]['avgviolation'])) || ($_SESSION['HChart1'][$i]['avgviolation'] == $this->translate->_("n/a"))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['HChart1'][$i]['avgviolation'];
                    }
                }
                break;
            case 5:
                for ($i = 0; $i <= 23; $i++) {
                    if ((!isset($_SESSION['HChart2'][$i]['violation'])) || ($_SESSION['HChart2'][$i]['violation'] == $this->translate->_("n/a"))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['HChart2'][$i]['violation'];
                    }
                    if ((!isset($_SESSION['HChart1'][$i]['violation'])) || ($_SESSION['HChart1'][$i]['violation'] == $this->translate->_("n/a"))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['HChart1'][$i]['violation'];
                    }
                }
                break;
            case 6:
                for ($i = 0; $i <= 23; $i++) {
                    if ((!isset($_SESSION['HChart2'][$i]['percentviolation'])) || ($_SESSION['HChart2'][$i]['percentviolation'] == $this->translate->_("n/a"))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['HChart2'][$i]['percentviolation'];
                    }
                    if ((!isset($_SESSION['HChart1'][$i]['percentviolation'])) || ($_SESSION['HChart1'][$i]['percentviolation'] == $this->translate->_("n/a"))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['HChart1'][$i]['percentviolation'];
                    }
                }

                break;
            default :
                return $this->translate->_('There is no collected data available based on the parameters/dates you have selected at this time.');
        }

        $graph = new Graph(1000, 600);
        $graph->SetScale("textlin");
        $theme_class = new UniversalTheme;
        $graph->SetTheme($theme_class);
        $graph->img->SetAntiAliasing(false);
        $graph->SetBox(false);
        $graph->img->SetAntiAliasing();
        $graph->yaxis->HideZeroLabel();
        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false, false);
        $graph->xgrid->Show();
        $graph->xgrid->SetLineStyle("solid");

        for ($i = 0; $i <= 24; $i++) {
            $xaxis[$i] = $i;
        }
        $graph->xaxis->SetTickLabels($xaxis);
        $graph->xgrid->SetColor('#E3E3E3');

        $p1 = new LinePlot($datay);
        $p2 = new LinePlot($datay2);
        $graph->Add($p1);
        $p1->SetColor("#44a2ff ");
        $p1->mark->SetColor("#44a2ff ");
        $p1->mark->SetFillColor("#44a2ff ");
        $p1->mark->SetType(MARK_UTRIANGLE, '', 1.0);
        $p1->SetLegend($this->translate->_("Period 1"));
        $graph->Add($p2);
        $p2->SetColor("#ff9090");
        $p2->mark->SetFillColor("#ff9090");
        $p2->mark->SetColor("#ff9090");
        $p2->mark->SetType(MARK_UTRIANGLE, '', 1.0);
        $p2->SetLegend($this->translate->_("Period 2"));
        $graph->legend->SetFrameWeight(1);
        $graph->SetMargin(50, 10, 10, 10);
        @unlink("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_3.png");
        $graph->Stroke("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_3.png");
        return 1;
    }

    /**
     *  draw Weekly Report - Hourly Values chart
     *
     * @module     Signs
     * @category  AJAX << wbuild3Action 
     */
    public function wbuild3graphAction()
    {
        $base_dir = dirname(APPLICATION_PATH) .DIRECTORY_SEPARATOR. 'www'.DIRECTORY_SEPARATOR.'jpgraph'.DIRECTORY_SEPARATOR;
        include( $base_dir.'jpgraph.php');
        include( $base_dir.'jpgraph_line.php');

        if (empty($_SESSION['HChart'])) {
            return $this->translate->_('There is no collected data available based on the parameters/dates you have selected at this time.');
        }

        for ($i = 0; $i <= 23; $i++) {
            if (!is_numeric($_SESSION['HChart'][$i]) != "n/a") {
                $datay[$i] = $_SESSION['HChart'][$i];
            } else {
                $datay[$i] = 0;
            }
        }

        $graph = new Graph(1000, 600);
        $graph->SetScale("textlin");

        $theme_class = new UniversalTheme;

        $graph->SetTheme($theme_class);
        $graph->img->SetAntiAliasing(false);
        $graph->SetBox(false);

        $graph->img->SetAntiAliasing();
        $graph->yaxis->HideZeroLabel();
        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false, false);
        $graph->xgrid->Show();
        $graph->xgrid->SetLineStyle("solid");

        for ($i = 0; $i <= 24; $i++) {
            $xaxis[$i] = $i;
        }
        $graph->xaxis->SetTickLabels($xaxis);
        $graph->xgrid->SetColor('#E3E3E3');

        $p1 = new LinePlot($datay);
        $graph->Add($p1);
        $p1->SetColor("#44a2ff ");
        $p1->mark->SetColor("#44a2ff ");
        $p1->mark->SetFillColor("#44a2ff ");
        $p1->mark->SetType(MARK_UTRIANGLE, '', 1.0);

        $graph->legend->SetFrameWeight(1);

        //$graph->Stroke();
        $graph->SetMargin(50, 10, 10, 30);
        @unlink("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_3.png");
        $graph->Stroke("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_3.png");
        return 1;
    }

    /**
     *  draw  Power Supply Values chart
     *
     * @module     Signs
     * @category  AJAX << bvbuild3Action 
     */
    public function bvbuild3graph()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        try {


            $base_dir = dirname(APPLICATION_PATH) . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'jpgraph' . DIRECTORY_SEPARATOR;
            include($base_dir . 'jpgraph.php');
            include($base_dir . 'jpgraph_line.php');

            $stat_records = new Signs_Model_SignsStatRecords();
            $records = $stat_records->getBattaryRecords($_SESSION['reports']['location'], $_SESSION['reports']['start_date'], $_SESSION['reports']['end_date']);
            if (empty($records)) {
                return $this->translate->_('There is no collected data available based on the parameters/dates you have selected at this time.');
            }
            $i = 0;
            $ic = 0;
            $count_ar = round(count($records) / 10);
            foreach ($records as $value) {
                $data[$i] = $value['batt_voltage'];
                if ($ic == $count_ar) {
                    $labels[$i] = $value['time'];
                    $ic = 0;
                } else {
                    $labels[$i] = "";
                }
                $ic++;
                $i++;
            }

            if (!empty($data)) {
                $labels[0] = $records['0']['time'];
                $graph = new Graph(1000, 600);
                $graph->SetScale("textlin");

                $theme_class = new UniversalTheme;
                $graph->SetTheme($theme_class);
                $graph->img->SetAntiAliasing(false);
                $graph->SetBox(false);
                $graph->img->SetAntiAliasing();
                $graph->yaxis->HideZeroLabel();
                $graph->yaxis->HideLine(true);
                $graph->yaxis->HideTicks(true, true);
                $graph->yaxis->SetTitle($this->translate->_('Voltage'), 'middle');
                $graph->xaxis->SetTitle($this->translate->_('Time/Date'), 'middle');
                $graph->xgrid->SetLineStyle("solid");
                $graph->xaxis->SetTickLabels($labels);
                $graph->xgrid->SetColor('#E3E3E3');

                $p1 = new LinePlot($data);
                $graph->Add($p1);
                $p1->SetColor("#44a2ff");

                $graph->yaxis->SetTitleMargin(40);
                $graph->SetMargin(60, 40, 10, 60);
                @unlink("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_3.png");
                $graph->Stroke("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_3.png");
            }
        } finally {
            ini_restore('memory_limit');
            ini_restore('max_execution_time');
        }
        return 1;
    }

    /**
     *  draw  Count by Speed Range Report chart
     *
     * @module     Signs
     * @category  AJAX << sbbuild3Action 
     */
    public function sbbuild3graph()
    {
        $base_dir = dirname(APPLICATION_PATH) .DIRECTORY_SEPARATOR. 'www'.DIRECTORY_SEPARATOR.'jpgraph'.DIRECTORY_SEPARATOR;
        include($base_dir.'jpgraph.php');
        include($base_dir.'jpgraph_bar.php');

        $Report = new Report_SpeedRange($_SESSION['reports']['location'], $_SESSION['reports']['start_date'], $_SESSION['reports']['end_date'], $this->translate->_('Total'));
        $Report->initByData();
        $Report->editLineTitle(0, $this->translate->_('Speed'));
        $Report->editLineTitle(1, $this->translate->_('Count'));
        $this->view->report = $Report;
        $this->view->result = 1;
        $speedrange = $_SESSION['chart']['speedrange'];
        $k = 0;
        for ($i = 1; $i <= max(array_keys($speedrange)); $i = $i + 5) {
            $labels[$k] = $i . '-' . ($i + 4);
            $speed = 0;
            for ($j = $i; $j <= $i + 4; $j++) {
                if (!isset($speedrange[$j])) {
                    $speedrange[$j] = 0;
                }
                $speed+= $speedrange[$j];
            }
            $data[$k] = $speed;
            $k++;
        }

        if (!empty($data)) {
            $graph = new Graph(800, 600, 'auto');
            $graph->SetScale("textlin");
            $graph->SetBox(false);
            $graph->ygrid->SetFill(false);
            $theme_class = new UniversalTheme;
            $graph->SetTheme($theme_class);
            $graph->img->SetAntiAliasing(false);
            $graph->img->SetAntiAliasing();
            $graph->yaxis->HideZeroLabel();
            $graph->yaxis->HideLine(true);
            $graph->yaxis->HideTicks(true, true);
            $graph->xgrid->SetLineStyle("solid");
            $graph->xaxis->SetTickLabels($labels);
            $graph->xaxis->SetLabelAngle(270);
            $graph->xgrid->SetColor('#E3E3E3');
            $graph->xaxis->SetLabelAlign('center', 'left');

            $p1 = new BarPlot($data);
            $graph->Add($p1);
            $p1->SetColor("#44a2ff");
            $p1->SetFillColor("#44a2ff");

            $graph->SetMargin(50, 10, 10, 80);
            @unlink("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_3.png");
            $graph->Stroke("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_3.png");
        }
        return 1;
    }

    /**
     *  draw  Weekly Report - Hourly Values chart
     *
     * @module     Signs
     * @category  AJAX << wbuild4Action 
     */
    public function wbuild4graph()
    {
        $base_dir = dirname(APPLICATION_PATH) .DIRECTORY_SEPARATOR. 'www'.DIRECTORY_SEPARATOR.'jpgraph'.DIRECTORY_SEPARATOR;
        include($base_dir.'jpgraph.php');
        include($base_dir.'jpgraph_bar.php');
        
        if (empty($_SESSION['DChart'])) {
            return $this->translate->_('There is no collected data available based on the parameters/dates you have selected at this time.');
        }

        for ($i = 0; $i <= 6; $i++) {
            if ($_SESSION['DChart'][$i] != "n/a") {
                $datay[$i] = $_SESSION['DChart'][$i];
            } else {
                $datay[$i] = 0;
            }
        }

        $graph = new Graph(1000, 600, 'auto');
        $graph->SetScale("textlin");
        $graph->SetBox(false);
        $graph->ygrid->SetFill(false);
        $graph->xaxis->SetTickLabels(array($this->translate->_('Monday'), $this->translate->_('Tuesday'), $this->translate->_('Wednesday'), $this->translate->_('Thursday'), $this->translate->_('Friday'), $this->translate->_('Saturday'), $this->translate->_('Sunday')));
        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false, false);
        $b1plot = new BarPlot($datay);
        $graph->Add($b1plot);

        $b1plot->SetColor("white");
        $b1plot->SetFillColor("#44a2ff ");
        $b1plot->SetWidth(45);
        //$graph->Stroke();
        $graph->SetMargin(50, 10, 10, 30);
        @unlink("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_4.png");
        $graph->Stroke("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_4.png");
        return 1;
    }

    /**
     *  draw  Comparison Chart - Daily Values chart
     *
     * @module     Signs
     * @category  AJAX << cbuild3Action 
     */
    public function cbuild4graphAction($type)
    {
        $base_dir = dirname(APPLICATION_PATH) .DIRECTORY_SEPARATOR. 'www'.DIRECTORY_SEPARATOR.'jpgraph'.DIRECTORY_SEPARATOR;
        include($base_dir.'jpgraph.php');
        include($base_dir.'jpgraph_bar.php');
        
        switch ($type) {
            case 1:
            case 2:
                for ($i = 0; $i < 7; $i++) {
                    if ((!isset($_SESSION['DChart2'][$i]['count'])) || (!is_numeric($_SESSION['DChart2'][$i]['count']))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['DChart2'][$i]['count'];
                    }
                    if ((!isset($_SESSION['DChart1'][$i]['count'])) || (!is_numeric($_SESSION['DChart1'][$i]['count']))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['DChart1'][$i]['count'];
                    }
                }
                $datay2[7] = $datay2[0];
                $datay[7] = $datay[0];
                unset($datay2[0]);
                $datay2 = array_values($datay2);
                unset($datay[0]);
                $datay = array_values($datay);
                break;
            case 3:
                for ($i = 0; $i < 7; $i++) {
                    if ((!isset($_SESSION['DChart2'][$i]['speed'])) || (!is_numeric($_SESSION['DChart2'][$i]['speed']))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['DChart2'][$i]['speed'];
                    }
                    if ((!isset($_SESSION['DChart1'][$i]['speed'])) || (!is_numeric($_SESSION['DChart1'][$i]['speed']))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['DChart1'][$i]['speed'];
                    }
                }
                $datay2[7] = $datay2[0];
                $datay[7] = $datay[0];
                unset($datay2[0]);
                $datay2 = array_values($datay2);
                unset($datay[0]);
                $datay = array_values($datay);
                break;
            case 4:
                for ($i = 0; $i < 7; $i++) {
                    if ((!isset($_SESSION['DChart2'][$i]['avgviolation'])) || (!is_numeric($_SESSION['DChart2'][$i]['avgviolation']))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['DChart2'][$i]['avgviolation'];
                    }
                    if ((!isset($_SESSION['DChart1'][$i]['avgviolation'])) || (!is_numeric($_SESSION['DChart1'][$i]['avgviolation']))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['DChart1'][$i]['avgviolation'];
                    }
                }
                $datay2[7] = $datay2[0];
                $datay[7] = $datay[0];
                unset($datay2[0]);
                $datay2 = array_values($datay2);
                unset($datay[0]);
                $datay = array_values($datay);
                break;
            case 5:
                for ($i = 0; $i < 7; $i++) {
                    if ((!isset($_SESSION['DChart2'][$i]['totalvoalation'])) || (!is_numeric($_SESSION['DChart2'][$i]['totalvoalation']))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['DChart2'][$i]['totalvoalation'];
                    }
                    if ((!isset($_SESSION['DChart1'][$i]['totalvoalation'])) || (!is_numeric($_SESSION['DChart1'][$i]['totalvoalation']))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['DChart1'][$i]['totalvoalation'];
                    }
                }
                $datay2[7] = $datay2[0];
                $datay[7] = $datay[0];
                unset($datay2[0]);
                $datay2 = array_values($datay2);
                unset($datay[0]);
                $datay = array_values($datay);
                break;
            case 6:
                for ($i = 0; $i < 7; $i++) {
                    if ((!isset($_SESSION['DChart2'][$i]['procviolation'])) || (!is_numeric($_SESSION['DChart2'][$i]['procviolation']))) {
                        $datay2[$i] = 0;
                    } else {
                        $datay2[$i] = $_SESSION['DChart2'][$i]['procviolation'];
                    }
                    if ((!isset($_SESSION['DChart1'][$i]['procviolation'])) || (!is_numeric($_SESSION['DChart1'][$i]['procviolation']))) {
                        $datay[$i] = 0;
                    } else {
                        $datay[$i] = $_SESSION['DChart1'][$i]['procviolation'];
                    }
                }
                $datay2[7] = $datay2[0];
                $datay[7] = $datay[0];
                unset($datay2[0]);
                $datay2 = array_values($datay2);
                unset($datay[0]);
                $datay = array_values($datay);
                break;
            default :
                return $this->translate->_('There is no collected data available based on the parameters/dates you have selected at this time.');
        }

        $graph = new Graph(1000, 600, 'auto');
        $theme_class = new UniversalTheme;
        $graph->SetScale("textlin");
        $graph->SetTheme($theme_class);
        $graph->SetBox(false);
        $graph->ygrid->SetFill(false);
        $graph->xaxis->SetTickLabels(array($this->translate->_('Monday'), $this->translate->_('Tuesday'), $this->translate->_('Wednesday'), $this->translate->_('Thursday'), $this->translate->_('Friday'), $this->translate->_('Saturday'), $this->translate->_('Sunday')));
        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false, false);
        $b1plot = new BarPlot($datay);
        $b2plot = new BarPlot($datay2);
        // Create the grouped bar plot
        $gbplot = new GroupBarPlot(array($b1plot, $b2plot));
        $graph->Add($gbplot);
        $b1plot->SetColor("#44a2ff ");
        $b1plot->SetWidth(39);
        $b1plot->SetLegend($this->translate->_("Period 1"));
        $b1plot->SetFillColor("#44a2ff ");
        $b2plot->SetColor("#ff9090");
        $b2plot->SetWidth(39);
        $b2plot->SetLegend($this->translate->_("Period 2"));
        $b2plot->SetFillColor("#ff9090");
        $graph->SetMargin(50, 10, 10, 10);
        @unlink("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_4.png");
        $graph->Stroke("temp_files/user_" . Zend_Auth::getInstance()->getIdentity()->id . "_4.png");
        return 1;
    }

    /* --------------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */
    /* ------------------------------Export methods------------------------- */
    /* --------------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     *  export comparisson weekly report data to csv file
     *
     * @module     Signs
     * @category  AJAX 
     */
    public function wbuildcsvAction()
    {
        include 'simple_html_dom.php';
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $html = str_replace('th', 'td', $_SESSION['wbuild2']);
        $html = str_replace('<br/>', '/', $html);
        $html = str_replace('Average', 'Avg', $html);

        $html = str_get_html($html);
        $report_name = $this->getRequest()->getParam('name','Report');

        $tempfile = '_tempCSV' . uniqid(rand(1, 100000), true);
        $path = dirname(APPLICATION_PATH) .DIRECTORY_SEPARATOR. 'www'.DIRECTORY_SEPARATOR.'temp_files'.DIRECTORY_SEPARATOR;

        $fp = fopen($path . $tempfile, 'w+');
        foreach ($html->find('tr') as $element) {
            $td = array();
            foreach ($element->find('td') as $row) {
                $td[] = $row->plaintext;
            }
            fputcsv($fp, $td);
        }

        rewind($fp);

        $this->_response->setHeader('Content-Type', 'application/ms-excel')
            ->setHeader('Content-Description', 'File Transfer')
            ->setHeader('Content-Disposition', 'attachment; filename="'.$report_name.'('.$this->getFileExportTime().').csv"');

        fpassthru($fp);
        fclose($fp);
        unlink($path . $tempfile);
    }

    /**
     *  export custom weekly report data to csv file
     *
     * @module     Signs
     * @category  AJAX
     */
    public function buildcsvAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $data = stripcslashes($_REQUEST['csv_text']);
        $new_data = str_replace("&nbsp;", "", $data);

        $report_name = $this->getRequest()->getParam('name','Report');

        $this->_response->setHeader('Content-Type', 'application/ms-excel')
            ->setHeader('Content-Description', 'File Transfer')
            ->setHeader('Content-Disposition', 'attachment; filename="'.$report_name.' ('.$this->getFileExportTime().').csv"')
            ->setBody($new_data);
    }

    /**
     *  get the company time of exported file(csv,pdf)
     *
     * @module     Signs
     * @category  AJAX 
     */
    public function getFileExportTime()
    {   
        $time = wd_API::getCurrentTimeOfCompany(Zend_Auth::getInstance()->getIdentity()->company_id); 
        $date = date("Y-m-d H:i:s", strtotime($time));
        return $date;
    }

    /**
     *  print weekly  report data
     *
     * @module     Signs
     * @category  AJAX 
     */
    public function wbuildprintAction()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $header = '<div>';
            if (!empty($_GET['params']) && is_array($params)) {
                $params = json_decode($_GET['params']);
                $header .= '<script>
                               $(document).ready(function() { 
                                    var params = '.json_encode($params).';
                                    params.forEach(function(entry) {
                                        if (entry[1]==false) {
                                            $("div.report_div table .type_"+entry[0]).hide();
                                        }
                                    });
                                    history.pushState("", "", "/signs/reports/wbuildprint");
                                    window.print();
                                    //window.onfocus=function(){ window.close();}
                               })
                        </script>';
            } else {
                $header .= '<script>
                                   $(document).ready(function() { 
                                        window.print();
                                   })
                            </script>';
            }
            $this->view->content =  $header . $_SESSION['wbuild' . $id];
            $this->_helper->layout->setLayout('reports');
        }
        //exit;
    }
    
     public function printcustomAction() {
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $datatypes =  $_POST['datatypes'];
            $str = "";
            foreach ($datatypes as $key => $type) {
                if ($type) {
                    $str .= "document.getElementsByClassName('type_" . $key . "')[0].style.display = 'none';";
                }
            }
            
            $header = '
                    <script>
                    $(document).ready(function() {
                      window.print();
                     });
       

                    </script>';
            echo $header . $_SESSION['wbuild' . $id];
        }
        exit;
    }

    public function wbuildpdfcustomAction()
    {
        $id = $this->getRequest()->getParam('id');
        $this->view->graph = $_SESSION['wbuild' . $id];
    }

    /**
     * export weekly  report data to pdf file
     *
     * @module     Signs
     * @category  AJAX 
     */
    public function wbuildpdfAction()
    {

        /** memory_limit
         *  max_execution_time
         */

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        try {

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $GLOBALS['tmp_buf'] = str_repeat('x', 1024 * 200);
            // Handle the output stream and set a handler function.
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $header = '<link href="/css/modal.css" media="print, screen" rel="stylesheet" type="text/css" />
                    <link href="/css/main.css" media="print, screen" rel="stylesheet" type="text/css" />
                    <link href="/wd/assets/css/OLD_css/modal.css" media="print, screen" rel="stylesheet" type="text/css" />
                    <link href="/wd/assets/css/OLD_css/metro-bootstrap.css" media="print, screen" rel="stylesheet" type="text/css" />
                    <link href="/signs/css/reports.css" media="print, screen" rel="stylesheet" type="text/css" />';
                require_once 'mpdf60/mpdf.php';
                include 'simple_html_dom.php';
                $mpdf = new mPDF('', '', 9, 'Verdana', 15, 15, 3, 3, 1, 1, 'L');
                $mpdf->AddPage('L');
                $html = $_SESSION['wbuild' . $id];
				$locinfo = wd_API::getLocationInfo($_SESSION['reports']['location']);
/** 
 * UTF-8 code page
 */
//                $html = '<head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /></head>' . $html;

                $dom = new simple_html_dom();
                $doc = new DOMDocument();
                @$doc->loadHTML($html);

                $selector = new DOMXPath($doc);
                $div = $selector->query('//div[1]')->item(0);
                $line = $div->nodeValue;


                if ($id == 1) {
/**
 * translate separator
 */
                    list($a, $b) = explode($this->view->translate('Location'), $line);
                    $line = $a;
                }
                if ($id == 3 || $id == 4) {
                    $dom->load($html);
                    $line = $dom->find('h2', 0)->plaintext;
                    /* list($a, $b) = explode('Values', $line);
                     $line = $a; */
                }

                if (isset($_GET['params'])) {
                    //echo($html);
                    //exit;
                    try {
                        $doc->loadHTML($html);
                    } catch (Exception $e) {
                        echo($html);
                        exit;
                    }
                    $xpath = new DOMXpath($doc);
                    $columns = json_decode($_GET['params']);
                    $str = "";
                    if ($columns) {
                        foreach ($columns as $column) {
                            $key = $column[0];
                            $type = $column[1];
                            if ($type === false) {

                                $query = 'td[@type_' . $key . ']';
                                $query = '//td[contains(concat(" ", normalize-space(@class), " ")," type_' . $key . ' ")]';
                                $entries = $xpath->query($query);
                                foreach ($entries as $node) {
                                    //echo $node->getNodePath()."<br />";
                                    $node->parentNode->removeChild($node);

                                }
                                $query = '//th[contains(concat(" ", normalize-space(@class), " ")," type_' . $key . ' ")]';
                                $entries = $xpath->query($query);
                                foreach ($entries as $node) {
                                    //echo $node->getNodePath()."\n\n";

                                    $node->parentNode->removeChild($node);

                                }
                            } else {

                            }
                        }
                    }
                    $mpdf->WriteHTML($header . $doc->saveHTML(), 2);
                } else {
                    $mpdf->WriteHTML($header . $html, 2);
                }
                $saveName = preg_replace('/[\t\r\n\:\ \-]+/', '', $line) .'_'.$locinfo['name'].' '. $this->getFileExportTime() . '.pdf';
                $mpdf->Output($saveName, 'D');
            }
        } finally {
            ini_restore('memory_limit');
            ini_restore('max_execution_time');
        }
    }

    /* --------------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */
    /* ----------------------------Calculate methods------------------------ */
    /* --------------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * Calculate difference In Days between $start_date, $end_date
     * @param     string $start_date 
     * @param     string $end_date  
     * @return    int  $difference_in_days
     * @module     Signs
     * @category  private 
     */
    private function differenceInDaysCalculate($start_date, $end_date)
    {
        //Calculate Difference In Days
        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);
        $difference = ($end_date - $start_date);
        $difference_in_days = round($difference / 86400) + 1;
        return $difference_in_days;
    }

    /**
     * Get Location Data for tamplate
     *
     * @module     Signs
     * @category  AJAX 
     */
    public function getlocationdataAction()
    {
        $id = $this->getRequest()->getParam('id');
        $location = wd_API::getLocationInfo($id);
        $this->view->location = $location;
    }

    /**
     * Get  tamplate
     *
     * @module     Signs
     * @category  AJAX 
     */
    public function gettamplateAction()
    {
        $this->_helper->layout->disableLayout();
    }

    /**
     * save chart
     *
     * @module     Signs
     * @category  AJAX 
     */
    public function savechartAction()
    {
        include 'simple_html_dom.php';
        $img = $this->getRequest()->getParam('img');
        $tickLabels = '<div style="font-size:smaller">' . $this->getRequest()->getParam('tickLabels') . '</div>';
        $chartControl = '<div>' . $this->getRequest()->getParam('chartControl') . '</div>';
        $decocedData = base64_decode($img);
        $path = $_SERVER['DOCUMENT_ROOT'] . '/temp_files/';
        $name = rand(0, 200) . 'print_img.png';
        $fp = fopen($path . $name, 'w');
        fwrite($fp, $decocedData);
        fclose($fp);
        $html = str_get_html($_SESSION['wbuild3']);
        $chart = $html->find('div[id=chart-container]', 0);
        if ($chart) {
            //$chart->innertext = $chartControl . '<div id="placeholder" class="chart-placeholder" style="float: left; width: 850px; height: 500px; padding: 0px; position: relative;"><img height="261" width="850" src="/temp_files/' . $name . '"/>' . $tickLabels . '</div>';
          $chart->innertext = '<div id="placeholder" class="chart-placeholder" style="padding: 0px; position: relative;"><img class="chart_img" width="100%" src="/temp_files/' . $name . '"/>' . $tickLabels . '</div>' . $chartControl;
        }
        $_SESSION['wbuild3'] = $html->save();
        $this->view->result = 1;
    }

}
