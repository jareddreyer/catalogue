<!-- panel -->
<div class="jplist-panel">
    <div class="jplist-drop-down" data-control-type="filter-drop-down" data-control-name="profile-filter">
        <ul>
            <li><span data-path="default"> Browse by User</span></li>
            <% loop $AllMembers %>
                <li><a href="$link$ID">$FirstName $Surname </a></li>
            <% end_loop %>
        </ul>
    </div>

    <!-- Filter DropDown Control -->
    <div class="jplist-drop-down" data-control-type="filter-drop-down" data-control-name="category-filter" data-control-action="filter">
        <ul>
            <li><span data-path="default">Filter by Status</span></li>
            <li><span data-path=".Downloaded">Downloaded</span></li>
            <li><span data-path=".Downloading">Downloading</span></li>
            <li><span data-path=".NoTorrents">No Torrents</span></li>
            <li><span data-path=".Wanted">Wanted</span></li>
        </ul>
    </div>
    <div class="jplist-drop-down genre" data-control-type="filter-drop-down" data-control-name="genre-filter" data-control-action="filter">
        <ul>
            <li><span data-path="default">Filter by Genre</span></li>
            <% if getGenres %>$getGenres <% end_if %>
        </ul>
    </div>
    <button type="button" class="jplist-reset-btn" data-control-type="reset" data-control-name="reset" data-control-action="reset">
        Reset <i class="fa fa-share"></i>
    </button>
    <div class="text-filter-box">

        <!--[if lt IE 10]>
        <div class="jplist-label">Filter by Title:</div>
        <![endif]-->

        <input data-path=".list-media__title" type="text" value="" placeholder="Filter by Title" data-control-type="textbox" data-control-name="title-filter" data-control-action="filter">
        <!-- clear textbox button -->
        <i class="fa fa-times-circle jplist-clear" data-type="clear"></i>
    </div>

    <div class="text-filter-box">
                <!--[if lt IE 10]>
        <div class="jplist-label">Filter by Keywords:</div>
        <![endif]-->

        <input class="keywordsText" data-button="#keywordSearch" data-path=".keywords" type="text" value="" placeholder="Filter by Keywords" data-control-type="textbox" data-control-name="keywords-filter" data-control-action="filter">
    </div>
    <div>

        <!-- pagination -->
        <div data-control-animate-to-top="true" data-control-action="paging" data-control-name="paging" data-control-type="pagination" class="jplist-pagination"></div>

        <div data-control-animate-to-top="true" data-control-action="paging" data-control-name="paging" data-control-type="items-per-page-drop-down" class="jplist-drop-down">
            <div class="jplist-dd-panel">3 per page</div>
            <ul>
                <li class="active"><span data-number="10"> 10 per page </span></li>
                <li><span data-number="20"> 20 per page </span></li>
                <li><span data-number="20"> 30 per page </span></li>
                <li><span data-number="all"> View All </span></li>
            </ul>
        </div>
    </div>
</div>
