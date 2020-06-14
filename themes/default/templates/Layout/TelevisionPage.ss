<div class="content-container unit size3of4 lastUnit">
    <h1><% loop $Member %>$FirstName's $Up.Title<% end_loop %></h1>

    <% if $getCountTitles('series') > 0 %>
        <div class="content">
            <div class="catalogue">
                <% include SearchHeader %>
                <div class="list-media__container">
                    <% loop $television %>
                       <% include CatalogueList ParentTop=$Top %>
                    <% end_loop %>
                </div>
                <!-- no results found -->
                <div class="jplist-no-results jplist-hidden">
                    <p>No series were found</p>
                </div>
            </div>
            <% include CommentsModal %>
            <% include CatalogueModal %>
        </div>

    <% else %>
        <div class="catalogue">
            <div class="jplist-panel">
                <p>User does not have any series in their catalogue. Try another user?</p>

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
