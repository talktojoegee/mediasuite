'use strict';

function textboxOnFocus( key ) {
    $("#notes_message").attr("style", "display:none;");
    $('#'+key).show();	
}

function textboxOnBlur(key ) {
    $("#"+key).attr("style", "display:none;");
}

function setFocus( key ) {
	$("#"+key).focus();
}

function testDatabaseConnection() {    
	
	$('.loading_img').show();
	$('#notes_message').attr("style", "display:none;");
	buttonDisable();
	
	var database_host = $("#database_host").val();
	var database_name = $("#database_name").val();
	var database_username = $("#database_username").val();
	var database_password = $("#database_password").val();
	
	$.ajax({
		url: "ajax/handler.ajax.php",
		global: false,
		type: "POST",
		data: ({db_host:database_host,
			    db_name:database_name,
				db_username:database_username,
				db_password:database_password,
				action : "sap_test_db_connection"
		}),
		dataType: "html",
		async:false,
		error: function(html){
			$('.loading_img').hide();
            $("#notes_message").html('AJAX: cannot connect to the server or server response error! Please try again later.');
		},
		success: function(html){
			var obj = jQuery.parseJSON(html);
			if(obj.status == "1"){
				if(obj.db_connection_status == "1"){
					$("#notes_message").html("<h4 class='success'>Success</h4><p>DB Version: "+obj.db_version+"</p><p>A connection was successfully established with the server.</p>");	
				}else{
					$("#notes_message").html("<h4>Error</h4><p>"+obj.db_error+"</p>");	
				}
			}else if (obj.hasOwnProperty("db_pass")){
				$("#notes_message").html("<h4>Error</h4><span class='msg_error'>"+obj.db_pass+"</span>");                
			}else{
                $("#notes_message").html("<span class='msg_error'>Wrong parameters passed or connection error!</span>");
			}
		}
	});
	$('.loading_img').hide();
	$('#notes_message').fadeIn();
	buttonEnable();
}

function buttonDisable(){	
	$("#button_test").attr("style", "cursor:default;");	
}

function buttonEnable(){
	$("#button_test").attr("style", "cursor:pointer;");
}