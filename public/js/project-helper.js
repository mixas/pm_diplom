function addMessage(message, type, scrollTo){
    var html = '<div class="alert alert-'+type+' fade in alert-dismissable" style="margin-top:18px;">';
    html += '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">x</a>';
    html += message;
    html += '</div>';

    $('#application-messages-wrapper').append(html);

    if(scrollTo != false) {
        $('html, body').animate({
            scrollTop: $("#application-messages-wrapper").offset().top
        }, 1000);
    }
}



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
                    $('#result-block').html(label + ' ' + user.full_name);
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