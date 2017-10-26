function addMessage(message, type){
    var html = '<div class="alert alert-'+type+' fade in alert-dismissable" style="margin-top:18px;">';
    html += '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">x</a>';
    html += message;
    html += '</div>';

    $('#application-messages-wrapper').append(html);
}