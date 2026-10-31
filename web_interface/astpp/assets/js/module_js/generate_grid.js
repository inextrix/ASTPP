// CSRF: token for state-changing GET actions (assets/js/astpp_csrf.js, hooks/csrf_guard.php)
function csrf_url(url) {
    return typeof window.astpp_csrf_url === 'function' ? window.astpp_csrf_url(url) : url;
}
$(document).ready(function () {

    $("#show_search").click(function () {
        $("#search_bar").slideToggle("slow");
    });
    $("#global_clearsearch_filter").click(function () {
        $("#search_bar").slideToggle("slow");
        $("#left_panel_search_form").slideToggle("slow");
    });
    $("#left_panel_add").click(function () {
        $("#left_panel_form").slideToggle("slow");
    });
    $("#left_panel_search").click(function () {
        $("#left_panel_search_form").slideToggle("slow");
    });
    $("#updatebar").click(function () {
        $("#update_bar").slideToggle("slow");
    });
    $("#global_clearbatchupdate_filter").click(function () {
        $("#update_bar").slideToggle("slow");
    });
    $('.checkall').click(function () {
        $('.chkRefNos').prop('checked', $(this).prop('checked'));//if you want to select/deselect checkboxes use this
    });
    $('.toast-close-button').click(function () {
        $("#toast-container").css("display", "none");
        $("#toast-container_error").css("display", "none");
    });
});
function quick_search(destination) {
    url = base_url + destination;
    var value = document.getElementById("left_panel_quick_search").value;
    $.ajax({
        type: "POST",
        url: url,
        data: "left_panel_search=" + value,
        success: function (response) {

            $('.flex_grid').flexOptions({
                newp: 1
            }).flexReload();
        }
    });
}
function get_alert_message(first_cnt, second_cnt, id, flag) {
    var str = '';
    if (flag == '1') {
        if (first_cnt != '' && second_cnt != '') {
            str += 'This rate group is using by ' + second_cnt + ' accounts and ' + first_cnt + ' origination rates \n';
        }
        else if (first_cnt != '' || second_cnt != '') {
            var field_name = 'origination rates';
            if (second_cnt != '') {
                field_name = 'customers';
            }
            str += 'This rate group is using by ' + second_cnt + field_name + '.\n';
        }
        var answer = confirm(str + 'Are you sure you want to delete ?');
        return answer;
    }
    if (flag == '2') {
        var answer = confirm('This trunk is using by ' + first_cnt + ' outbound rates. Are you sure you want to delete ?');
        return answer;
    }
    if (flag == '3') {
        var answer = confirm("Are you sure want to delete? This action will delete all other data which belongs to this account(s).");
        return answer;
    }
    if (flag == '4') {
        var answer = confirm("Are you sure want to delete? This action will delete all other data which belongs to this account(s).");
        return answer;
    }
}
function get_alert_msg(id) {
    //confirm_string = 'are you sure to delete?';
    confirm_string = 'Are you sure want to delete?';
    var answer = confirm(confirm_string);
    return answer; // answer is a boolean
}
function get_reliase_msg(id) {
    confirm_string = 'Are you sure want to release DID?';
    var answer = confirm(confirm_string);
    return answer; // answer is a boolean
}
function get_alert_msg_restore(id) {
    confirm_string = 'Are you sure want to restore this database?';
    var answer = confirm(confirm_string);
    return answer; // answer is a boolean
}
// harshs changes for fix customer side record select issue
function clickchkbox(chkid) {

    var chk_flg = 0;
    $(".chkRefNos").change(function () {
        if ($(this).prop("checked") == false) {
            $('.checkall').prop("checked", false);
        }
        if ($(".chkRefNos:checked").length == $(".chkRefNos").length) {
            $('.checkall').prop("checked", true)
        }
    });
    $("#add_patterns_btn").removeAttr('disabled');
}
// harshs changes for fix customer side record select issue end here
function post_request_for_search(grid_id, destination, form_id) {

    if (destination == "") {
        destination = build_url("_search");
    }
    $.ajax({
        type: 'POST',
        url: destination,
        data: $('#' + form_id).serialize(),
        success: function (response) {
            //alert(response);
            $('#' + grid_id).flexOptions({
                newp: 1
            }).flexReload();
        }
    });
}
function clear_search_request(grid_id, destination) {
    if (destination == "") {
        destination = build_url("_clearsearchfilter");
    }
    $.ajax({
        type: 'POST',
        url: destination,
        success: function (response) {
            $('#' + grid_id).flexOptions({
                newp: 1
            }).flexReload();
            $('.selectpicker').selectpicker('refresh');
        }
    });
}
function build_url(append_value) {
    var url_string = "";
    var pathname = window.location.pathname;
    url = pathname.split("/");
    var path = url[1] + '/' + url[2];
    if (append_value == "_json") {
        var custom_url = base_url + path;
        url_string = custom_url.substring(0, document.URL.length - 1) + append_value;
    } else {
        url_string = document.URL.substring(0, document.URL.length - 1) + append_value;
    }
    return url_string;
}
function build_grid_reports(grid_id, destination, collumn_arr, buttons) {
    if (destination == "") {
        destination = build_url("_json");
    }
    $("#" + grid_id).flexigrid({
        url: destination,
        method: 'GET',
        dataType: 'json',
        colModel: build_collumns(collumn_arr),
        buttons: build_buttons(buttons),
        sortname: "id",
        sortorder: "desc",
        usepager: false,
        resizable: true,
        title: '',
        pagetext: page_gettext,
        outof: of_gettext,
        nomsg: no_records_gettext,
        procmsg: 'Processing, please wait ...',
        pagestat: 'Displaying {from} to {to}' + of_gettext + '{total}' + records_gettext,
        onSuccess: function (data) {
            $('a[rel*=facebox]').facebox({
                loadingImage: '/assets/images/loading.gif',
                closeImage: '/assets/images/closelabel.png'
            });
        }
        /*	  onError: function(){ 
              alert("Request failed");
          }*/
    });
    $("#" + grid_id).addClass("flex_grid_reports");
}
function build_grid(grid_id, destination, collumn_arr, buttons) {

    // alert(buttons);
    if (destination == "") {
        destination = build_url("_json");
    }
    var callstart = collumn_arr;
    var sort = '';
    var col_arr = String(callstart);
    if (col_arr.toLowerCase().indexOf("callstart") >= 0) {
        sort = 'callstart';
    } else if (col_arr.toLowerCase().indexOf("country_id") >= 0) {
        sort = 'country_id';
    }
    else if (col_arr.toLowerCase().indexOf("module_name") >= 0) {
        sort = 'module_name';
    }
    else {
        sort = 'id';
    }
    $("#" + grid_id).flexigrid({
        url: destination,
        method: 'GET',
        dataType: 'json',
        colModel: build_collumns(collumn_arr),
        buttons: build_buttons(buttons),
        usepager: true,
        resizable: true,
        title: '',
        sort: true,
        sortname: sort,
        sortorder: "desc",
        pagetext: page_gettext,
        outof: of_gettext,
        nomsg: no_records_gettext,
        procmsg: 'Processing, please wait ...',
        pagestat: '{from} - {to}' + of_gettext + '{total}' + records_gettext,
        onSuccess: function (data) {
            $('a[rel*=facebox]').facebox({
                loadingImage: '/assets/images/loading.gif',
                closeImage: '/assets/images/closelabel.png'
            });
        }
        /*        onError: function(data){ 
    var col_field = [];
    for (var key in data) {
    col_field.push(data[key]);
    }
            alert(col_field);
                alert("Request failed");
            }*/
    });
    $("#" + grid_id).addClass("flex_grid");
}

function build_collumns(collumn_arr) {
    var col_field = [];
    var collumn_property = new Array();
    var col_arr = "";
    var searchflg = true;
    col_arr = collumn_arr;
    for (var key in col_arr) {
        col_field.push(col_arr[key]);
    }
    var jsonObj = []; //declare object
    for (var i = 0; i < col_field.length; i++) {
        if (col_field[i] != "") {
            var col_str = col_field[i];
            if (col_str != 'null' && col_str != '') {
                collumn_property = col_str.toString().split(',');
                if (collumn_property[6] == 'false' && (collumn_property[0] == 'Action' || collumn_property[0] == '行動' || collumn_property[0] == 'action' || collumn_property[0] == 'Aktion' || collumn_property[0] == 'Δράση' || collumn_property[0] == 'Azione' || collumn_property[0] == 'アクション'|| collumn_property[0] == 'Açao'|| collumn_property[0] == 'Acțiune'|| collumn_property[0] == 'Действие' || collumn_property[0] == 'Acción')) {
                    continue;
                }
                // 	    alert("{display:"+collumn_property[0]+", name:"+collumn_property[0]+", width:"+collumn_property[1]+" , sortable: 'false', align: 'center'}");
                // sandip add else if condition for account disable sorting
                if (collumn_property[7] == 'false' || collumn_property[0] == 'Action' || collumn_property[0] == '行動' || collumn_property[0] == 'action' || collumn_property[0] == 'Aktion' || collumn_property[0] == 'Δράση' || collumn_property[0] == 'Azione' || collumn_property[0] == 'アクション'|| collumn_property[0] == 'Açao'|| collumn_property[0] == 'Acțiune'|| collumn_property[0] == 'Действие' || collumn_property[0] == 'Acción') {
                    searchflg = false;
                } else if (collumn_property[0] == 'Account' && collumn_property[7] == 'build_concat_string' && collumn_property[9] == 'false') {
                    searchflg = false;
                } else {
                    searchflg = true;
                }
                if (!collumn_property[8]) {
                    collumn_property[8] = "center";
                }
                //alert(collumn_property[0]);
                var custom_str = collumn_property[0].slice(0, 6);
                var custom_display = '';
                if (custom_str != "<input") {
                    custom_display = gettext_custom(collumn_property[0]);
                } else {
                    custom_display = collumn_property[0];
                }
                jsonObj.push({
                    display: custom_display,
                    name: collumn_property[0],
                    width: collumn_property[1],
                    align: collumn_property[8],
                    sortable: searchflg,
                    sortorder: "desc",
                    sortname: collumn_property[2]
                });
            }
        }
    }
    return jsonObj;
}

function build_buttons(buttons_arr) {
    var jsonObj = []; //declare object
    if (buttons_arr == "") {
        return jsonObj;
    }
    var btn_field = [];
    var button_property = new Array();

    var btn_arr = buttons_arr;
    for (var key in btn_arr) {
        if (btn_arr[key] != null)
            btn_field.push(btn_arr[key]);
    }

    for (var i = 0; i < btn_field.length; i++) {
        if (btn_field[i] != "") {
            var btn_str = btn_field[i];
            button_property = btn_str.toString().split(',');
            //ASTPP 3.0  
            custom_url = base_url + button_property[4];
            result = custom_url.replace(/.*?:\/\//g, "");
            result = result.replace("//", "/");
            var newURL = window.location.protocol + "//" + result;
            var layout = 'small';

            if (typeof button_property[6] != 'undefined' && button_property[6] != '') {
                layout = button_property[6];
            }
            var post_data = {};
            post_data['button_name'] = button_property[7]; //HP: Permission changes 25-Jan-2019
            post_data['current_url'] = window.location.href;
            $.ajax({
                type: 'POST',
                async: false,
                url: base_url + "login/customer_permission_list/",
                data: post_data,
                success: function (response) {
                    var response_trim = response.trim();
                    // alert(button_property[0]+'---'+response_trim);
                    if (response_trim == 0) {
                        if (button_property[5] == 'popup') {
                            jsonObj.push({
                                //name: gettext_custom(button_property[0]), 
                                name: button_property[0],
                                bclass: button_property[1],
                                iclass: button_property[2],
                                btn_url: newURL,
                                clayout: layout,
                                onpress: button_action_popup
                            });
                        } else {
                            jsonObj.push({
                                //name: gettext_custom(button_property[0]), 
                                name: button_property[0],
                                bclass: button_property[1],
                                iclass: button_property[2],
                                btn_url: button_property[4],
                                clayout: layout,
                                onpress: button_action
                            });
                        }
                    }
                }
            });
        }
    }
    return jsonObj;
}

function redirect_page(url) {
    if (url == "NULL") {
        $(document).trigger('close.facebox');
    } else {
        var custom_url = base_url + url;
        var result = custom_url.replace(/.*?:\/\//g, "");
        result = result.replace("//", "/");
        var newURL = window.location.protocol + "//" + result;
        window.location.href = csrf_url(newURL);
    }
}
///*ASTPP_invoice_changes_05_05_start*/
function delete_multiple(btn_url, flag) {
    var result = "";
    var idarr = [];
    $(".chkRefNos").each(function () {
        if (this.checked == true) {
            result += ",'" + $(this).val() + "'";
            idarr.push($(this).val());
        }
    });
    result = result.substr(1);

    if (result) {
        if (flag > 0) {
            confirm_string = 'Are you sure want to delete? This action will delete all other data which belongs to this account(s).';
        } else {
            confirm_string = 'Are you sure want to delete this selected records?';
        }
        var answer = confirm(confirm_string);
        if (answer) {
            $.ajax({
                type: "POST",
                cache: false,
                async: true,
                url: btn_url,
                data: "selected_ids=" + result,
                success: function (data) { //alert(data); 
                    var tmpdata = '';
                    if (data.trim() == 'SUBSCRIPTION') {
                        process_subscription('SUBSCRIPTION', idarr);
                        tmpdata = 1;
                    } else if (data.trim() == 'DIDs') {
                        process_DIDs('DIDs', idarr);
                        tmpdata = 1;
                    }
                    if (data == 1 || tmpdata == 1) {
                        $('.flex_grid').flexOptions({
                            newp: 1
                        }).flexReload();
                        $('input:checkbox').removeAttr('checked');
                        $("#toast-container_error").css("display", "block");
                        $(".toast-message").html("Selected records has been deleted.");
                        $('.toast-top-right').delay(5000).fadeOut();
                    } else {
                        alert("Problem to delete records");
                    }
                }
            });
        }
    } else {
        alert("Please select atleast one record to delete.");
    }
}
function process_subscription(type, idarr) {

    for (i = 0; i < idarr.length; i++) {

        $.ajax({
            type: "GET",
            cache: false,
            async: true,
            url: base_url + 'ProcessCharges/BillAccountCharges/' + type + "/" + idarr[i]
        })
    }
}
function process_DIDs(type, idarr) {
    for (i = 0; i < idarr.length; i++) {
        $.ajax({
            type: "GET",
            cache: false,
            async: true,
            url: base_url + 'ProcessCharges/BillAccountCharges/' + type + "/" + idarr[i]
        })
    }
}
function export_multiple(btn_url, flag) {
    var result = "";
    var idarr = [];
    $(".chkRefNos").each(function () {
        if (this.checked == true) {
            result += "_" + $(this).val() + "";
            idarr.push($(this).val());
        }
    });
    result = result.substr(1);
    if (result) {
            confirm_string = 'Are you sure want to export records';
        var answer = confirm(confirm_string);
	
        if (answer) {
		location.href=btn_url+'/'+result; 
        }
    } else {
        alert("Please select atleast one record to export.");
    }
}
//END
function delete_multiple_selected(btn_url) {
    var result = "";
    $(".chkRefNos").each(function () {
        if (this.checked == true) {
            result += ",'" + $(this).val() + "'";
        }
    });
    result = result.substr(1);
    if (result) {
        $.ajax({
            type: "POST",
            cache: false,
            async: true,
            url: btn_url,
            data: "selected_ids=" + result,
            success: function (response) {
                var data = jQuery.parseJSON(response);
                var str = 'Are you sure want to delete this selected records?';
                var answer = '';
                if (data.selected_ids) {
                    if (data.str) {
                        answer = confirm(data.str + str);
                    } else {
                        answer = confirm(str);
                    }
                    if (answer) {
                        var post_data = {};
                        post_data['selected_ids'] = data.selected_ids;
                        post_data['flag'] = 'true';
                        $.ajax({
                            type: "POST",
                            cache: false,
                            async: true,
                            url: btn_url,
                            data: post_data,
                            success: function (data) {
                                $('input:checkbox').removeAttr('checked');
                                if (data.trim() == 'SUBSCRIPTION') {

                                    process_subscription('SUBSCRIPTION', idarr);
                                    data = 1;
                                }
                                if (data.trim() == 'DIDs') {

                                    process_DIDs('DIDs', idarr);
                                    data = 1;
                                }
                                if (data == 1) {
                                    /*				    $('.flex_grid').flexOptions({
                                                        newp:1
                                                        }).flexReload(); 
                                    */
                                    $('.flex_grid').flexReload();
                                    $('input:checkbox').removeAttr('checked');
                                    $("#toast-container_error").css("display", "block");
                                    $(".toast-message").html("Selected records has been deleted.");
                                    $('.toast-top-right').delay(5000).fadeOut();
                                } else {
                                    alert("Problem to delete records");
                                }
                            }
                        });
                    }
                }
                else {
                    alert(data.str);
                }
            }
        });
    }
    else {
        alert("Please select atleast one record to delete.");
    }
}
function button_action(t) {
    var flag = '0';
    custom_url = base_url + t.btn_url;
    result = custom_url.replace(/.*?:\/\//g, "");
    result = result.replace("//", "/");
    var newURL = window.location.protocol + "//" + result;
    var str = newURL.split('/');
    for (var i = 0; i < str.length; i++) {
        if (str[i] == 'price_delete_multiple' || str[i] == 'trunk_delete_multiple') {
            flag = '1';
        }
        if (str[i] == 'customer_selected_delete' || str[i] == 'reseller_selected_delete') {
            flag = '2';
        }
	if(str[i] == 'languages_export'){
            flag='3';
        }
    }
    if (t.name == 'Refresh') {
        $('.flex_grid').flexReload();
    }
    else if (t.name == "DELETE" || t.name == "Delete" || t.name == "Effacer" || t.name == "Borrar") {
        if (flag == '1') {
            delete_multiple_selected(newURL);
        }
        else if (flag == '2') {
            delete_multiple(newURL, flag);
        }
        else {
            delete_multiple(newURL);
        }
    }else if ((t.name.toLowerCase() == 'export' || t.name.toLowerCase() == "экспорт" || t.name == "Exportar" || t.name == "Exportation" || t.name == "Exportar") && flag == '3') {
        export_multiple(newURL);
    } else {
        window.location = csrf_url(newURL);
    }

}
function button_action_popup(t, grid) {
    custom_url = base_url + t.btn_url;

    result = custom_url.replace(/.*?:\/\//g, "");
    result = result.replace("//", "/");
    var newURL = window.location.protocol + "//" + result;
    if (t.name == 'Refresh') {
        $('.flex_grid').flexReload();
    }
    else if (t.name == "DELETE" || t.name == "Delete" || t.name == "Effacer" || t.name == "Borrar") {
        delete_multiple(t.btn_url);
    } else {
        jQuery.facebox({
            ajax: newURL,
            clayout: t.Clayout
        });
    }
}

function submit_form(form_id) {
    $('#error_msg').fadeIn();
    var form = $('#' + form_id);
    $('input').removeClass('borderred');
    $('.tooltips').css('display', "none");
      // Jaimin -ASTPPCOM-765
         $('.overlay').show();
     //END
    $.ajax({
        type: 'POST',
        url: form.attr('action'),
        data: $('#' + form_id).serialize(),
        success: function (response) {
            var tmp = jQuery.parseJSON(response);
             // Jaimin -ASTPPCOM-765
                $('.overlay').hide();
             //END
            if (tmp.SUCCESS_ORDER) {
                $("#toast-container").css("display", "block");
                $(".toast-message").html(tmp.SUCCESS_ORDER);
                $('.toast-top-right').delay(5000).fadeOut();
                $(document).trigger('close.facebox');
                location.reload();
            }
            if (!tmp.SUCCESS) {
                //$(".error_div").css("display","block");
                var myObject = eval('(' + response + ')');
                for (i in myObject) {
                    var fieldname = i.replace("_error", "");
                    $("input[name='" + fieldname + "']").addClass("borderred");
                    $("#" + i + "_div").css("display", "block");
                    $("#" + i).html(gettext_custom(capitalizeFirstLetter(myObject[i])));
                    //                    $("#"+i).html(myObject[i]);
                }
            } else {
                $("#toast-container").css("display", "block");
                $(".toast-message").html(tmp.SUCCESS);
                $('.toast-top-right').delay(5000).fadeOut();
                $(document).trigger('close.facebox');
                $('.flex_grid').flexReload();
            }
            if (tmp.EXPORT_LINK) {
                location.reload(true);
            }
        }
    });
}
function display_astpp_message(validate_ERR, ERR_type) {
    if (ERR_type == "notification") {
        $("#toast-container_error").css("display", "block");
        $(".toast-message").html(validate_ERR);
        $('.toast-top-right').delay(5000).fadeOut();

    } else {
        $("#toast-container").css("display", "block");
        $(".toast-message").html(validate_ERR);
        $('.toast-top-right').delay(5000).fadeOut();
    }
}
function print_error(ERR_STR) {
    var myObject = eval('(' + ERR_STR + ')');
    for (i in myObject) {
        var fieldname = i.replace("_error", "");
        $("input[name='" + fieldname + "']").addClass("borderred");
        $("#" + i + "_div").css("display", "block");
        $("#" + i).html(gettext_custom(capitalizeFirstLetter(myObject[i])));
    }
}
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
/********
ASTPP 3.0
Batch delete
********/
function post_request_for_batch_delete(grid_id, destination, form_id) {
    var result = confirm("Are you sure delete " + destination.trim() + " search record?");
    if (result == true) {
        destination = build_url("_batch_delete");
        $.ajax({
            type: 'POST',
            url: destination,
            data: $('#' + form_id).serialize(),
            success: function (response) {
                $('.flex_grid').flexOptions({
                    newp: 1
                }).flexReload();
                $('#' + grid_id).flexOptions({
                    newp: 1
                }).flexReload();
            }
        });
    }
}

function get_lang(value) {
    // alert(value);
    $.ajax({
        type: 'GET',
        cache: false,
        url: base_url + "login/set_lang_global/" + value,
        success: function (response) {
            location.reload(true);
        },
    });
}
function gettext_custom(collumn_property) {

    var collumn = '';
    //var url = 'http://65.111.177.99:9999/accounts/customer_global_grid_list/';
    var url = base_url + "login/get_language_text/";
    //var url = "http://192.168.1.22:8073/login/get_language_text/";
    //alert(url);exit;
    $.ajax({
        url: url,
        type: 'post',
        async: false,
        data: { "display": collumn_property },
        cache: false,
        success: function (response) {
            collumn = response;
        }
    });
    return collumn;
}



/********************/
