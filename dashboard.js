//# sourceURL=signs/js/dashboard.js
var lastScheduleSelected;
var location_name;
var schedule_type;
 $(document).ready(function() {
     var location_id = 0;

    $("#comm-data").dialog({
        resizable: true,
        autoOpen: false,
        modal: true,
        width: 900,
        height: 415,
        buttons: {
            Save: function() {
                saveData();
            }, //end save button
            Close: function() {
                $("#comm-data").dialog('close');
            } //end cancel button

        }//end buttons

    }); //end dialog

    $("#advanced-settings").dialog({
        resizable: true,
        autoOpen: false,
        modal: true,
        width: 580,
        height: 450,
        buttons: {
            Save: function() {
                saveAdvancedSettings();
            },
            Close: function() {
                $("#advanced-settings").dialog('close');
            }

        }

    });

    $("#alerts").dialog({
        resizable: true,
        autoOpen: false,
        modal: true,
        width: 500,
        height: 450,
        buttons: {
            Save: function() {
                saveAlertsSettings();
            },
            Close: function() {
                $("#alerts").dialog('close');
            }

        }

    });

    $("#bind-location").dialog({
        resizable: true,
        autoOpen: false,
        modal: true,
        width: 500,
        height: 200,
        buttons: {
            Save: function() {
                bindRadarLocation();
            },
            Close: function() {
                $("#bind-location").dialog('close');
            }

        }

    });

    oTable = $('#dashboard-table').dataTable({
        "bPaginate": false,
        "bFilter": false,
        "bInfo": false,
        "columnDefs": [
                    {
                        "targets": [ 3,4,5,6,7,8 ],
                        "orderable": false,
                    },
                ],
        "order":[],
    });

});

function getData(set_type, id, location_id,loctype) {
    //location_id = $('div#comm-data-parameters input[name="location_id"]')[0].value;
    //location_name = $('div#comm-data-parameters input[name="location_name"]')[0] ? $('div#comm-data-parameters input[name="location_name"]')[0].value : '';
    location_name = $('div#comm-data-parameters input[name="location_name"]')[0].value
    if ( set_type == 1) {

        $("#comm-data").dialog({
            title: "Schedule ("+location_name+")"
        });

        $("#comm-data-content").load("/signs/dashboard/showschedule?id=" + id+'&lid='+location_id, function() {
            lastScheduleSelected = id;
            $("#comm-data").dialog('open');
             schedule_type = $('select#schedule_ids option:first').attr("name");
        });

    } else {
        lastScheduleSelected = null;
    }
    if (set_type == 2) {
        $("#comm-data").dialog({
            title: "Express mode ("+location_name+")"
        });

        $("#comm-data-content").load("/signs/dashboard/showexpressmode?id=" + id+'&lid='+location_id, function() {
            $("#comm-data").dialog("option", "height", "635");	     
             $('#sign_type').val(loctype);
	     $("#comm-data").dialog('open');
            first_sid = $('select[name="schedule_id"]:first').val();
             if (first_sid == null){
                $('div.navigation-bar-content a').eq(1).css('pointer-events', 'none');
             }
        });
    }
    if (set_type == 3) {
        $("#comm-data").dialog({
            title: "Beacon Schedule ("+location_name+")"
        });
        $("#comm-data-content").load("/signs/dashboard/showbschedule?id=" + id, function() {
            $("#comm-data").dialog('open');
        });
    }
    if (set_type == 4) {
        $("#comm-data").dialog({
            title: "Calendar ("+location_name+")"
        });
        $("#comm-data-content").load("/signs/dashboard/showcalendar?id=" + id, function() {
            $("#comm-data").dialog('open');
        });

    }
    if (set_type == 5) {
        $("#comm-data").dialog({
            title: "Sign Parameters ("+location_name+")"
        });
        $("#comm-data-content").load("/signs/dashboard/showsignparam?id=" + id, function() {
            $("#comm-data").dialog('open');
        });

    }
    if (set_type == 6 || set_type == 8) {
        $("#comm-data").dialog({
            title: "Variable Messages ("+location_name+")"
        });
        $("#comm-data-content").load("/signs/dashboard/showmessages?id=" + id+'&set_type='+set_type+'&location_id='+location_id, function() {
         full_set_id = $('div#comm-data-content select[name="full_messages_select"] option:selected').val();
         if(set_type=='6'){
            if (typeof full_set_id === 'undefined'){
                $('div#comm-data-content nav div.element.input-element.place-right').eq(0).show();
            } else {
                $('div#comm-data-content nav div.element.input-element.place-right').eq(0).hide();
            }
            $('div#comm-data-content nav span.element.place-right.nohover').eq(0).hide();
        }

         if(set_type=='8'){
            $('div#comm-data-content nav div.element.input-element.place-right').eq(1).hide();
            $('div#comm-data-content nav span.element.place-right.nohover').eq(1).hide();
        }
            small_set_id = $('div#comm-data-content select[name="small_messages_select"] option:selected').val();
            $('div#comm-data-parameters input[name="small_set_id"]').val(small_set_id);

            $('div#comm-data-parameters input[name="full_set_id"]').val(full_set_id);
            $("#comm-data").dialog('option', 'height',520);
            $("#comm-data").dialog('open');
        });

    }

}
function showData(id, loc_id) {
     if (typeof loc_id === 'undefined' || typeof id === 'undefined') { return; };
     if (id.toString().substring(0,4)=='type') {
        set_type = id.substring(5,6);
        $('div#comm-data-parameters input[name="set_type"]').val(set_type);
        $('div#comm-data-parameters input[name="current_value"]').val(id);
        $('div#comm-data-parameters input[name="location_id"]').val(loc_id);
        $('div#comm-data-parameters input[name="location_name"]').val($('td[location_id="'+loc_id+'"] span').text());

        getData(set_type, id, loc_id);

     } else {

        $.ajax({
            url: '/signs/dashboard/getcommdata/',
            type: 'post',
            dataType: 'json',
            data: {
                'id': id,
		        'loc_id': loc_id
            },
            success: function(d)
            {   
                $('div#comm-data-parameters input[name="set_type"]').val(d['type']);
                $('div#comm-data-parameters input[name="current_value"]').val(id);
                $('div#comm-data-parameters input[name="location_id"]').val(loc_id);
                $('div#comm-data-parameters input[name="location_name"]').val($('td[location_id="'+loc_id+'"] span').text());

                getData(d['type'], id, loc_id,d.loctype);
            }
        });
     }
}

 function showAdvancedSettings (location_id) {
     if (typeof location_id === 'undefined') { return; };
        location_name = $('td[location_id="'+location_id+'"] span').text();
     $("#advanced-settings-content").load("/signs/dashboard/advancedsettings?location_id=" + location_id, function() {
        $("#advanced-settings").dialog({
            title: "Advanced Settings ("+location_name+")"
        });
        $("#advanced-settings").dialog('open');
    });
 }

 function bindRadar (radar_id) {
     if (typeof radar_id === 'undefined') { return; };

     radar_serial = $('td[sign_serial="'+radar_id+'"]').text();
     $('div#bind-location-parameters input[name="radar_serial"]').val(radar_serial);
     $('div#bind-location-parameters input[name="radar_id"]').val(radar_id);
     $("#bind-location").dialog({
        title: "Assign location for radar: "+radar_serial
      });
     $("#bind-location-content").load("/signs/dashboard/getfreelocations/",{
                radar_id: radar_id
            }, function() {
        $("#bind-location").dialog('open');
     });



 }

 function bindRadarLocation() {
    //schedule_name = $('div#comm-data-content select[name="schedule_id"] option:selected').text();

    radar_id = $('div#bind-location-parameters input[name="radar_id"]').val();
    radar_serial = $('div#bind-location-parameters input[name="radar_serial"]').val();
    location_id = $('div#bind-location-content select[name="location"] option:selected').val();
    location_name = $('div#bind-location-content select[name="location"] option:selected').text();

    token = $('token').html();

    if (location_id == 0 ) {
        $("#bind-location").dialog('close');
    } else {

        $.ajax({
            url: '/signs/api/setsignlocation',
            type: 'post',
            data: {
                token: token,
                location_id: location_id,
                sign_id: radar_id
            },

        }).done(function(result)
            {
                if (result.state === 'ok')
                {

                    ShowAlert('Radar Sign '+radar_serial+' successfully assigned to '+location_name, 'Success' );
                    $('td[sign_serial="'+radar_id+'"]').parent('tr').replaceWith( $('td[location_id="'+location_id+'"]').parent('tr') );
                    $('td[sign_serial]',$('td[location_id="'+location_id+'"]').parent('tr')).attr('sign_serial',radar_id).html(radar_serial);
                    $("#bind-location").dialog('close');
                     location.reload(true);
                }
                else
                {
                    ShowAlert("Error! Please, reload page.",'Error');
                }
            });
    }

}


 function showAlerts (location_id) {
     if (typeof location_id === 'undefined') { return; };
     $('div#comm-data-parameters input[name="location_id"]').val(location_id);
     location_name = $('td[location_id="'+location_id+'"] span').text();
     $('div#comm-data-parameters input[name="location_name"]').val(location_name);

     $("#alerts-content").load("/signs/dashboard/alerts?location_id=" + location_id, function() {
        $("#alerts").dialog({
            title: "Alerts Settings ("+location_name+")"
        });
        $("#alerts").dialog('open');
    });
 }

 function ToogleOnOff(alert_type) {
     id="a_of-max-speed"
    if ($('#a_of-' + alert_type)[0].checked === true) {
        $('#a_of-' + alert_type).val('OFF');
        $('#input_' + alert_type).val($('#h_input_' + alert_type).val());
        $('#input_' + alert_type).attr('disabled', false);
        $('#txt_' + alert_type).removeClass('txt_disabled');

    } else {
        $('#a_of-' + alert_type).val('ON');
        $('#h_input_' + alert_type).val($('#input_' + alert_type).val());
        $('#input_' + alert_type).val('OFF');
        $('#input_' + alert_type).attr('disabled', true);
        $('#txt_' + alert_type).addClass('txt_disabled');
    }
}

function selectAddContact() {
    var str = $('#alert-type_contact option:selected').val();
    $('#div_type_contact').hide();
    var cont_array = str.split(',');
    if (cont_array.length == 1 ) return true;
    if ($('tr').is('#contact_' + cont_array['0'])) {
        ShowAlert('This contact has been added already!', 'Warning');
    }
    else {
        if (cont_array['2'] === '0' || cont_array['2'] === '5' ) {
            var img_link = '<img src="/signs/images/email.png" />';
        } else {
            var img_link = '<img src="/signs/images/phone.png" />';
        }

        if ($('#a_of-max-speed').attr('checked') === 'checked') {

            var chekMaxspeed = 'checked="checked"';
        } else {
            var chekMaxspeed = '';
        }

        if ($('#a_of-min-speed').attr('checked') === 'checked') {
            var chekMinspeed = 'checked="checked"';
        } else {
            var chekMinspeed = '';
        }

        if ($('#a_of-low-batt').attr('checked') === 'checked') {
            var chekBatt = 'checked="checked"';
        } else {
            var chekBatt = '';
        }

        var $str = '<tr name="contact" id="contact_' + cont_array['0'] + '">\n\
                         <td style="width:50px">' + img_link + '</td>\n\
                         <td style="width:200px">' + cont_array['1'] + '</td>\n\
                         <td style="width:50px"><input id="cbMaxspeed_' + cont_array['0'] + '" ' + chekMaxspeed + ' type="checkbox"/></td>\n\
                         <td style="width:50px"><input id="cbMinspeed_' + cont_array['0'] + '" ' + chekMinspeed + ' type="checkbox"/></td></td>\n\
                         <td style="width:50px"><input id="cbBatt_' + cont_array['0'] + '" ' + chekBatt + ' type="checkbox"/></td>\n\
                         <td style="width:50px"><a href="#" onclick="delContact(' + cont_array['0'] + ')"><img src="/signs/images/deletered.png" alt="delete" /></a></td>\n\
                   </tr>';
        $("#alert-body_contacts").append($str);

    }
}

function delContact(id) {
    $('#contact_' + id).remove();

}
function saveAlertsSettings() {
    var arrContacts = new Array();
    var arrContactsForSend = new Array();
    location_id = $('div#comm-data-parameters input[name="location_id"]')[0].value;
    location_name = $('div#comm-data-parameters input[name="location_name"]')[0].value;

    var vContacts = document.getElementsByName("contact");
    $.each(vContacts, function(key, element) {
        var value = $(element).attr('id');
        arrContacts[key] = value.replace("contact_", "");
    });

    arrContactsForSend.push({
        attr: '1',
        value: $('#input_low-batt').val()
    });
    arrContactsForSend.push({
        attr: '2',
        value: $('#input_max-speed').val()
    });
    arrContactsForSend.push({
        attr: '3',
        value: $('#input_min-speed').val()
    });

    $.each(arrContacts, function(key, contact) {
        var max;
        if ($('#cbMaxspeed_' + contact).attr('checked') === 'checked') {
            max = 1;
        } else {
            max = 0;
        }

        var min;
        if ($('#cbMinspeed_' + contact).attr('checked') === 'checked') {
            min = 1;
        } else {
            min = 0;
        }

        var batt;
        if ($('#cbBatt_' + contact).attr('checked') === 'checked') {
            batt = 1;
        } else {
            batt = 0;
        }

        arrContactsForSend.push({
            attr: '0',
            value: contact,
            max: max,
            min: min,
            batt: batt
        });
    });


    $.ajax({
        url: '/signs/tools/savealert',
        type: 'post',
        dataType: 'json',
        data: {
            'location_id': location_id,
            'set_points': arrContactsForSend
        },
        success: function(response)
        {
            ShowPromt("The data has been saved!");
            tr = $('table tr td[location_id="'+location_id+'"]').parent();

            max = $('#input_max-speed').val();
            $('td dl dt:contains("Max Speed")',tr).next().html(max);

            min = $('#input_min-speed').val();
            $('td dl dt:contains("Min Speed")',tr).next().html(min);

            batt = $('#input_low-batt').val();
            $('td dl dt:contains("Low Batt")',tr).next().html(batt);

            $("#alerts").dialog('close');
        }
    });

}

function updateData(id, new_type) {
    location_id = $('div#comm-data-parameters input[name="location_id"]')[0].value;
    set_type = $('div#comm-data-parameters input[name="set_type"]')[0].value;

    if (new_type != undefined ) {
        set_type  = new_type;
        $('div#comm-data-parameters input[name="new_type"]').val(new_type);
        $('div#comm-data-parameters input[name="new_value"]').val(id);
        if (new_type == 6) {
            $('div#comm-data-parameters input[name="small_set_id"]').val(id);
        } else if (new_type == 8) {
            $('div#comm-data-parameters input[name="full_set_id"]').val(id);
        }
    }
    if (set_type == 1) {
        lastScheduleSelected = id;
        $("#comm-data-content").load("/signs/dashboard/showschedule?id=" + id+'&lid='+location_id,function() {
             schedule_type = $('select#schedule_ids option:first').attr("name");
         });
        $("#comm-data").dialog({
            title: "Schedule ("+location_name+")"
        });
    }
    if (set_type == 2) {
        $("#comm-data-content").load("/signs/dashboard/showschedule?id=" + id+'&lid='+location_id);
    }
    if (set_type == 3) {
        $("#comm-data-content").load("/signs/dashboard/showbschedule?id=" + id);
    }
    if (set_type == 4) {
        $("#comm-data-content").load("/signs/dashboard/showcalendar?id=" + id);
    }
    if (set_type == 5) {
        $("#comm-data-content").load("/signs/dashboard/showsignparam?id=" + id);

    }
    if (set_type == 6 ) {
        $("#comm-data-content div[small]").load("/signs/dashboard/showsmallmessages?id=" + id);
    }
    if (set_type == 8) {
        $("#comm-data-content div[full]").load("/signs/dashboard/showfullmessages?id=" + id);
    }
}

function saveData() {
    schedule_name = $('div#comm-data-content select[name="schedule_id"] option:selected').text();
    current_id = $('div#comm-data-parameters input[name="current_value"]')[0].value;
    location_id = $('div#comm-data-parameters input[name="location_id"]')[0].value;
    location_name = $('div#comm-data-parameters input[name="location_name"]')[0].value;
    token = $('token').html();

    if ($('div#comm-data-content #schedule_rule_work_page').length >0) {
        set_id = 0;
        set_type = '2';
        schedule_name = 'Express mode'

        form = $("#frmRules"),
        data = form.serialize();
        data = data + '&location_id=' + location_id;
        data = data + '&set_type=' + set_type;
        data = data + '&token='+token;

    }  else {
        set_type = $('div#comm-data-parameters input[name="set_type"]')[0].value;

        if (set_type=='6') {
            small_set_id = $('div#comm-data-parameters input[name="small_set_id"]')[0].value;
            small_name = $('div#comm-data-content select[name="small_messages_select"] option[value='+small_set_id+']').text();
            data = { 'small_set_id' : small_set_id, 'location_id' : location_id, 'token':token, 'set_type':'6'};
        }else if (set_type=='8') {
            full_set_id = $('div#comm-data-parameters input[name="full_set_id"]')[0].value;
            full_name = $('div#comm-data-content select[name="full_messages_select"] option[value='+full_set_id+']').text();
            data = { 'full_set_id': full_set_id, 'location_id' : location_id, 'token':token, 'set_type':'8'};
        } else {
            set_id = $('div#comm-data-content select[name="schedule_id"]')[0].value;

            if (set_type=='2') {
                new_type = $('div#comm-data-parameters input[name="new_type"]')[0].value;
                if ( new_type=='1') {
                    set_type = new_type;
                    //set_id = $('div#comm-data-parameters input[name="new_value"]')[0].value;
                    schedule_name = $('div#comm-data-content select[name="schedule_id"] option[value='+set_id+']').text();
                }
            }
            data = { 'schedule_id' : set_id, 'location_id' : location_id, 'token':token, 'set_type':set_type};
        }

    }
        if ((set_type!=6 && set_type!=8 ) && current_id == set_id && set_id !== 0  ) {
            $("#comm-data").dialog('close');
        } else {

            $.ajax({
                url: '/signs/api/setlocationschedule',
                type: 'post',
                //dataType: 'json',
                data: data,

            }).done(function(result)
                {
                    if (result.state === 'ok') {
                        var unassigned = false;
                        switch (set_type) {
                           /* case '2':
                                set_id = result.schedule_id;
                                stype = 'schedule_id';
                                SettingTypeLabel = 'Schedule';
                                break;*/
                              case '2':
                                stype = 'schedule_id';
                                SettingTypeLabel = 'Express mode';
                                if (schedule_name=='Select ...') {
                                    schedule_name = 'Not assigned';
                                    unassigned = true;
                                } else if (schedule_name!='Schedule') {
                                    //schedule_name = 'Schedule';
                                }
                                break;
                            case '1':
                                stype = 'schedule_id';
                                SettingTypeLabel = 'Schedule';
                                if (schedule_name=='Select ...') {
                                    schedule_name = 'Not assigned';
                                    unassigned = true;
                                } else if (schedule_name!='Express mode') {
                                    //schedule_name = 'Schedule';
                                }
                                break;
                            case '3':
                                stype = 'beacon_id';
                                SettingTypeLabel = 'Beacon';
                                if (schedule_name=='<empty>') {
                                    schedule_name = 'Not Assigned';
                                    unassigned = true;
                                    set_id = '\'type_3\'';
                                }
                                break;

                            case '4':
                                stype = 'calendar_id';
                                SettingTypeLabel = 'Calendar';
                                if (schedule_name=='<empty>'
                                   || schedule_name=='No calendar') {
                                    schedule_name = 'Not Assigned';
                                    unassigned = true;
                                    set_id = '\'type_4\'';
                                } else {
                                    //schedule_name = 'Assigned';
                                }
                                break;
                            case '6':
                             stype = 'message_id';
                                SettingTypeLabel = 'Custom message';
                                if (schedule_name=='<empty>'
                                    || (small_name=='<empty>')){
                                    schedule_name = 'Not Assigned';
                                    unassigned = true;
                                    set_id = 'type_6';
                                } else {
                                    schedule_name = (small_name=='<empty>') ? '' : small_name;
                                }
                                break;

                            case '8':
                                stype = 'message_id';
                                SettingTypeLabel = 'Custom message';
                                if (schedule_name=='<empty>'
                                    ||  full_name=='<empty>'){
                                    schedule_name = 'Not Assigned';
                                    unassigned = true;
                                    set_id = 'type_8';
                                } else {
                                  //  schedule_name = (small_name=='<empty>') ? '' : small_name;
                                    schedule_name = (full_name =='<empty>') ? schedule_name : schedule_name.concat((schedule_name=='')?'':' ',full_name);
                                }
                                break;
                        };

                        if (unassigned) {
                          ShowAlert(location_name + ' has no ' + SettingTypeLabel + ' assigned.', 'Notice');
                        }
                        else {
                          ShowAlert(SettingTypeLabel + ' ' + schedule_name + ' successfully assigned to ' + location_name + '.', 'Notice');
                        }

                        if (set_type == '6' || set_type == '8') {
                            if (small_set_id == '0' && full_set_id == '0') {
                                $('td['+stype+'] a',$('td[location_id="'+location_id+'"]').parent()).attr('onclick','showData(\''+set_id+'\', '+location_id+')');
                                $('td['+stype+']',$('td[location_id="'+location_id+'"]').parent()).attr(stype,set_id);
                            }else if ( small_set_id != '' && small_set_id != '0') {
                                $('td['+stype+'] a',$('td[location_id="'+location_id+'"]').parent()).attr('onclick','showData('+small_set_id+', '+location_id+')');
                                $('td['+stype+']',$('td[location_id="'+location_id+'"]').parent()).attr(stype,small_set_id);
                            } else if ( full_set_id != '' && full_set_id != '0') {
                                $('td['+stype+'] a',$('td[location_id="'+location_id+'"]').parent()).attr('onclick','showData('+full_set_id+', '+location_id+')');
                                $('td['+stype+']',$('td[location_id="'+location_id+'"]').parent()).attr(stype,full_set_id);
                            }

                        } else {
                            $('td['+stype+']',$('td[location_id="'+location_id+'"]').parent()).attr(stype,set_id);
                            $('td['+stype+'] a',$('td[location_id="'+location_id+'"]').parent()).attr('onclick','showData('+set_id+', '+location_id+')');
                        }
                        switch (set_type) {
                            case '1':
                                if (schedule_name!='Express mode') {
                                    $('td['+stype+'] a',$('td[location_id="'+location_id+'"]').parent()).html('Schedule');
                                    location.reload(true);
                                }
                                break;
                            case '2':
                                if (schedule_name!='Schedule') {
                                    $('td['+stype+'] a',$('td[location_id="'+location_id+'"]').parent()).html('Express mode');
                                    location.reload(true);
                                }
                                break;
                            case '3':
                            case '4':
                            case '6':
                            case '8':
                                $('td['+stype+'] a',$('td[location_id="'+location_id+'"]').parent()).html((schedule_name!='Not Assigned' && schedule_name!='')?'Assigned':'Not Assigned');
                                 location.reload(true);
                                break;
                            default:
                                $('td['+stype+'] a',$('td[location_id="'+location_id+'"]').parent()).html(schedule_name);
                        }

                        $("#comm-data").dialog('close');
                        //refreshScheduleShowLocations();

                        // HACK: Reload content in manage modal for lowCost camera
                        //camerasManageControl.modalControl('sign');

                    }
                    else
                    {
                        ShowAlert("Error! Please, reload page.");
                    }
                });

        }



}

function saveAdvancedSettings(){

    var data = Array();
    var bLow = Math.round($("#slider-range").slider("option", "values")[1] * 16 / 100) - 1;
    var bHigh = Math.round($("#slider-range").slider("option", "values")[0] * 16 / 100) - 1;
    var strobeBM1 = $('#strobe_flashing option:selected').val();
    var strobeBM2 = $('#strobe_series  option:selected').val();


    data.push({
        id: $("#setting_id").val(),
        location_id: $("#setting_lid").val(),
        brightness_byte: (bHigh << 4) | bLow,
        dig_blink_mode: $('#led_flashing option:selected').val(),
        strobe_blink_mode: strobeBM1 | strobeBM2,
        head_power: $("#amount_radar").val(),
        head_target_mode: $('#detection_mode option:selected').val(),
        sign_sn: $('#sign-sn  option:selected').val()
    });

    $.ajax({
        url: '/signs/manage/saveadvancedsetting/',
        type: 'post',
        //dataType: 'json',
        data: {
            'data': data
        },
        success: function(response)
        {
            location_id = $("#setting_lid").val();
            location_name = $('td[location_id="'+location_id+'"] span').text();

            ShowAlert("Advanced settings has been saved! ("+location_name+")",'Success');

            tr = $('table tr td[location_id="'+location_id+'"]').parent();

            brightness = $('div#signs-view-content input#amount').val()+' - '+$('div#signs-view-content input#amount2').val();
            $('td dl dt:contains("Brightness")',tr).next().html(brightness);

            LEDs_Flashing_Speed = $('div#signs-view-content select#led_flashing :selected').text();
            $('td dl dt:contains("LEDs Flashing Speed")',tr).next().html(LEDs_Flashing_Speed);

            Strobe = $('div#signs-view-content select#strobe_series :selected').text()+'; '+$('div#signs-view-content select#strobe_flashing :selected').text();
            $('td dl dt:contains("Strobe")',tr).next().html(Strobe);

            fastest_closest = $('div#signs-view-content select#detection_mode :selected').text();
            fastest_closest = fastest_closest.replace(' Vehicle','');
            Radar_Sensitivity = $('div#signs-view-content input#amount_radar').val()+'%; '+fastest_closest;
            $('td dl dt:contains("Radar Sensitivity")',tr).next().html(Radar_Sensitivity);

             $("#advanced-settings").dialog('close');
        }
    });

}

function getExpressModeWindow(id,location_id)
{
    location_id = typeof location_id !== 'undefined'?location_id:$('div#comm-data-parameters input[name="location_id"]')[0].value;
    id = typeof id !== 'undefined'?id:$('div#comm-data-parameters input[name="current_value"]')[0].value;
    $("#comm-data").dialog("option", "height", "635");
    url = "/signs/dashboard/showexpressmode?id="+id+"&lid=" + location_id;


    $("#comm-data-content").load(url, function() {
        //$('#frmRules').attr("action", url);
        $('div#comm-data-content select[name="schedule_id"]').val(lastScheduleSelected);
        $("#comm-data").dialog({
            title: "Express mode ("+location_name+")"
        });
        $("#comm-data").dialog('open');
        if(schedule_type!='undefined'){
           $('#sign_type').val(schedule_type);
            set_sign_type(schedule_type);
        }
    });

}