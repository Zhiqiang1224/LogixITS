//# sourceURL=signs/js/matrix.js

var edit=true;
function collapse() {
    $('tr.schedule').show();
    $('img.plus').hide();
    $('img.minus').show();
}

function expand() {
    $('tr.schedule').hide();
    $('img.plus').show();
    $('img.minus').hide();
}

$(document).ready(function(){
    bindSpoilerToggle();
   
    $("#add_msg_frm").dialog({
        resizable: false,
        autoOpen: false,
        modal: true,
        width: 400,
        height: 270,
        buttons: {
            Save: function() {
                if (edit) {
                    SaveEditedMessageGroup();
                } else {
                    SaveNewMessageGroup();
                }
            }, // end continue button
            Close: function() {
                $(this).dialog('close');
            } //end cancel button
        }//end buttons
    });//end dialog
    
    $("#div_matrix_editor").dialog({
        title: 'Edit Custom Message',
        resizable: false,
        autoOpen: false,
        modal: true,
        width: 830,
        height: 890,
        buttons: {
            Save: function() {
                btn_save_clicked();
            }, // end continue button
            Cancel: function() {
                btn_cancel_clicked();
            } //end cancel button
        }//end buttons
    });//end dialog
    
    $("#edt_text").keypress(edt_text_keypressed);
    
    $("#upload_msessage").dialog({
        resizable: false,
        autoOpen:false,
        modal: true,
        width:450,
        height:180,
        buttons: {
            Save: function() {
                       
                ajaxFileUpload();   
            }, // end continue button
            Close: function() {
                $(this).dialog('close');
            } //end cancel button
        }//end buttons
    });//end dialog
   
    $("#wndSetLocation").dialog({
        resizable: true,
        autoOpen: false,
        modal: true,
        width: 800,
        height: 500,
        buttons: {
            Ok: function() {
                SetLocations();

            },
            Close: function() {
                $(this).dialog('close');
            }
        }
    });
});     
    
function AddMessageGroup(){
    edit=false;
    $('#ui-dialog-title-add_msg_frm').html("Add Message Group");
    $("#add_msg_frm label.input-control.radio").show();
    $('select[name=animation_speed] option[value=2]').attr('selected', 'selected');
    $("#add_msg_frm").dialog('open');
    $("#schedulename").val('');

}  
function EditMessageGroup(id){
    edit=true;
    $('#ui-dialog-title-add_msg_frm').html("Edit Message Group");
    $("#add_msg_frm label.input-control.radio").hide();
    $.ajax({
        url: '/signs/matrix/edit/',
        type: 'post',
        dataType: 'json',
        data: {
            'getForm': id
        },
        success: function(response)
        { 
            if(response['schedulename']){
                $("#schedulename").val(response['schedulename']);
                $("#schedulename-id").val(id);
                
                $('select[name=animation_speed]').val(response['animation_speed']);
                $("#add_msg_frm").dialog('open');
                $("#animation_speed").show();
            } else {
                response['errMessage'];
            }
        }     
    }); 
            
    //$("#add_msg_frm").dialog('open');
}

function SaveNewMessageGroup(){
    if (edit) return SaveEditedMessageGroup();
    var schedulename = $("#schedulename").val();
    var matrixType = $("input[name = messages-type]:checked").val();
    var animation_speed = $("select[name=animation_speed] option:selected").val();
    
    $.ajax({
        url: '/signs/matrix/add/',
        type: 'post',
        dataType: 'json',
        data: {
            'schedulename': schedulename,
            'matrixType':matrixType,
            'animation_speed':animation_speed,
                
        },
        success: function(response)
        { 
            if(response['schedule_add_successfully']){
                ShowPromt("The data has been saved!"); 
                $("#add_msg_frm").dialog('close');
                location.reload(); 
            } else {
                ShowAlert(response['errMessage'], 'Warning');
            }
                
        }     
    });    
        
}    
   
function SaveEditedMessageGroup(){
    
    var schedulename = $("#schedulename").val();
    var id = $("#schedulename-id").val();
    var animation_speed = $("select[name=animation_speed] option:selected").val();
    $.ajax({
        url: '/signs/matrix/edit/',
        type: 'post',
        dataType: 'json',
        data: {
            'schedulename': schedulename,
            'id': id,
            'animation_speed':animation_speed,
        },
        success: function(response)
        { 
            if(response['schedule_edit_successfully']){
                ShowPromt("The data has been saved!"); 
                $("#add_msg_frm").dialog('close');
                location.reload(); 
            } else {
                ShowAlert(response['errMessage'], 'Warning');
            }
            
        }     
    });    
    
}    
function ShowEditMessageFrm(id){
        
        
    $.ajax({
        url: '/signs/matrix/editrecord/',
        type: 'post',
        dataType: 'json',
        data: {
            'id': id
        },
        success: function(response)
        { 
            if(response['form']){
                $("#edit_msessage").html(response['form']);
               // $("#edit_msessage").dialog('open');
            } else {
                ShowAlert(response['errMessage'], 'Warning');
            }
                
        }     
    });   
}
function SaveEditedMessage() {
        
    var  desc = $("#desc").val();
    var  id = $("#id").val();
    var  show_speed;
    if($("#show_speed").is(':checked')){
        show_speed = 1;
    } else {
        show_speed=0;
    }
    var  animation_frame;
    if($("#animation_frame").is(':checked')){
        animation_frame = 1;
    } else {
        animation_frame=0;
    }
            
    $.ajax({
        url: '/signs/matrix/chekrules/',
        type: 'post',
        dataType: 'json',
        data: {
            'id': id

        },
        success: function(r)
        {
            if (r['lids'] > 0) {
                ShowConfirm("These rule changes will be applied and uploaded to " + r['lids'] + " locations using this messages.",
                        "Warning", "SavingConfirm('" + id + "','" + desc + "','" + show_speed + "','" + animation_frame + "')");
            } else {
                SavingConfirm(id, desc, show_speed, animation_frame);
            }

        }
    });
}   
function SavingConfirm(id,desc,show_speed,animation_frame){
    $.ajax({
        url: '/signs/matrix/editrecord/',
        type: 'post',
        dataType: 'json',
        data: {
            'id': id,
            'desc': desc,
            'show_speed': show_speed,
            'animation_frame': animation_frame
        },
        success: function(response)
        {
            if (response['editrecord_successfully']) {
                ShowPromt("The data has been saved!"); 
                //$("#edit_msessage").dialog('close');
                location.reload();
            } else {
                ShowAlert(response['errMessage'], 'Warning');
            }

        }
    }); 
}
function UploadConfirm(id) {
    $.ajaxFileUpload
            (
                    {
                        url: '/signs/matrix/upload/',
                        secureuri: false,
                        fileElementId: 'file',
                        contentType: 'application/json',
                        dataType: 'json',
                        data: {
                            upload: true,
                            id: id
                        },
                        success: function(data, status)
                        {
                            if (typeof(data.upload_successfully) != 'undefined')
                            {
                                if (data.upload_successfully == 'TRUE')
                                {
                                    ShowPromt("The data has been saved!"); 
                                    $("#upload_msessage").dialog('close');
                                    location.reload();
                                } else
                                {
                                    if (data.upload_successfully == 'WRONG_SIZE')
                                    {
                                        var text = "The matrix size of the message you selected does not match the matrix size of the current message slot. Please do one of the following:<br>"+
                                                   "  - Select a message that matches the size of the message slot, or <br>" +
                                                   "  - Select a message slot that matches the size of the message.";
                                        ShowAlert(text, "The message matrix does not match the size of the message slot");
                                    }  else {
                                          ShowECustomMessageBox();
                                    }
                                  

                                }
                            }
                        },
                        error: function(data, status, e)
                        {
                            ShowAlert(e, 'Warning');
                        }
                    }
            )

    return false;
}

function UploadMessage(id) {
    $.ajax({
        url: '/signs/matrix/upload/',
        type: 'post',
        dataType: 'json',
        data: {
            'id': id
        },
        success: function(response)
        { 
            if(response['form']){
                $("#upload_msessage").html(response['form']);
                $("#upload_msessage").dialog('open');
            } else {
                ShowAlert(response['errMessage'], 'Warning');
            }
        
        }     
    });   
}
       
function ajaxFileUpload(){
        
    var  id = $("#id").val();
    $.ajax({
        url: '/signs/matrix/chekrules/',
        type: 'post',
        dataType: 'json',
        data: {
            'id': id

        },
        success: function(r)
        {
            if (r['lids'] > 0) {
                ShowConfirm("These rule changes will be applied and uploaded to " + r['lids'] + " locations using this messages.",
                        "Warning", "UploadConfirm('" + id  + "')");
            } else {
                UploadConfirm(id);
            }

        }
    });    
}

$(document).ready(function() {
    $("#repmess").dialog({
        resizable: false,
        autoOpen:false,
        modal: true,
        width:650,
        height:300,
        buttons: {
            Ok: function() {
                $("#repmess").dialog('close');
                    
            }
        }//
    });//end dialog        
})

function ShowRCustomMessageBox(text,title){
    $('#repmess').dialog( "option", "title", title );
    $('#repmess').dialog("open");
    $("#repmess_container").html(text);
   
      
}
function ShowECustomMessageBox(){
    $('#repmess').dialog( "option", "title", "The selected message file uses the incorrect file format." );
    $('#repmess').dialog("open");
    
}

function DeleteMsgGrp(id,count){
    if (parseInt(count)>0){
         ShowConfirm("Are you sure you want to delete this custom message group? It is being used by "+count+" locations at this time...",
            "Warning", "DeleteMsgGrpConfirm('" + id + "')");   
    } else {
        ShowConfirm("Are you sure you want to delete this custom message group?",
                "Warning", "DeleteMsgGrpConfirm('" + id + "')");   
    }
   
}
function DeleteMsgGrpConfirm(id) {
    $.getJSON(
            '/signs/matrix/delete',
            {id: id},
    function(results)
    {
        if (results.state == 'ok')
        {
            location.reload();
        }
        else
        {
            ShowAlert(results.errors, 'Warning');
        }
    }

    );
}

function SetLocationForSchedule(set_id, type) {
    var name = $('tr#schedule-'+set_id+' td.list-group.schedule_name').text();
    $("#wndSetLocation-content").load("/signs/matrix/addlocations",
            {
                set_id: set_id,
                type:type
            }, function(
            ) {
        $("#wndSetLocation").dialog({
        title: "Set Locations For [ "+name+" ]"
        });
        $("#wndSetLocation").show();
        $("#wndSetLocation").dialog("open");
	    $("input.check-one").each(function() {
            if($(this).is(':disabled')) {
               $(this).hide(); 
            }        
        });
    });

}

function SetLocations() {
    var searchIDs = $('input.check-one:checked').map(function() {

        return $(this).val();

    });
    $.ajax({
        url: '/signs/matrix/addlocations/',
        type: 'post',
        dataType: 'json',
        data: {
            'locations': searchIDs.get(),
            'set_id': $("#set_id").val(),
            'type': $("#type").val()

        },
        success: function(response)
        {
            if (response['result'] == 1) {
                ShowPromt("The data has been saved!"); 
                $("#wndSetLocation").dialog("close");
            } else {
                ShowCustomMessageBox(text.ServerExeption)
            }

        }
    });
}

$(document).on("click", "input#animation_frame",function(){ 
    if (this.checked) {
        $('div#animation_speed').show();
    } else {
        $('div#animation_speed').hide();
    }
});

$(document).on("change", '#div_matrix_editor input#file', function(e) {
    var file = e.target.files[0];
    if (!file) {
      return;
    }
    var reader = new FileReader();
    reader.onload = function(e) {
        if (file.size > 4000) {
            ShowAlert('File is too big!',"Error");
            return true;
        }
        if (file.name.substr(file.name.length - 4) != '.rsm' 
            && file.name.substr(file.name.length - 4) != '.lrm' ) {
            ShowAlert('Wrong file extension! .lrm  and  .rsm only accepted!',"Error");
            return true;
        }
        $('#div_matrix_editor input#desc').val(file.name);
        var contents = e.target.result;
        var lines = contents.match(/[^\r\n]+/g);
        code ='';
        l = lines.length;
        for (i=0; i<l; i++) {
            str = lines[i];
            if (str.substr(0, 6) == 'matrix') {
                pos = str.length - 45;
                code = code.concat(str.substr(pos, 2),str.substr(pos+6, 2), str.substr(pos+12, 2), str.substr(pos+18, 2),
                        str.substr(pos+24, 2),str.substr(pos+30, 2), str.substr(pos+36, 2), str.substr(pos+42, 2));
            }
            if (str.substr(0, 5) == 'Name=') {
                $('#div_matrix_editor input#desc').val(str.substr(5,str.length - 5));
            }
        }
        clear_board();
        //draw_board();
        str2board(code);
    };
    reader.readAsText(file);
  
})

function copy_matrix() {  
    var message =  board2str();
    $.ajax({
        url: '/signs/matrix/copytoclipboard/',
        type: 'post',
        dataType: 'json',
        data: {
            'message': message
        },
        success: function(response)
        { 
            if(response['success'] != 'ok'){
                ShowAlert(response['errMessage'], 'Error');
            }
        
        }     
    });   
};

function paste_matrix() {  
    $.ajax({
        url: '/signs/matrix/pastefromclipboard/',
        type: 'get',
        dataType: 'json',
        data: { },
        success: function(response)
        { 
            if(response['success'] == 'ok'){
                message = response['message'];
                clear_board();
                str2board(message);
            } else {
                ShowAlert(response['errMessage'], 'Error');
            }
        
        }     
    });   
};


function export_matrix() {  
    //var message =  board2str();
    
    file = $('#div_matrix_editor input#desc').val();
    if (file.substr(file.length-4) != '.rsm') {
        file = file.concat('.rsm');
    }
    newLine = String.fromCharCode(0x0D).concat(String.fromCharCode(0x0A));
    //link.download = file;
    
    message = "[message]\r\n";
    
    matrix =  board2str().match(/.{1,16}/g);
    for(i=0; i<matrix.length; i++) {
        message = message.concat("matrix",i);
        str = matrix[i];
        for(j=1;j<9;j++) {
            message = message.concat((j==1)?'=0x':', 0x',str.substr((j-1)*2,2));
        }
        message = message.concat(" \r\n");
    }
    message = message+"[Properties]\r\n";
    message = message+"Name="+file.substr(0,file.length - 4)+"\r\n";
    message = message+"Mode=0\r\n";
    message = message+"Type="+((matrix.length>9)?'SP800':'SP600')+"\r\n";
    
    $.ajax({
        url: '/signs/matrix/export/',
        type: 'post',
        dataType: 'json',
        data: {
            'message': message
        },
        success: function(response)
        { 
            if(response['success'] != 'ok'){
                ShowAlert(response['errMessage'], 'Error');
            } else {
                window.open('/signs/matrix/exportfile/'.concat(file))
            }
        
        }     
    });   
};

//LED cell size in pixels:
var CELL_W = 13;
var CELL_H = 13;

//sign sizes:
var SP600_W = 32;
var SP600_H = 16;
var SP800_W = 32;
var SP800_H = 40;

//default matrix size - vertical SP800:
var DEF_MATRIX_W = SP800_W;
var DEF_MATRIX_H = SP800_H;

var MATRIX_W = DEF_MATRIX_W;
var MATRIX_H = DEF_MATRIX_H;

var INIT_MATRIX_W = MATRIX_W;
var INIT_MATRIX_H = MATRIX_H;

//canvas size in pixels:
var CANVAS_W, CANVAS_H;

var gDivElementObj, gCanvasElement, gCanvasElementObj, gContext;
var gLED0, gLED1;
var gMsgID = 0;
var gMsgNo = 0;

var gBoard; //array[x][y] of boolean;
var gDrawing; //=true if drawing

var gFonts = [
    {   //Default font
        "A": "7E0909097E000000",
        "B": "7F49494936000000",
        "C": "3E41414122000000",
        "D": "7F4141413E000000",
        "E": "7F49494941000000",
        "F": "7F09090901000000",
        "G": "3E41415132000000",
        "H": "7F0808087F000000",
        "I": "417F410000000000",
        "J": "40413F0000000000",
        "K": "7F08142241000000",
        "L": "7F40404040000000",
        "M": "7F0204027F000000",
        "N": "7F0408107F000000",
        "O": "3E4141413E000000",
        "Q": "3E4151215E000000",
        "P": "7F09090906000000",
        "R": "7F09192946000000",
        "S": "2649494932000000",
        "T": "01017F0101000000",
        "U": "3F4040403F000000",
        "V": "1F2040201F000000",
        "W": "3F4030403F000000",
        "X": "6314081463000000",
        "Y": "0304780403000000",
        "Z": "6151494543000000",
        "a": "2054545478000000",
        "b": "7E48484830000000",
        "c": "3844444400000000",
        "d": "304848487E000000",
        "e": "3854545458000000",
        "f": "7C12120204000000",
        "g": "0854545438000000",
        "h": "7E08087000000000",
        "i": "007A000000000000",
        "j": "40403A0000000000",
        "k": "7E10284400000000",
        "l": "027E000000000000",
        "m": "7C047C0478000000",
        "n": "7C04047800000000",
        "o": "3844443800000000",
        "p": "7C24241800000000",
        "q": "1824247C00000000",
        "r": "7C04040800000000",
        "s": "4854545424000000",
        "t": "3E48484000000000",
        "u": "3C40407C00000000",
        "v": "1C2040201C000000",
        "w": "3C4060403C000000",
        "x": "6C10106C00000000",
        "y": "4C50503C00000000",
        "z": "4464544C44000000",
        "$": "242A7F2A12000000",
        "?": "0201510906000000",
        "!": "5F00000000000000",
        "'": "0700000000000000",
        "(": "3E41000000000000",
        ")": "413E000000000000",
        ".": "4000000000000000",
        ",": "C000000000000000",
        "0": "3E4141413E000000",
        "1": "427F400000000000",
        "2": "4261514946000000",
        "3": "2241494936000000",
        "4": "0F0808087F000000",
        "5": "4F49494931000000",
        "6": "3E49494932000000",
        "7": "0101790503000000",
        "8": "3649494936000000",
        "9": "264949493E000000",
        "_": "4040404040000000"
    },
    {   //Narrow font
        "A": "7E09097E00000000",
        "B": "7F49493600000000",
        "C": "3E41412200000000",
        "D": "7F41413E00000000",
        "E": "7F49494100000000",
        "F": "7F09090100000000",
        "G": "3E41513200000000",
        "H": "7F08087F00000000",
        "I": "417F410000000000",
        "J": "41413F0000000000",
        "K": "7F08146300000000",
        "L": "7F40404000000000",
        "M": "7F0204027F000000",
        "N": "7F04087F00000000",
        "O": "3E41413E00000000",
        "P": "7F09090600000000",
        "Q": "3E41617E00000000",
        "R": "7F19294600000000",
        "S": "2649493200000000",
        "T": "01017F0101000000",
        "U": "3F40403F00000000",
        "X": "7708087700000000",
        "Y": "2748483F00000000",
        "Z": "7149494700000000",
        "a": "3048487800000000",
        "b": "7C48483000000000",
        "c": "3048480000000000",
        "d": "3048487C00000000",
        "e": "3854545800000000",
        "f": "7814040000000000",
        "g": "0854543800000000",
        "h": "7E08087000000000",
        "i": "7A00000000000000",
        "j": "4034000000000000",
        "k": "7C10284000000000",
        "l": "047C000000000000",
        "m": "7808780870000000",
        "n": "7808087000000000",
        "o": "3048483000000000",
        "p": "7828281000000000",
        "q": "1028287800000000",
        "r": "7008080000000000",
        "s": "4854240000000000",
        "t": "3C50400000000000",
        "u": "3840407800000000",
        "v": "3840403800000000",
        "w": "3840604038000000",
        "x": "4830304800000000",
        "y": "1860180000000000",
        "z": "4868584800000000",
        "$": "242A7F2A12000000",
        "?": "0201510906000000",
        "!": "5F00000000000000",
        "'": "0700000000000000",
        "(": "3E41000000000000",
        ")": "413E000000000000",
        ".": "4000000000000000",
        ",": "C000000000000000",
        "0": "3E4141413E000000",
        "1": "427F400000000000",
        "2": "4261514946000000",
        "3": "2241494936000000",
        "4": "0F0808087F000000",
        "5": "4F49494931000000",
        "6": "3E49494932000000",
        "7": "0101790503000000",
        "8": "3649494936000000",
        "9": "264949493E000000",
        "_": "4040404000000000"
    }
];

var gFontsMatrix = [];

var gBoardBackup = "";
        
function init_board(mw, mh, update_screen) {
    MATRIX_W = mw || DEF_MATRIX_W;
    MATRIX_H = mh || DEF_MATRIX_H;
    CANVAS_W = CELL_W * MATRIX_W;
    CANVAS_H = CELL_H * MATRIX_H;
    update_screen = (typeof update_screen != "undefined") ? update_screen : true;
    if (update_screen) {
        gDivElementObj.width(CANVAS_W).height(CANVAS_H);
        gCanvasElementObj.attr("width", CANVAS_W).attr("height", CANVAS_H);
    } //if
    clear_board();
    if (gFontsMatrix.length == 0)
      make_matrix();
}

function clear_board() {
    gBoard = [];
    for (var x = 0; x < MATRIX_W; x++) {
        gBoard[x] = [];
        for (var y = 0; y < MATRIX_H; y++) {
            gBoard[x][y] = false;
        }
    }
}

function draw_led(x, y, is_on) {
    gContext.drawImage(is_on ? gLED1 : gLED0, parseInt(x * CELL_W), parseInt(y * CELL_H));
}

function draw_board() {
    var x, y;

    //clear canvas:
    gContext.fillStyle = "#BBBBBB";
    gContext.fillRect(0, 0, gCanvasElement.width - 1, gCanvasElement.height - 1);

    //draw LEDs:
    for (x = 0; x < MATRIX_W; x++) {
        for (y = 0; y < MATRIX_H; y++) {
            draw_led(x, y, gBoard[x][y]);
        }
    }

    //draw grid 8x8:
    gContext.beginPath();
    gContext.strokeStyle = "black";
    for (x = 8; x < MATRIX_W; x += 8) {
      gContext.moveTo(x * CELL_W + 0.5, 0);
      gContext.lineTo(x * CELL_W + 0.5, gCanvasElement.height - 1);
    }
    for (y = 8; y < MATRIX_H; y += 8) {
      gContext.moveTo(0, y * CELL_H + 0.5);
      gContext.lineTo(gCanvasElement.width - 1, y * CELL_H + 0.5);
    }
    gContext.stroke();
}

function get_cursor_pos(e) {
    var parentOffset = gCanvasElementObj.parent().offset();
    var x = Math.floor((e.pageX - parentOffset.left) / CELL_W);
    var y = Math.floor((e.pageY - parentOffset.top) / CELL_H);
    return {"x": x, "y": y};
}

function board_mousedown(e) {
    gDrawing = true;
    board_mousemove(e);
}

function board_mouseup(e) {
    gDrawing = false;
}

function board_mousemove(e) {
    if (gDrawing) {
        var pt = get_cursor_pos(e);
        var is_on = $("#btn_draw").prop("checked");
        gBoard[pt.x][pt.y] = is_on;
        draw_led(pt.x, pt.y, is_on);
    } //if
}

function board_mouseout(e) {
    gDrawing = false;
}

function get_board(rotation) {
    //rotation: 0 = no change, 1 = 90 deg. clockwise, 2 = 180 deg., 3 = 270 deg.
    rotation = rotation || 0;
    switch (rotation) {
        case 0:
        default:
            return gBoard;

        case 1:
            var b = [];
            for (var y = 0; y < MATRIX_H; y++) {
                b[MATRIX_H - y - 1] = [];
                for (var x = 0; x < MATRIX_W; x++) {
                    b[MATRIX_H - y - 1][x] = gBoard[x][y];
                }
            }
            return b;

        case 2:
            var b = [];
            for (var x = 0; x < MATRIX_W; x++) {
                b[x] = [];
                for (var y = 0; y < MATRIX_H; y++) {
                    b[x][y] = gBoard[MATRIX_W - x - 1][MATRIX_H - y - 1];
                }
            }
            return b;

        case 3:
            var b = [];
            for (var y = 0; y < MATRIX_H; y++) {
                b[MATRIX_H - y - 1] = [];
                for (var x = 0; x < MATRIX_W; x++) {
                    b[MATRIX_H - y - 1][x] = gBoard[MATRIX_W - x - 1][MATRIX_H - y - 1];
                }
            }
            return b;
    } //switch
}

function set_board(b) {
    for (var x in b) {
        for (var y in b[x]) {
            gBoard[x][y] = b[x][y];
        }
    }
}

function is_vertical() {
    return (MATRIX_H > SP600_H) ? (MATRIX_H > MATRIX_W) : (MATRIX_H < MATRIX_W);
}

function str2board(hex) {
    function str2board_vertical(hex) {
        var x, y, byte, mask, matrix_row = 0;
        var len = hex.length;

        x = MATRIX_W - 1;
        if (len > 128) { //big message
            y = SP800_H - 1;
        }
        else { //small message
            y = SP600_H - 1;
        }

        for (var i = 0; i < len - 1; i += 2) {
            byte = parseInt(hex.substr(i, 2), 16);
            if (isNaN(byte))
                byte = 0;
            mask = 0x01;
            while (mask <= 0x80) {
                gBoard[x][y] = (byte & mask) > 0;
                mask <<= 1;
                y--;
            } //while
            matrix_row++;
            x--;
            y += 8;
            if (matrix_row >= 8) {
                matrix_row = 0;
                if (x < 0) {
                    x = MATRIX_W - 1;
                    y -= 8;
                    if (y < 0)
                        break;
                } //if (x < 0)
            } //if (matrix_row >= 8)
        } //for i
    } //function str2board_vertical(hex)

    clear_board();

    if (is_vertical()) { //vertical big or horizontal small
        str2board_vertical(hex);
    }
    else {
        init_board(MATRIX_H, MATRIX_W, false); //make it vertical, but don't update the screen
        str2board_vertical(hex);
        var b = get_board(1);
        init_board(MATRIX_H, MATRIX_W, false); //make it horizontal again
        set_board(b);
    } //else

    draw_board();
}

function board2str() {
    function byte2hex(byte) {
        //zero-pad, if necessary:
        return ((byte < 16) ? "0" : "") + byte.toString(16).toUpperCase();
    }

    function board2str_vertical(b) {
        var x = MATRIX_W - 1;
        var y = MATRIX_H - 1;
        var byte, mask, matrix_row = 0;
        var hex = "";

        for ( ; ; ) {
            byte = 0;
            mask = 0x01;
            while (mask <= 0x80) {
                if (b[x][y])
                    byte |= mask;
                mask <<= 1;
                y--;
            } //while
            hex = hex + byte2hex(byte);
            matrix_row++;
            x--;
            y += 8;
            if (matrix_row >= 8) {
                matrix_row = 0;
                if (x < 0) {
                    x = MATRIX_W - 1;
                    y -= 8;
                    if (y < 0)
                        break;
                } //if (x < 0)
            } //if (matrix_row >= 8)
        } //for

        return hex;
    } //function board2str_vertical(b)

    if (is_vertical()) { //vertical big or horizontal small
        return board2str_vertical(gBoard);
    }
    else {
        return board2str_vertical(get_board(3));
    } //else
}

function shift_board(dir) {
    //dir: 0 = left, 1 = right, 2 = up, 3 = down.
    var x, y;
    dir = dir || 0;
    switch (dir) {
        case 0: default:
            for (y = 0; y < MATRIX_H; y++) {
              if (gBoard[0][y])
                return;
            }
            for (y = 0; y < MATRIX_H; y++) {
                for (x = 0; x < MATRIX_W - 1; x++) {
                    gBoard[x][y] = gBoard[x + 1][y];
                }
                gBoard[MATRIX_W - 1][y] = false;
            }
            break;

        case 1:
            for (y = 0; y < MATRIX_H; y++) {
              if (gBoard[MATRIX_W - 1][y])
                return;
            }
            for (y = 0; y < MATRIX_H; y++) {
                for (x = MATRIX_W - 1; x > 0; x--) {
                    gBoard[x][y] = gBoard[x - 1][y];
                }
                gBoard[0][y] = false;
            }
            break;

        case 2:
            for (x = 0; x < MATRIX_W; x++) {
              if (gBoard[x][0])
                return;
            }
            for (x = 0; x < MATRIX_W; x++) {
                for (y = 0; y < MATRIX_H - 1; y++) {
                    gBoard[x][y] = gBoard[x][y + 1];
                }
                gBoard[x][MATRIX_H - 1] = false;
            }
            break;

        case 3:
            for (x = 0; x < MATRIX_W; x++) {
              if (gBoard[x][MATRIX_H - 1])
                return;
            }
            for (x = 0; x < MATRIX_W; x++) {
                for (y = MATRIX_H - 1; y > 0; y--) {
                    gBoard[x][y] = gBoard[x][y - 1];
                }
                gBoard[x][0] = false;
            }
            break;
    } //switch
}

function shadow_board(dir) {
    //dir: 0 = left, 1 = right, 2 = up, 3 = down.
    var x, y;
    dir = dir || 0;
    switch (dir) {
        case 0: default:
            for (y = 0; y < MATRIX_H; y++) {
                for (x = 1; x < MATRIX_W; x++) {
                    if (gBoard[x][y])
                        gBoard[x - 1][y] = true;
                }
            }
            break;

        case 1:
            for (y = 0; y < MATRIX_H; y++) {
                for (x = MATRIX_W - 2; x >= 0; x--) {
                    if (gBoard[x][y])
                        gBoard[x + 1][y] = true;
                }
            }
            break;

        case 2:
            for (x = 0; x < MATRIX_W; x++) {
                for (y = 1; y < MATRIX_H; y++) {
                    if (gBoard[x][y])
                        gBoard[x][y - 1] = true;
                }
            }
            break;

        case 3:
            for (x = 0; x < MATRIX_W; x++) {
                for (y = MATRIX_H - 2; y >= 0; y--) {
                    if (gBoard[x][y])
                        gBoard[x][y + 1] = true;
                }
            }
            break;
    } //switch
}

function invert_board() {
  for (var x = 0; x < MATRIX_W; x++) {
    for (var y = 0; y < MATRIX_H; y++) {
      gBoard[x][y] = !gBoard[x][y];
    }
  }
}

//main constructor:
function show_editor(width, height, lMsgID) {
    gMsgID =  lMsgID;
    $.ajax({
        url: '/signs/matrix/editrecord/',
        type: 'post',
        dataType: 'json',
        data: {
            'id': gMsgID
        },
        success: function(response)
        {
             if (response['result']) {
                if (response['result']['animation_frame']=='1'){
                    $("div#div_matrix_editor #animation_frame").attr('checked', true);
                }else {
                    $("div#div_matrix_editor #animation_frame").attr('checked', false);
                }
                if (response['result']['type'] != '8') {
                    $("#show_speed").parent().show();
                    if (response['result']['show_speed'] == '1') {
                        $("#show_speed").attr('checked', true);
                    } else {
                        $("#show_speed").attr('checked', false);
                    }
                } else {
                    $("#show_speed").parent().hide();
                }
                var gMsgNo = response['result']['slot'];
                var msg_name = response['result']['desc'];
                var code = response['result']['message'];
                $("#div_matrix_editor").dialog('open');
                gDivElementObj = $("#div_board");
                gCanvasElementObj = $("#canvas_board");
                gCanvasElementObj.mousedown(board_mousedown)
                                 .mouseup(board_mouseup)
                                 .mousemove(board_mousemove)
                                 .mouseout(board_mouseout);

                gCanvasElement = document.getElementById("canvas_board");
                gContext = gCanvasElement.getContext("2d");

                gLED0 = document.getElementById("led0");
                gLED1 = document.getElementById("led1");

                //controls:
                $("#span_drawmode").buttonset();
                $(".matrix_editor_btn").button();
                $("#desc").val(msg_name);

                INIT_MATRIX_W = width;
                INIT_MATRIX_H = height;
                init_board(width, height);
                str2board(code);
                
                $("#edt_text").val("");
                $("#btn_removetext").hide();
            } else {
                ShowAlert(response['errMessage'], 'Warning');
            }
        }
    });   
}

function update_text() {
  var lines = $("#edt_text").val().split("\n");
  var font_idx = $("#sel_font").val();
  var space_x = parseInt($("#sel_space_x").val());
  var space_y = parseInt($("#sel_space_y").val());
  var transparent = $("#cb_transparent").is(":checked");
  
  //vertical alignment:
  var shift_y = 0;
  switch (parseInt($("#sel_veralign").val())) {
    case 0:
      shift_y = 1;
      break;
    case 1:
      shift_y = parseInt((MATRIX_H / 2) - (lines.length * (8 + space_y) / 2));
      break;
    case 2:
      shift_y = MATRIX_H - lines.length * (8 + space_y);
      break;
  } //switch
  
  for (var line = 0; line < lines.length; line++) {
    if (line >= MATRIX_H / 8) //max no of lines
      break;
    
    var px = 0;
    var s = lines[line];
    var w = get_str_width(font_idx, s, space_x);
    var py = shift_y + line * (7 + space_y);
    //horizontal alignment:
    switch (parseInt($("#sel_horalign").val())) {
      case 0:
        px = 1;
        break;
      case 1:
        px = parseInt((MATRIX_W / 2) - (w / 2));
        break;
      case 2:
        px = MATRIX_W - w - 1;
        break;
    } //switch

    if (px < 1)
      px = 1;

    if (px > MATRIX_W)
      px = MATRIX_W;

    //draw string:
    for (var i = 0; i < s.length; i++) {
      var ch = s.charAt(i);
      var char_width = get_char_width(font_idx, ch);
      if (!char_width)
        continue; //no such character
      if (px + char_width > MATRIX_W + 1)
        break; //out of borders
      draw_char(font_idx, px, py, ch, transparent);
      px += char_width + space_x;
    } //for i
  } //for line

  draw_board();
}

function get_str_width(font_idx, s, space_x) {
  var res = 0;
  var len = s.length;
  for (var i = 0; i < len; i++) {
    res += get_char_width(font_idx, s.charAt(i));
    if (i < len - 1)
      res += space_x;
  } //for
  return res;
}

function get_char_width(font_idx, ch) {
  if (typeof gFontsMatrix[font_idx] == "undefined" ||
      typeof gFontsMatrix[font_idx][ch] == "undefined")
    return 0;

  var res = 0;
  for (var i = 0; i < 8; i++) {
    if (gFontsMatrix[font_idx][ch][i])
      res = i + 1;
  } //for
  return res;
}

function draw_char(font_idx, x, y, ch, transparent) {
  if (typeof gFontsMatrix[font_idx] == "undefined" ||
      typeof gFontsMatrix[font_idx][ch] == "undefined")
    return; //no such character in this font

  var max_x = gBoard.length - 1;
  var max_y = gBoard[0].length - 1;
  for (var nx = 0; nx < 8; nx++) {
    var bx = x + nx;
    for (var ny = 0; ny < 8; ny++) {
      var by = y + ny;
      var mask = 1 << ny;
      if (bx <= max_x && by <= max_y) {
        if (gFontsMatrix[font_idx][ch][nx] & mask) {
          gBoard[bx][by] = true;
        }
        else {
          if (!transparent)
            gBoard[bx][by] = false;
        } //else
      } //if
    } //for ny
  } //for nx
}

function make_matrix() {
  //convert gFonts[] from hex strings to binary gFontsMatrix[font_idx][ch][0..7]:
  for (var i = 0; i < gFonts.length; i++) {
    gFontsMatrix[i] = [];
    for (var ch in gFonts[i]) {
      gFontsMatrix[i][ch] = [];
      var s = gFonts[i][ch]; //e.g. "7E0909097E000000"
      for (var j = 0; j < 8; j++) {
        gFontsMatrix[i][ch][j] = parseInt(s.charAt(2*j) + s.charAt(2*j+1), 16);
      } //for j
    } //for ch
  } //for i
}

function edt_text_keypressed(event) {
  if (event.which == 13) {
    //avoid dialog events on ENTER:
    event.preventDefault();
    $("#edt_text").val($("#edt_text").val() + "\n");
    return false;
  }
}

//========================================= user controls: =========================================

function str2board_btn_clicked() {
    var hex = $("#hex_string").val();
    str2board(hex);
}

function board2str_btn_clicked() {
    $("#hex_string").val(board2str());
}

function btn_clear_clicked() {
    clear_board();
    draw_board();
}

function btn_rotate_clicked() {
    var b = get_board(1);
    init_board(MATRIX_H, MATRIX_W);
    set_board(b);
    draw_board();
}

function btn_shift_clicked(dir) {
    shift_board(dir);
    draw_board();
}

function btn_addtext_clicked() {
  if (gBoardBackup == "") {
    gBoardBackup = board2str();
  }
  else {
    str2board(gBoardBackup);
  }
  update_text();
  $("#btn_removetext").show();
}

function btn_removetext_clicked() {
  if (gBoardBackup != "") {
    str2board(gBoardBackup);
    gBoardBackup = "";
    $("#btn_removetext").hide();
  }
}

function btn_shadow_clicked(dir) {
  shadow_board(dir);
  draw_board();  
}

function btn_invert_clicked() {
  invert_board();
  draw_board();
}

function btn_save_clicked() {
    //TO DO:
    //close_editor(gMsgID, gMsgNo, $("#msg_name").val(), board2str());
    if (MATRIX_H === INIT_MATRIX_H && MATRIX_W === INIT_MATRIX_W ) {
      var show_speed;
      if ($("#show_speed").is(':checked')) {
          show_speed = 1;
      } else {
          show_speed = 0;
      }
      var animation_frame;
      if ($("#animation_frame").is(':checked')) {
          animation_frame = 1;
      } else {
          animation_frame = 0;
      }

      $.ajax({
          url: '/signs/matrix/saveMessage/',
          type: 'post',
          dataType: 'json',
          data: {
              'gMsgID': gMsgID,
              'gMsgNo': gMsgNo,
              'desc': $("#desc").val(),
              'message': board2str(),
              'show_speed': show_speed,
              'animation_frame': animation_frame
          },
          success: function(response)
          {
             if (response.result) {
                  $('.message_image_'+gMsgID).attr('src', $('.message_image_' + gMsgID).attr('src') + '&' + Math.random());
                  $('.message_desc_' + gMsgID).html($("#desc").val());
                  gBoardBackup = "";
                  $("#div_matrix_editor").dialog("close");
             }
          }
      });
    } else {
        if (INIT_MATRIX_H == 40) {
            ShowAlert("Please rotate message board to VERTICAL position to save custom message"); 
        } else {
            ShowAlert("Please rotate message board to HORIZONTAL  position to save custom message");
        }
    }
}

function btn_cancel_clicked() {
    gBoardBackup = "";
    $("#div_matrix_editor").dialog("close");
}

jQuery.extend({
    createUploadIframe: function(id, uri)
    {
        //create frame
        var frameId = 'jUploadFrame' + id;
        var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
        if (window.ActiveXObject)
        {
            if (typeof uri == 'boolean') {
                iframeHtml += ' src="' + 'javascript:false' + '"';

            }
            else if (typeof uri == 'string') {
                iframeHtml += ' src="' + uri + '"';

            }
        }
        iframeHtml += ' />';
        jQuery(iframeHtml).appendTo(document.body);

        return jQuery('#' + frameId).get(0);
    },
    createUploadForm: function(id, fileElementId, data)
    {
        //create form	
        var formId = 'jUploadForm' + id;
        var fileId = 'jUploadFile' + id;
        var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');
        if (data)
        {
            for (var i in data)
            {
                jQuery('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
            }
        }
        var oldElement = jQuery('#' + fileElementId);
        var newElement = jQuery(oldElement).clone();
        jQuery(oldElement).attr('id', fileId);
        jQuery(oldElement).before(newElement);
        jQuery(oldElement).appendTo(form);



        //set attributes
        jQuery(form).css('position', 'absolute');
        jQuery(form).css('top', '-1200px');
        jQuery(form).css('left', '-1200px');
        jQuery(form).appendTo('body');
        return form;
    },
    ajaxFileUpload: function(s) {
        // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout		
        s = jQuery.extend({}, jQuery.ajaxSettings, s);
        var id = new Date().getTime()
        var form = jQuery.createUploadForm(id, s.fileElementId, (typeof (s.data) == 'undefined' ? false : s.data));
        var io = jQuery.createUploadIframe(id, s.secureuri);
        var frameId = 'jUploadFrame' + id;
        var formId = 'jUploadForm' + id;
        // Watch for a new set of requests
        if (s.global && !jQuery.active++)
        {
            jQuery.event.trigger("ajaxStart");
        }
        var requestDone = false;
        // Create the request object
        var xml = {}
        if (s.global)
            jQuery.event.trigger("ajaxSend", [xml, s]);
        // Wait for a response to come back
        var uploadCallback = function(isTimeout)
        {
            var io = document.getElementById(frameId);
            try
            {
                if (io.contentWindow)
                {
                    xml.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
                    xml.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;

                } else if (io.contentDocument)
                {
                    xml.responseText = io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
                    xml.responseXML = io.contentDocument.document.XMLDocument ? io.contentDocument.document.XMLDocument : io.contentDocument.document;
                }
            } catch (e)
            {
                jQuery.handleError(s, xml, null, e);
            }
            if (xml || isTimeout == "timeout")
            {
                requestDone = true;
                var status;
                try {

                    status = isTimeout != "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified

                    if (status != "error")
                    {
                        // process the data (runs the xml through httpData regardless of callback)

                        var data = jQuery.uploadHttpData(xml, s.dataType);

                        // If a local callback was specified, fire it and pass it the data
                        if (s.success)
                            s.success(data, status);

                        // Fire the global callback
                        if (s.global)
                            jQuery.event.trigger("ajaxSuccess", [xml, s]);
                    } else
                        jQuery.handleError(s, xml, status);
                } catch (e)
                {
                    status = "error";
                    //   jQuery.handleError(s, xml, status, e);
                }

                // The request was completed
                if (s.global)
                    jQuery.event.trigger("ajaxComplete", [xml, s]);

                // Handle the global AJAX counter
                if (s.global && !--jQuery.active)
                    jQuery.event.trigger("ajaxStop");

                // Process result
                if (s.complete)
                    s.complete(xml, status);

                jQuery(io).unbind()

                setTimeout(function()
                {
                    try
                    {
                        jQuery(io).remove();
                        jQuery(form).remove();

                    } catch (e)
                    {
                        jQuery.handleError(s, xml, null, e);
                    }

                }, 100)

                xml = null

            }
        }
        // Timeout checker
        if (s.timeout > 0)
        {
            setTimeout(function() {
                // Check to see if the request is still happening
                if (!requestDone)
                    uploadCallback("timeout");
            }, s.timeout);
        }
        try
        {

            var form = jQuery('#' + formId);
            jQuery(form).attr('action', s.url);
            jQuery(form).attr('method', 'POST');
            jQuery(form).attr('target', frameId);
            if (form.encoding)
            {
                jQuery(form).attr('encoding', 'multipart/form-data');
            }
            else
            {
                jQuery(form).attr('enctype', 'multipart/form-data');
            }
            jQuery(form).submit();

        } catch (e)
        {
            jQuery.handleError(s, xml, null, e);
        }

        jQuery('#' + frameId).load(uploadCallback);
        return {abort: function() {
            }};

    },
    uploadHttpData: function(r, type) {
        var data = !type;
        data = type == "xml" || data ? r.responseXML : r.responseText;
        // If the type is "script", eval it in global context
        if (type == "script")
            jQuery.globalEval(data);
        // Get the JavaScript object, if JSON is used.
        if (type == "json")
            eval("data = " + data);
        // evaluate scripts within html
        if (type == "html")
            jQuery("<div>").html(data).evalScripts();

        return data;
    }
})
