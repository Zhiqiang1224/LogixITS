//# sourceURL=signs/js/reports.js
/*
 *
 * @autor Denys Cherkasov arp.denis@gmail.com
 */

$(document).ready(function() {
    $("#loading").dialog({
        resizable: false,
        autoOpen: false,
        modal: true,
        width: 150,
        height: 150
    }); //end dialog

// Changed Location
    $("#locations").change(function() {
        var id = $("#locations").val();
        var type = $("#report_type").val();
        if (id !== '0') {
            $("#report").load("/signs/reports/gettamplate/", {}, function() {
                $('#divStep3').load('/signs/reports/getdaterange?lid=' + id + '&type=' + type, {}, function() {
                    var str = $("select[name=data_type] option:selected").html();
                    if (str === null) {
                        str = '';
                    } else {
                        str = " - " + str;
                    }
                    ;
                    $("#tables h2").html($("#report_type option:selected").html() + str);
                    $('#divStep3').show();
                });
                $("#tblLocationName").html($("#locations option:selected").html());
                getlocationdata($("#locations").val());
            });
        } else {
            $("#report").load("/signs/reports/gettamplate/", {}, function() {
                $('#divStep3').hide();
                $("#tblLocationAddress").html("");
                $("#tblLocationName").html("");
            });
        }
    });


});

//Get Location Data for tamplate
function getlocationdata(id) {
    $.ajax({
        url: '/signs/reports/getlocationdata/',
        type: 'post',
        dataType: 'json',
        data: {
            'id': id
        },
        success: function(r)
        {
            var str = "";
            if (r['location']['address'] !== "") {
                str = r['location']['address'];
            }
            if (r['location']['city'] !== "") {
                str += "  " + r['location']['city'];
            }
            if (r['location']['state'] !== "") {
                str += "  " + r['location']['state'];
            }
            if (r['location']['country'] !== "") {
                str += "  " + r['location']['country'];
            }
            if (r['location']['zip'] !== "") {
                str += "  " + r['location']['zip'];
            }
            $("#tblLocationAddress").html(str);
        }
    });
}

// Build Comparison Report
function buildComparisonReport() {

    var slbydef;
    if ($("#stop_limit1").prop('checked')) {
        slbydef = "on";
    } else {
        slbydef = "off";
    }

    var year = $("#year option:selected").val();
    var month = $("#month option:selected").val();
    var speed_limit = $("#speed_limit").val();
    var location = $("#locations option:selected").val();
    var data_type = $("select[name=data_type] option:selected").val();
    var week = [];
    var i = 0;
    $.each($("input.weeks:checked"), function(e) {
        week[i] = $(this).val();
        i++;
    });
    var week2 = [];
    var i = 0;
    $.each($("input.twoweeks:checked"), function(e) {
        week2[i] = $(this).val();
        i++;
    });
    $(".ui-dialog-titlebar").hide();
    $("#loading").dialog("open");
    $("#report").hide();
    $("#report").load("/signs/reports/cbuild/",
            {
                week: week,
                weeks: week2,
                year: year,
                month: month,
                slbydef: slbydef,
                speed_limit: speed_limit,
                location: location
                //data_type: data_type

            }, function(
            ) {

        $("#report").show();
        $("#loading").dialog("close");
        $('.ui-tabs-anchor').css('color','black');
         bindWeeklyTabs();

    });
}
function bindWeeklyTabs() {
    // remember tab widget is from jQuery UI 1.8! not from 1.9 check correct documentation when needed
    $("#tabs").tabs({
        select: function(event, ui) {
            if (ui.index > 0) {
                StartLoading();
            }
        },
        load: function(event, ui) {
            SttopLoading();
            $('.ui-tabs-anchor').css('color','black');
        },
    });
    /*
    $( "#tabs" ).on( "tabsbeforeload", function( event, ui ) {
        console.log('132');
        StartLoading();
    } );
    $( "#tabs" ).on( "tabsload", function( event, ui ) {
        SttopLoading();
    } );
    */
}
// Build Weekly report
function buildWeeklyReport() {
    var slbydef;
    if ($("#stop_limit1").prop('checked')) {
        slbydef = "on";
    } else {
        slbydef = "off";
    }

    var year = $("#year option:selected").val();
    var month = $("#month option:selected").val();
    var speed_limit = $("#speed_limit").val();
    var location = $("#locations option:selected").val();
    var data_type = $("select[name=data_type] option:selected").val();
    var week = [];
    var i = 0;
    $.each($("input.weeks:checked"), function(e) {
        week[i] = $(this).val();
        i++;
    });
    $(".ui-dialog-titlebar").hide();
    $("#loading").dialog("open");
    $("#report").hide();
    $("#report").load("/signs/reports/wbuild/",
            {
                week: week,
                year: year,
                month: month,
                slbydef: slbydef,
                speed_limit: speed_limit,
                location: location,
                data_type: data_type

            }, function(
            ) {
        $("#report").show();
        $("#loading").dialog("close");
        bindWeeklyTabs();
        $('.ui-tabs-anchor').css('color','black');
        $('#tabs-1').height($(window).height() - 120);

    });
}
//buildCustomReport
function buildCustomReport() {
    var slbydef;
    if ($("#stop_limit1").prop('checked')) {
        slbydef = "on";
    } else {
        slbydef = "off";
    }
    var information_type = $("select[name=information_type] option:selected").val();
    var size = $("select[name=size] option:selected").val();

    var start_date = $("#from_datepicker").val();
    var end_date = $("#to_datepicker").val();
    var ds = new Date();
    ds.setTime(Date.parse(start_date));
    var de = new Date();
    de.setTime(Date.parse(end_date));
    if (de >= ds) {
        var speed_limit = $("#speed_limit").val();
        var location = $("#locations option:selected").val();
        var group_records = $("select[name=group_records] option:selected").val();
        var report_name = $("input[name=report_name]").val();
        var data_type_1 = $("input[name=data_type_1]").prop("checked");
        var data_type_2 = $("input[name=data_type_2]").prop("checked");
        var data_type_3 = $("input[name=data_type_3]").prop("checked");
        var data_type_4 = $("input[name=data_type_4]").prop("checked");
        var data_type_5 = $("input[name=data_type_5]").prop("checked");
        var data_type_6 = $("input[name=data_type_6]").prop("checked");
        var data_type_7 = $("input[name=data_type_7]").prop("checked");
        var data_type_8 = $("input[name=data_type_8]").prop("checked");
        var data_type_9 = $("input[name=data_type_9]").prop("checked");
        var data_type_10 = $("input[name=data_type_10]").prop("checked");
        var data_type_11 = $("input[name=data_type_11]").prop("checked");
        var data_type_12 = $("input[name=data_type_12]").prop("checked");
        var data_type_13 = $("input[name=data_type_13]").prop("checked");
        var data_type_14 = $("input[name=data_type_14]").prop("checked");
        var range_to = $("input[name=range_to]").val();
        var range_from = $("input[name=range_from]").val();

        var week = [];
        var i = 0;
        $.each($("input.weeks:checked"), function(e) {
            week[i] = $(this).val();
            i++;
        });
        $(".ui-dialog-titlebar").hide();
        $("#loading").dialog("open");
        $("#report").hide();
        $("#report").load("/signs/reports/cubuild/",
                {
                    range_to: range_to,
                    range_from: range_from,
                    end_date: end_date,
                    start_date: start_date,
                    information_type: information_type,
                    size: size,
                    slbydef: slbydef,
                    speed_limit: speed_limit,
                    location: location,
                    report_name: report_name,
                    data_type_1: data_type_1,
                    data_type_2: data_type_2,
                    data_type_3: data_type_3,
                    data_type_4: data_type_4,
                    data_type_5: data_type_5,
                    data_type_6: data_type_6,
                    data_type_7: data_type_7,
                    data_type_8: data_type_8,
                    data_type_9: data_type_9,
                    data_type_10: data_type_10,
                    data_type_11: data_type_11,
                    data_type_12: data_type_12,
                    data_type_13: data_type_13,
                    data_type_14: data_type_14,
                    group_records: group_records

                }, function(
                ) {

            for (var i = 1; i <= 14; i++) {
                if (!$("input[name=data_type_" + i + "]").prop("checked")) {
                    $(".type_" + i).hide();
                }
            }
            $("#report").show();
            $("#loading").dialog("close");
            $('.ui-tabs-anchor').css('color','black');

            $("div#parametrs input").click(function() {
                for (var i = 1; i <= 14; i++) {
                    if (!$("input[name=data_type_" + i + "]").prop("checked")) {
                        $(".type_" + i).hide();
                    } else {
                        $(".type_" + i).show();
                    }
                }
            });
        });
    } else {
        ShowAlert("You need to specify a date range that includes the Available Date Range for this Location!");
    }
}
//Change Data Type of Report
function changeDataType(e) {
    var str = $("select[name=data_type] option:selected").html();
    if (str === null) {
        str = '';
    } else {
        str = " - " + str;
    }
    $("#tables h2").html($("#report_type option:selected").html() + str);
}
//Write Date range to tamplate
function CheckDataRange() {
    var strfrom = "";
    var strto = "";
    for (var i = 4; i >= 0; i--)
    {
        if ($("input[name=\"week[" + (i) + "]\"]").attr("checked") === "checked") {
            strfrom = $("#fweek" + i).html();
        }
    }
    for (var i = 0; i <= 4; i++)
    {
        if ($("input[name=\"week[" + (i) + "]\"]").attr("checked") === "checked") {
            strto = $("#tweek" + i).html();
        }
    }
    $("#tblPeriod").html(strfrom + " to " + strto);
}
function CheckDataRange2() {
    var strfrom = "";
    var strto = "";
    for (var i = 4; i >= 0; i--)
    {
        if ($("input[name=\"weeks[" + (i) + "]\"]").attr("checked") === "checked") {
            strfrom = $("#fweeks" + i).html();
        }
    }
    for (var i = 0; i <= 4; i++)
    {
        if ($("input[name=\"weeks[" + (i) + "]\"]").attr("checked") === "checked") {
            strto = $("#tweeks" + i).html();
        }
    }
    $("#tblPeriod2").html(strfrom + " to " + strto);
}


//buildBattVoltReport
function buildBattVoltReport() {

    var start_date = $("#from_datepicker").val();
    var end_date = $("#to_datepicker").val();
    var location = $("#locations option:selected").val();
    $(".ui-dialog-titlebar").hide();
    $("#loading").dialog("open");
    $("#report").hide();
    $("#report").load("/signs/reports/bvbuild3/",
            {
                end_date: end_date,
                start_date: start_date,
                location: location
            }, function(
            ) {
        $("#report").show();
        $("#loading").dialog("close");
    });
}
//buildBattVoltReport
function buildSpeedBinsReport() {

    var start_date = $("#from_datepicker").val();
    var end_date = $("#to_datepicker").val();
    var location = $("#locations option:selected").val();
    $(".ui-dialog-titlebar").hide();
    $("#loading").dialog("open");
    $("#report").hide();
    $("#report").load("/signs/reports/sbbuild3/",
            {
                end_date: end_date,
                start_date: start_date,
                location: location
            }, function(
            ) {
        $("#report").show();
        $("#loading").dialog("close");
    });
}
//buildAlertReport
function buildAlertReport() {

    var start_date = $("#from_datepicker").val();
    var end_date = $("#to_datepicker").val();
    var location = $("#locations option:selected").val();
    $(".ui-dialog-titlebar").hide();
    $("#loading").dialog("open");
    $("#report").hide();
    $("#report").load("/signs/reports/arbuild3/",
            {
                end_date: end_date,
                start_date: start_date,
                location: location
            }, function() {
        if ($("#chkHideAckn").attr("checked")) {

            $(".acknowledg").hide();
        } else {
            $(".acknowledg").show();
        }

        $("#report").show();
        $("#loading").dialog("close");
    });
}
//build camera alert report
function buildCamAlertReport() {

    var start_date = $("#from_datepicker").val();
    var end_date = $("#to_datepicker").val();
    var location = $("#locations option:selected").val();
    $(".ui-dialog-titlebar").hide();
    $("#loading").dialog("open");
    $("#report").hide();
    $("#report").load("/reports/crbuild3/",
            {
                end_date: end_date,
                start_date: start_date,
                location: location
            }, function(
            ) {
        $("#report").show();
        $("#loading").dialog("close");
    });
}
//buildCommunicationReport
function buildCommunicationReport() {

    var start_date = $("#from_datepicker").val();
    var end_date = $("#to_datepicker").val();
    var location = $("#locations option:selected").val();
    $(".ui-dialog-titlebar").hide();
    $("#loading").dialog("open");
    $("#report").hide();
    $("#report").load("/signs/reports/clbuild3/",
            {
                end_date: end_date,
                start_date: start_date,
                location: location
            }, function(
            ) {
        $("#report").show();
        $("#loading").dialog("close");
    });
}

function HidePanel() {
    $("#from_datepicker").trigger( 'blur' );
    $("#to_datepicker").trigger( 'blur' );
    $("#reports_wizard").hide("slide", 500);
    $("#showpanel").show("slide", 500);

}
function ShowPanel() {
    $("#reports_wizard").show("slide", 500);
    $("#showpanel").hide("slide", 500);
}
function StartLoading() {
    $(".ui-dialog-titlebar").hide();
    $("#loading").dialog("open");
    $("#report").hide();
}
function SttopLoading() {
    $("#report").show();
    $("#loading").dialog("close");
}
function FullScreenChart() {
    $("#charts").dialog({
        resizable: true,
        autoOpen: true,
        modal: true,
        width: $(window).width() - 10,
        height: $(window).height()
    }); //end
    $("#placeholderBig").width($(window).width() - 100);
    $("#placeholderBig").height($(window).height() - 100);
    $(".chartBig-container").width($(window).width() - 110);
    $(".chartBig-container").height($(window).height() - 110);
    $("#placeholder").empty();
    pushChart("#placeholderBig");
    $(".ui-dialog-titlebar").hide();
    $("#charts").dialog("open");
}
function exitFullScreenChart() {
    pushChart("#placeholder");
    $("#placeholderBig").empty();
    $("#charts").dialog("close");
}
function showChartLegend() {
    $("#chart-control").show();
}
function hideChartLegend() {
    $("#chart-control").hide();

}
function printChart(pdf) {
    var ctx = plot.getCanvas();
    var img = ctx.toDataURL();
    var img = img.replace(/^data:image\/(png|jpg);base64,/, "");
    var tickLabels = $('.tickLabels').html();
    var chartControl = $('#chart-control #labeler').html();


    $.ajax({
        url: '/signs/reports/savechart/',
        type: 'post',
        async: true,
        dataType: 'json',
        data: {
            'img': img,
            tickLabels: tickLabels,
            chartControl: chartControl,
            params: ''
        },
        success: function(r)
        {
            if (pdf === true) {
                window.open("/signs/reports/wbuildpdfcustom?id=3&params=", '_blank');
            } else {
                window.open("/signs/reports/wbuildprint?id=3&params=", '_blank');
            }

        }
    });
}
function printCanvas()
{
    var ctx = plot.getCanvas();
    var img = ctx.toDataURL();
    var windowContent = '<!DOCTYPE html>';
    windowContent += '<html>'
    windowContent += '<head><title>Print Chart</title></head>';
    windowContent += '<body>'
    windowContent += '<img src="' + img + '">';
    windowContent += '</body>';
    windowContent += '</html>';
    var printWin = window.open('', '', '');
    printWin.document.open();
    printWin.document.write(windowContent);
    printWin.document.close();
    printWin.focus();
    printWin.print();
    printWin.close();
}

function loadReport(report_id, type) {
    $("#loading").dialog("open");
    $("#" + report_id).load("/signs/reports/" + report_id,
            function() {
                $('.tab-control').tabcontrol();
                $("#report").show();
                $("#loading").dialog("close");
                if (type !== undefined) {
                    loadChart(report_id, type);
                }
            });
}

function loadChart(chart_id, type) {
    $("#loading").dialog("open");
    $("#" + chart_id + type).load("/signs/reports/" + chart_id,
            {"type": type},
    function(
            ) {
        $('.tab-control').tabcontrol();
        $("#report").show();
        $("#loading").dialog("close");
    });
}

function goToWeeklyReport() {
    window.location = '/signs/reports/weekly';
}

function goToComparisonReport() {
    window.location = '/signs/reports/comparison';
}

function goToPoverSupplyReport() {
    window.location = '/signs/reports/power';
}

function goToCustomReport() {
    window.location = '/signs/reports/custom';
}

function goToSpeedRangeReport() {
    window.location = '/signs/reports/srange';
}

function goToSyncLogReport() {
    window.location = '/signs/reports/synclog';
}

function goToReports(){
    window.location = '/signs/reports/index';
}



function showData(id) {

    $.ajax({
        url: '/signs/reports/getcommdata/',
        type: 'post',
        dataType: 'json',
        data: {
            'id': id
        },
        success: function(d)
        {
            if (d['type'] == 1) {
                $("#comm-data").dialog({
                    title: "Schedule"
                });
                $("#comm-data").load("/signs/reports/showschedule?id=" + id).dialog('open');
            }
            if (d['type'] == 2) {
                $("#comm-data").dialog({
                    title: "Express Mode"
                });
                $("#comm-data").load("/signs/reports/showschedule?id=" + id).dialog('open');
            }
            if (d['type'] == 3) {
                $("#comm-data").dialog({
                    title: "Beacon Schedule"
                });

                $("#comm-data").load("/signs/reports/showbschedule?id=" + id).dialog('open');
            }
            if (d['type'] == 4) {
                $("#comm-data").dialog({
                    title: "Calendar"
                });
                $("#comm-data").load("/signs/reports/showcalendar?id=" + id).dialog('open');
            }
            if (d['type'] == 5) {
                $("#comm-data").dialog({
                    title: "Sign Parameters"
                });
                $("#comm-data").load("/signs/reports/showsignparam?id=" + id).dialog('open');
            }
            if (d['type'] == 6) {
                $("#comm-data").dialog({
                    title: "Variable Small Messages"
                });
                $("#comm-data").load("/signs/reports/showmessages?id=" + id).dialog('open');
            }

            if (d['type'] == 8) {
                $("#comm-data").dialog({
                    title: "Variable Big Messages"
                });
                $("#comm-data").load("/signs/reports/showmessages?id=" + id).dialog('open');
            }
    
             if (d['type'] == '30') { 
                $("#comm-data").dialog({
                    title: d['name']
                });
                $("#comm-data").dialog('open');
                if(d['name'] === 'Small Message Unassigned'){
                    $('div#comm-data').html('The Small Message that was uploaded to the sign has been unassigned.');
                }
                if(d['name'] === 'Big Message Unassigned'){
                    $('div#comm-data').html('The Big Message that was uploaded to the sign has been unassigned.');
                }

                if(d['name'] === 'Beacon Unassigned'){
                    $('div#comm-data').html('The Beacon that was uploaded to the sign has been unassigned.');
                }

                if(d['name'] === 'Delete Small Message'){
                    $('div#comm-data').html('The Small Message that was uploaded to the sign has been deleted.');
                }
                if(d['name'] === 'Delete Big Message'){
                    $('div#comm-data').html('The Big Message that was uploaded to the sign has been deleted.');
                }
                if(d['name'] === 'Delete Schedule'){
                    $('div#comm-data').html('The Schedule that was uploaded to the sign has been deleted.');
                }
                if(d['name'] === 'Delete Calendar'){
                    $('div#comm-data').html('The Calendar that was uploaded to the sign has been deleted.');
                }
                if(d['name'] === 'Delete Beacon Schedule'){
                    $('div#comm-data').html('The Beacon Schedule that was uploaded to the sign has been deleted.');
                }
            }
	    
	    if (d['type'] == 40) {
                $("#comm-data").dialog({
                    title: "Calendar Unassigned"
                });
		 $("#comm-data").dialog('open');
                if(d['name'] === 'Calendar Unassigned'){
                    $('div#comm-data').html('The Calendar that was uploaded to the sign has been unassigned.');
                }
            }
                 /*   if (d['type'] == null) {
                        //ShowAlert('Record is empty!', 'Warning');
                         $("#comm-data").dialog({
                            title: "Express Mode"
                        });
                        $("#comm-data").load("/signs/reports/showschedule?id=" + id).dialog('open');
                    }*/
        }
    });
}
;

function printCustom(event) {
    //event.preventDefault();
    columns_to_print = [];
    $('#parametrs .parametrs-body input').each(function (idx) {
        columns_to_print.push([ this.value, this.checked]);
    });
    window.open('/signs/reports/wbuildprint?id=1&params=' + JSON.stringify(columns_to_print));
    return false;
}

function printCustomPdf(event) {
    //event.preventDefault();
    columns_to_print = [];
    $('#parametrs .parametrs-body input').each(function (idx) {
        columns_to_print.push( [this.value, this.checked] );
    });
    window.open('/signs/reports/wbuildpdf?id=1&params=' + JSON.stringify(columns_to_print));
    return false;
}

function getCSVData(tableId) {
    var csv_value;

    if (typeof tableId === 'undefined'){
        tableId = '#datatable';
    }
    if(tableId == '#srange-data'){
        var appendContentTopFirstRow = $('.js-appendContentTopFirstRow').text(),
            appendContentTopsecondRow = $('.js-appendContentTopsecondRow').text(),
            appendContentBottom = $('.js-appendContentBottom').text(),
            middleResult = $(tableId).table2CSV({delivery: 'value'});

        csv_value = appendContentTopFirstRow + '\n' + appendContentTopsecondRow + '\n' + middleResult + '\n' + appendContentBottom;

    }else {
        csv_value = $(tableId).table2CSV({delivery: 'value'});
    }

    $("#csv_text").val(csv_value);
    $('#csvForm').submit();
}