<div class="content-container unit size3of4 lastUnit">
    <h1>$Title</h1>

    <% if $getCountTitles('movie') > 0 %>
    <p>$getCountTitles('movie')
        <% loop $Member %>
            movies are listed in $FirstName {$Surname}'s catalogue.</p>
        <% end_loop %>

        <div class="content">
            <div class="catalogue">
                <% include SearchHeader %>
                <div class="list-media__container">
                    <% loop $movies %>
                        <div class="list-media__item">
                            <div class="list-media__poster-wrapper">
                                <a href="{$Up.ProfileURL}title/{$ID}" class="list-media__title hvr-grow" title="Browse {$Title} (Updated {$LastEdited.Ago})">
                                    <span class="hidden">$Title</span>
                                    <div
                                            class="list-media__poster"
                                            style="background-image:url('$Poster.setHeight(250).Link'), url('/{$ThemeDir}/images/blank.png')"
                                    ></div>
                                </a>
                            </div>
                            <div class="list-media__metadata">
                                <span class="list-media__metadata-source badge">$Source</span>
                                <span class="list-media__metadata-status {$Status} badge badge-pill">$Status</span>
                                <span title="view Comments here" class="comments glyphicon glyphicon-comment" data-toggle="modal" data-comments="<% if $Comments %>$Comments<% else %>No Comments available<% end_if%>" data-target="#myModal"></span>
                                <a href="{$Up.MaintenanceFormPageLink}edit/{$ID}"><span title="edit this title" class="glyphicon glyphicon-edit"></span></a>
                                <p class="list-media__maintenance">Updated $LastEdited.Ago by <a href="mailto:{$Owner.Email}?subject=Can I get {$Title} off you?<eom>">$Owner.FirstName</a></p>
                                <p class="list-media__metadata-keywords hidden">$keywords</p>
                                <p class="list-media__metadata-genres hidden">$genres</p>
                            </div>
                        </div>
                    <% end_loop %>
                </div>
                <!-- no results found -->
                <div class="jplist-no-results jplist-hidden">
                    <p>No films were found</p>
                </div>
            </div>

            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
                                &times;
                            </button>
                            <h4 class="modal-title">Comments</h4>
                        </div>
                        <div class="modal-body">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <% else %>
        <div class="catalogue">
            <div class="jplist-panel">
                <p>User does not have any films in their catalogue. Try another user?</p>

                <div class="jplist-drop-down" data-control-type="filter-drop-down" data-control-name="profile-filter">
                    <ul>
                        <li><span data-path="default"> Browse by User</span></li>
                        <% loop $AllMembers %>
                            <li><a href="$link$ID">$FirstName $Surname </a></li>
                        <% end_loop %>
                    </ul>
                </div>
            </div>
        </div>
    <% end_if %>
</div>
