$(function()
{
    $('.catalogue').jplist({
        itemsBox: '.list'
        ,itemPath: '.list-item'
        ,panelPath: '.jplist-panel'
    });

});

$('#myModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var comment = button.data('comments') // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    var modal = $(this)
    modal.find('.modal-body').html(comment.replace(/[']+/g, '').split(",").join("<br>"))
})
