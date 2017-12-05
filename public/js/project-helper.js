/*******************************
 * Main business logic
 ******************************/
function chooseUserAutomatically(identifier, isNew){
    console.log('chooseUserAutomatically');
    var data = {};
    if(typeof(isNew) != 'undefined' && isNew == true) {
        data.is_new = isNew;
    }
    data.priority = $('#priority').val();
    data.user_type = $('input[name=user_type]:checked').val();
    if(!data.user_type){
        alert('Please choose user type')
        return;
    }
    console.log('before request');
    $.ajax({
        type:'POST',
        dataType: 'json',
        url:'/system/chooseUserAutomatically/' + identifier,
        data: data,
        success: function(data){
            console.log(data);
            if(data.response) {
                if(data.result.success){
                    addMessage(data.result.message, 'success', false);
                    var user = data.result.user;
                    $('#assigned-user-id').val(user.id);
                    var label = '<label>Assigned user: </label>';
                    $('#result-block').html(label + ' ' + user.full_name + ' with effectivity coefficient: <strong>'+user.coefficient+'</strong><br>More detailed information about this calculations see <a target="_blank" href="/help">here</a>');
                    $('#result-block').show();
                }else{
                    addMessage(data.result.message, 'danger');
                }
            }else{
                addMessage(data.message, 'danger')
            }
        }
    });
}
/************************************/
/************************************/


/**********************************
 * Tasks logic
 *********************************/
function addComment(taskId){
    var data = {};
    data.comment_text = $('#comment-text').val();
    $.ajax({
        type:'POST',
        dataType: 'json',
        url:'/comments/add/'+taskId,
        data: data,
        success: function(data){
            console.log(data);
            if(data.response) {
                addMessage(data.message, 'success');
                $('#comment-text').val('');
            }else{
                addMessage(data.message, 'danger')
            }
            if(data.html){
                $(".comments-wrapper").append(data.html);
            }
            if($('.there-are-no-comments').length > 0){
                $('.there-are-no-comments').remove();
            }
        }
    });
}
function editComment(commentId){
    var commentText = $('#comment-text-' + commentId);
    var editButton = $('#edit-button-' + commentId);
    var commentTextArea = $('#comment-textarea-' + commentId);
    var cancelButton = $('#cancel-edit-button-' + commentId);
    var saveButton = $('#comment-save-button-' + commentId);
    commentText.hide();
    commentTextArea.show();
    cancelButton.show();
    editButton.hide();
    saveButton.show();
}
function cancelEditComment(commentId){
    var commentText = $('#comment-text-' + commentId);
    var editButton = $('#edit-button-' + commentId);
    var commentTextArea = $('#comment-textarea-' + commentId);
    var cancelButton = $('#cancel-edit-button-' + commentId);
    var saveButton = $('#comment-save-button-' + commentId);
    commentText.show();
    commentTextArea.hide();
    cancelButton.hide();
    editButton.show();
    saveButton.hide();
}
function editCommentPost(commentId){
    var data = {};
    data.comment_text = $('#comment-textarea-' + commentId).val();
    $.ajax({
        type:'POST',
        dataType: 'json',
        url:'/comments/edit/' + commentId,
        data: data,
        success: function(data){
            console.log(data);
            if(data.response) {
                addMessage(data.message, 'success');
                cancelEditComment(commentId);
            }else{
                addMessage(data.message, 'danger')
            }
            if(data.html){
                $("#comment-block-" + commentId).replaceWith(data.html);
            }
        }
    });
}



function addTimeLogPost(taskId){
    var data = {};
    data.task_id = taskId;
    var spent_time_hours = $('#spent_time_hours').val();
    var spent_time_minutes = $('#spent_time_minutes').val();

    if(!$('#time-log-form').valid()){
        alert('Not valid value for time');
    }

    console.log(spent_time_hours);
    console.log(spent_time_minutes);

    data.spent_time = (Number(spent_time_hours) * 60) + Number(spent_time_minutes);

    console.log(data.spent_time);

    if(!$.isNumeric(data.spent_time)){
        alert('Not valid value for time');
        return false;
    }
    $.ajax({
        type:'POST',
        dataType: 'json',
        url:'/tasks/addtimelog',
        data: data,
        success: function(data){
            console.log(data);
            if(data.response) {
                addMessage(data.message, 'success');
                closePopup();
            }else{
                addMessage(data.message, 'danger')
            }
            if(data.html){
                $("#time-logs-wrapper").append(data.html);
            }
            if(data.spent_time_html){
                $("#spent-time-wrapper").html(data.spent_time_html);
            }
        }
    });
}
function editTimeLog(timeLogId){
    var commentText = $('#time-log-time-' + timeLogId);
    var editButton = $('#edit-time-log-button-' + timeLogId);
    var commentTextArea = $('#time-log-form-edit-' + timeLogId);
    var cancelButton = $('#cancel-time-log-edit-button-' + timeLogId);
    var saveButton = $('#time-log-save-button-' + timeLogId);
    commentText.hide();
    commentTextArea.show();
    cancelButton.show();
    editButton.hide();
    saveButton.show();
}
function cancelEditTimeLog(timeLogId){
    var commentText = $('#time-log-time-' + timeLogId);
    var editButton = $('#edit-time-log-button-' + timeLogId);
    var commentTextArea = $('#time-log-form-edit-' + timeLogId);
    var cancelButton = $('#cancel-time-log-edit-button-' + timeLogId);
    var saveButton = $('#time-log-save-button-' + timeLogId);
    commentText.show();
    commentTextArea.hide();
    cancelButton.hide();
    editButton.show();
    saveButton.hide();
}
function editTimeLogPost(timeLogId){
    var data = {};

    if(!$('#spent_time_hours_edit_' + timeLogId).valid() || !$('#spent_time_minutes_edit_' + timeLogId)){
        alert('Not valid value for time');
    }

    var spent_time_hours = $('#spent_time_hours_edit_' + timeLogId).val();
    var spent_time_minutes = $('#spent_time_minutes_edit_' + timeLogId).val();

    data.spent_time = (Number(spent_time_hours) * 60) + Number(spent_time_minutes);
    if(!$.isNumeric(data.spent_time)){
        alert('Not valid value for time');
        return false;
    }

    data.time_log_id = timeLogId;
    $.ajax({
        type:'POST',
        dataType: 'json',
        url:'/tasks/edittimelog',
        data: data,
        success: function(data){
            console.log(data);
            if(data.response) {
                addMessage(data.message, 'success');
                cancelEditComment(timeLogId);
            }else{
                addMessage(data.message, 'danger')
            }
            if(data.html){
                $("#time-log-block-" + timeLogId).replaceWith(data.html);
            }
            if(data.spent_time_html){
                $("#spent-time-wrapper").html(data.spent_time_html);
            }
        }
    });
}


function reassign(){
    var data = {};
    $.ajax({
        type:'GET',
        dataType: 'json',
        url:'/tasks/reassign',
        data: data,
        success: function(data){
            console.log(data);
            if(data.response) {
//                    addMessage(data.message, 'success');
            }else{
                addMessage(data.message, 'danger')
            }
            if(data.html){
                $("#reassign-modal-popup-body").html(data.html);
            }
        }
    });
}
function reassignPost(taskId){
    var data = {};
    data.task_id = taskId;
    data.user_id = $('#assigned_user_id').val();
    $.ajax({
        type:'POST',
        dataType: 'json',
        url:'/tasks/reassign',
        data: data,
        success: function(data){
            console.log(data);
            if(data.response) {
                addMessage(data.message, 'success');
                if(data.user_name){
                    $('#assigned-user-value').html(data.user_name);
                }
                closePopup();
            }else{
                addMessage(data.message, 'danger')
            }
        }
    });
}
/**********************************************/
/**********************************************/





/*************************
 * General helper logic
 ************************/
document.addEventListener("DOMContentLoaded", function(event){

    $(function(){
        var appendthis =  ("<div class='modal-overlay js-modal-close'></div>");

        $('a[data-modal-id]').click(function(e) {
            e.preventDefault();
            $("body").append(appendthis);
            $(".modal-overlay").fadeTo(500, 0.7);
            //$(".js-modalbox").fadeIn(500);
            var modalBox = $(this).attr('data-modal-id');
            $('#'+modalBox).fadeIn($(this).data());
        });


        $(".js-modal-close, .modal-overlay").click(function() {
            closePopup();
        });

        $(window).resize(function() {
            $(".modal-box").css({
                top: (($(window).height()/2) - ($(".modal-box").outerHeight()/2)) + 'px',
                left: (($(window).width()/2) - ($(".modal-box").outerWidth()/2)) + 'px'
            });
        });

        $(window).resize();

    });
});
function closePopup(){
    $(".modal-box, .modal-overlay").fadeOut(500, function() {
        $(".modal-overlay").remove();
    });
}

function addMessage(message, type, scrollTo){
    var html = '<div class="alert alert-'+type+' fade in alert-dismissable" style="margin-top:18px;">';
    html += '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">x</a>';
    html += message;
    html += '</div>';

    $('#application-messages-wrapper').html('');
    $('#application-messages-wrapper').append(html);

    if(scrollTo != false) {
        $('html, body').animate({
            scrollTop: $("#application-messages-wrapper").offset().top
        }, 1000);
    }
}
/****************************************/
/****************************************/


/***********************************************/
/* Form ajax sending logic using jQuery plugin */
/***********************************************/
// prepare the form when the DOM is ready
$(document).ready(function() {
    var options = {
//            target:        '#output2',   // target element(s) to be updated with server response
        beforeSubmit:  showRequest,  // pre-submit callback
        success:       showResponse,  // post-submit callback
        dataType: 'json'
        // other available options:
        //url:       url         // override for form's 'action' attribute
        //type:      type        // 'get' or 'post', override for form's 'method' attribute
        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
        //clearForm: true        // clear all form fields after successful submit
        //resetForm: true        // reset the form after successful submit
        // $.ajax options can be used here too, for example:
        //timeout:   3000
    };
    // bind to the form's submit event
    $('#upload-form').submit(function() {
        $(this).ajaxSubmit(options);
        return false;
    });
});

// pre-submit callback
function showRequest(formData, jqForm, options) {
    var queryString = $.param(formData);
    console.log(queryString);
    return true;
}

// post-submit callback
function showResponse(responseText, statusText, xhr, $form)  {
console.log(responseText);
console.log(statusText);
    if(responseText.response) {
        addMessage(responseText.message, 'success', false);
        if(responseText.html){
            $(".attachments-items-wrapper").append(responseText.html);
        }
        closePopup();
    }else{
        addMessage(responseText.message, 'danger');
    }
}
/****************************************/
/****************************************/