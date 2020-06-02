$('Document').ready(function(){

    $('.catalogue').jplist({
        itemsBox: '.list-media__container',
        itemPath: '.list-media__item',
        panelPath: '.jplist-panel'
    });

    $(document).on('submit', '.commentForm' , function(e)
    {
        const $form = $(this);
        let formData = $form.serialize();
        let formAction = $form.prop('action');
        let formMethod = $form.prop('method');
        let encType = $form.prop('enctype');

        $.ajax({
            beforeSend: function(jqXHR,settings) {
                if ($form.prop('isSending')) {
                    return false;
                }
                $form.prop('isSending',true);
            },
            complete: function(jqXHR,textStatus) {
                $form.prop('isSending',false);
            },
            contentType: encType,
            data: formData,
            dataType: 'json',
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
                $('#myModal').find('.modal-footer').append('<div class="alert alert-warning">An error occurred: ' + errorThrown + '</div>');
            },
            success: function(data, textStatus, jqXHR) {

                $.each(data, function(index, element) {
                    let htmlData =
                        '<div class="comment">' +
                            '<div class="comment__content">' + element.Author + ' ' + element.Comment + '</div>' +
                            '<div class="comment__date">' + element.Created + '</div>' +
                        '</div>';
                    $(htmlData).appendTo('.modal-body');

                });
                $form.trigger('reset');
            },
            type: formMethod,
            url: formAction
        });
        e.preventDefault();
    });

});

$('#myModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget); // Button that triggered the modal
    const url = button.data('commentsurl');
    const modal = $(this);
    modal.find('.modal-body').empty(); // clear modal out before ajaxing

    // set catalogue id to the form
    $('.catalogueID').val(button.data('catalogueid'));

    //get comments
    $.ajax({
        type: "Get",
        url: url,
        dataType: 'json',
        success: function (data) {
            if(!$.trim(data)){
                modal.find('.modal-body').append('<div class="comment center"><h2>No comments available</h2></div>');

            } else {
                $.each(data, function(index, element) {
                    let htmlData =
                        '<div class="comment__content">' + element.Author + ' ' + element.Comment + '</div>' +
                        '<div class="comment__date">' + element.Created + '</div>';
                    modal.find('.modal-body').append('<div class="comment">' + htmlData + '</div>');
                });
            }
        },
        error: function(){
            console.log("The request failed");
        }
    });
});
