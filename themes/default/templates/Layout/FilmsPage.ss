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
                                <span title="view comments here" class="comments fa fa-comments-o"
                                      data-toggle="modal"
                                      data-target="#myModal"
                                      data-commentsurl="{$Up.Link}comments/{$ID}"
                                >
                                </span>
                                <a href="{$Up.MaintenanceFormPageLink}edit/{$ID}">
                                    <span title="edit this title" class="fa fa-pencil-square"></span>
                                </a>
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
            <% include ModalPartial %>
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
