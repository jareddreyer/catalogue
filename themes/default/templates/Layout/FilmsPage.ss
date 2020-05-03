<div class="content-container unit size3of4 lastUnit">
    <h1>$Title</h1>

    <% if $countTitles > 0 %>
        <p>$countTitles
    <% loop $Member %>
         movies are listed in $FirstName {$Surname}'s catalogue.</p>
    <% end_loop %>

    <div class="content">
        <div class="catalogue">
            <% include SearchHeader %>
            <div class="list-media__container">
                <% loop $films %>
                    <div class="list-media__item">
                        <div class="list-media__poster-wrapper">
                            <a href="{$Up.ProfileURL}title/{$ID}" class="list-media__title">

                                <img class="list-media__poster"
                                     <% if $Poster %>src="$Poster.setHeight(250).Link"
                                     <% else %> src="{$ThemeDir}/images/blank.png"<% end_if %>
                                     alt="Browse {$Title} (Updated {$LastEdited.Ago})"
                                >
                            </a>
                        </div>
                        <div class="list-media__metadata-wrapper">
                            <div class="list-media__metadata">
                                <span class="list-media__metadata-source badge">$Source</span>
                                <span class="list-media__metadata-status {$Status} badge badge-pill">$Status</span>
                                <span title="view Comments here" class="comments glyphicon glyphicon-comment" data-toggle="modal" data-comments="<% if $Comments %>$Comments<% else %>No Comments available<% end_if%>" data-target="#myModal"></span>
                                <a href="{$Up.MaintenanceFormPageLink}edit/{$ID}"><span title="edit this title" class="glyphicon glyphicon-edit"></span></a>
                                <span class="small">Last updated $LastEdited.Ago by <a href="mailto: {$Email}?subject=Can I get {$Title} off you?<eom>">$Owner.FirstName</a></span>
                                <span class="hide keywords">$keywords</span>
                                $genres
                            </div>

                        </div>
                    </div>
                <% end_loop %>
            </div>
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
