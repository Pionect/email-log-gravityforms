/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function ($) {

    $(".email_response").click(function () {
        var w = window.open('', 'newwin', 'width=650,height=500'),
            data = {
                action: 'show_smtp_response',
                email_id: $(this).data('email_id')
            };

        $.post(ajaxurl, data, function (response) {
            $(w.document.body).html(response);
        });
    });

});
