<!-- panel -->
<div class="jplist-panel catalogue">

    <!-- title filter -->
    <div class="text-filter-box">
        <!--[if lt IE 10]>
        <div class="jplist-label">Filter by Title:</div>
        <![endif]-->
        <input
            type="text"
            value=""
            class="jplist-no-right-border textfilter"
            placeholder="Filter by Title"
            data-button="#title-search-button"
            data-path=".list-media__title"
            data-control-type="textbox"
            data-control-name="title-filter"
            data-control-action="filter"
            data-typing-start="typingStart"
        >
        <i class="fa fa-times-circle jplist-clear" data-type="clear"></i>
        <button
                type="button"
                id="title-search-button">
            <i class="fa fa-search"></i>
        </button>
    </div>

    <!-- Users catalogue navigation filter -->
    <div
        class="jplist-drop-down"
        data-control-type="filter-drop-down"
        data-control-name="profile-filter"
    >
        <ul>
            <li><span data-path="default"> Browse by User</span></li>
            <% loop $AllMembers %>
                <li><a href="$link$ID">$FirstName $Surname </a></li>
            <% end_loop %>
        </ul>
    </div>

    <!-- Media status filter DropDown Control -->

    <button
        type="button"
        data-control-type="button-filter"
        data-path=".Downloaded"
        data-control-action="filter"
        data-control-name="downloaded-button"
    >
        <i class="fa fa-caret-right"></i>Downloaded
    </button>
    <button
        type="button"
        data-control-type="button-filter"
        data-path=".Downloading"
        data-control-action="filter"
        data-control-name="downloading-button"
    >
        <i class="fa fa-caret-right"></i>Downloading
    </button>
    <button
        type="button"
        data-control-type="button-filter"
        data-path=".NoTorrents"
        data-control-action="filter"
        data-control-name="unavailable-button"
    >
        <i class="fa fa-caret-right"></i>No Torrents
    </button>
    <button
        type="button"
        data-control-type="button-filter"
        data-path=".Wanted"
        data-control-action="filter"
        data-control-name="wanted-button"
    >
        <i class="fa fa-caret-right"></i>Wanted
    </button>


    <!-- genres filters -->
    <div
        class="jplist-checkbox-dropdown"
        data-control-type="checkbox-dropdown"
        data-control-action="filter"
        data-no-selected-text="Filter by genres"
        data-one-item-text="Filtered by {selected}"
        data-many-items-text="{num} filters selected"
        data-control-name="list-media__metadata-genres"
    >
        <ul>
            <% loop $getMetadataFilters($ClassName,'Genre') %><li class="jplist-group-item">$filters</li><% end_loop %>
        </ul>
    </div>

    <!-- keywords filters -->
    <% if $getMetadataFilters($ClassName,'Keywords') %>
        <div
            class="jplist-checkbox-dropdown"
            data-control-type="checkbox-dropdown"
            data-control-action="filter"
            data-no-selected-text="Filter by keywords"
            data-logic="and"
            data-one-item-text="Filtered by {selected}"
            data-many-items-text="{num} filters selected"
            data-control-name="list-media__metadata-keywords"
        >
            <ul>
                <% loop $getMetadataFilters($ClassName,'Keywords') %><li>$filters</li><% end_loop %>
            </ul>
        </div>
    <% end_if %>

    <!-- pagination per page filter -->
    <div
        class="jplist-drop-down"
        data-control-animate-to-top="true"
        data-control-action="paging"
        data-control-name="paging"
        data-control-type="items-per-page-drop-down"
        data-default="false"
    >
        <div class="jplist-dd-panel">3 per page</div>
        <ul>
            <li><span data-number="10"> 10 per page </span></li>
            <li><span data-number="20"> 20 per page </span></li>
            <li><span data-number="20"> 30 per page </span></li>
            <li><span data-number="all"> View All </span></li>
        </ul>
    </div>

    <!-- reset button -->
    <button type="button" data-control-type="reset" data-control-name="reset" data-control-action="reset">
        Reset <i class="fa fa-share"></i>
    </button>

    <!-- pagination -->
    <div
        class="jplist-pagination"
        data-control-animate-to-top="true"
        data-control-action="paging"
        data-control-name="paging"
        data-control-type="pagination"
        data-range="10"
    ></div>

</div>
