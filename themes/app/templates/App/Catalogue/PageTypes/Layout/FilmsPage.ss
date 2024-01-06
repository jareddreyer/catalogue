<div class="content-container unit size3of4 lastUnit">
    <h1><% loop $Member %>$FirstName's $Up.Title<% end_loop %></h1>

    <% if $getCountTitles('movie') > 0 %>
        <div class="content">
            <div class="catalogue">
                <% include SearchHeader %>
                <div class="list-media__container">
                    <% loop $movies %>
                        <% include CatalogueList ParentTop=$Top %>
                    <% end_loop %>
                </div>
                <!-- no results found -->
                <div class="jplist-no-results jplist-hidden">
                    <p>No films were found</p>
                </div>
            </div>
            <% include CommentsModal %>
            <% include CatalogueModal %>
        </div>

    <% else %>
        <div class="catalogue">
            <div class="jplist-panel">
                <p>User does not have any films in their catalogue. Try another user?</p>

                <div
                        class="dropdown pull-left"
                        data-control-type="boot-filter-drop-down"
                        data-control-name="profile-filter"
                        data-control-action="filter"
                >
                    <button
                            class="btn btn-default dropdown-toggle"
                            type="button"
                            data-toggle="dropdown"
                            id="dropdown-menu-profile"
                            aria-expanded="true"
                    >
                        <span data-type="selected-text">Browse by user</span>
                        <span class="caret"></span>

                    </button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdown-menu-profile">
                        <% loop $AllMembers %>
                            <li role="presentation"><a tabindex="-1" href="$link$ID">$FirstName $Surname </a></li>
                        <% end_loop %>
                    </ul>
                </div>
            </div>
        </div>
    <% end_if %>
</div>
