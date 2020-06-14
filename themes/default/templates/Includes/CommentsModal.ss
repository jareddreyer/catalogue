<div class="modal fade" id="CommentsModal" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
                <h4 class="modal-title">Comments</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <% if $Member.currentUserID %>
                    $commentForm
                <% else %>
                    You must be <a href="Security/login">logged in</a> to post comments
                <% end_if %>
            </div>
        </div>
    </div>
</div>
