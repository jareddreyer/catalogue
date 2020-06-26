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
                $('.no-comments').hide();
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

function getComments(url, extraParams=null, modal, currentPage, totalPages) {

    //get comments
    $.ajax({
        type: "Get",
        url: url,
        data: (extraParams) ? extraParams : '',
        contentType: 'application/json',
        dataType: 'json',
        beforeSend: function(jqXHR,settings) {
            modal.find('.modal-body').append('<div class="loader" style="width: 100%; text-align: center; padding: 10rem" role="status"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <span class="sr-only">Loading...</span></div>');
        },
        success: function (data) {
            modal.find('.loader').remove();
            if(!$.trim(data)){
                modal.find('.modal-body').append('<div class="comment center no-comments"><h2>No comments available</h2></div>');

            } else {
                $.each(data.Comments, function(index, element) {
                    let htmlData =
                        '<div class="comment__content">' + element.Author + ' ' + element.Comment + '</div>' +
                        '<div class="comment__date">' + element.Created + '</div>';
                    modal.find('.modal-body').append('<div class="comment">' + htmlData + '</div>');
                });

                if(data.CommentsCount.TotalPages > 1 && $('.comment').length < data.CommentsCount.TotalItems ) {
                    modal.find('.modal-body')
                        .append(
                            '<div class="load-more">' +
                            '<span class="load-more__button" data-currentpage="'+ data.CommentsCount.CurrentPage +'" data-totalpages="' + data.CommentsCount.TotalPages + '" data-totalitems="' + data.CommentsCount.TotalItems + '">'
                            + 'View more comments</span>' +
                            '<span class="load-more__count">' + totalPages + ' of ' + data.CommentsCount.TotalItems + '</span></div>');
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log("The request failed");
            modal.find('.loader').remove();
            modal.find('.modal-body').append('<div class="alert alert-warning">An error occurred: ' + errorThrown + '</div>');
        }
    });
};

$('#CommentsModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget); // Button that triggered the modal
    const url = button.data('commentsurl');
    const modal = $(this);
    modal.find('.modal-body').empty(); // clear modal out before ajaxing

    // set catalogue id to the form
    $('.catalogueID').val(button.data('catalogueid'));

    getComments(url,null, modal, 3, 3);

    // neat auto sizer for text area fields
    $('textarea').on('input', function() {
        $(this).outerHeight(38).outerHeight(this.scrollHeight);
    });

    $(this).off('click').on('click', '.load-more__button', function(e) {
        const currentPage = $(this).data('currentpage') + 3;
        const totalPages = $(this).data('totalpages') + 3;
        $('.load-more').hide();
        getComments(url, {comments: currentPage}, modal, currentPage, totalPages );
    });
});

$('#CatalogueModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget); // Button that triggered the modal
    const modal = $(this);
    modal.find('.modal-body').empty(); // clear modal out before ajaxing

    $.ajax({
        beforeSend: function(jqXHR,settings) {
           modal.find('.modal-body').append('<div style="width: 100%; text-align: center; padding: 10rem" role="status"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <span class="sr-only">Loading...</span></div>');
        },
        complete: function(jqXHR,textStatus) {

        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
            modal.find('.modal-body').append('<div class="alert alert-warning">An error occurred: ' + errorThrown + '</div>');
        },
        success: function(data, textStatus, jqXHR) {
            modal.find('.modal-body').html(data);
        },
        type: 'GET',
        url: button.data('profileurl')
    });

});
