<div class="content-container unit size3of4 lastUnit">
    <h1>$Title</h1>

    <% if $countTitles > 0 %>
        <p>$countTitles
    <% loop $CurrentMember %>
         movies are listed in $CurrentMember.FirstName $Surname's catalogue.</p>
    <% end_loop %>

    <div class="content">
        <div class="films">
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
                <button type="button" class="jplist-reset-btn" data-control-type="reset" data-control-name="reset" data-control-action="reset">
                    Reset <i class="fa fa-share"></i>
                </button>
                <div class="jplist-drop-down" data-control-type="filter-drop-down" data-control-name="category-filter" data-control-action="filter">
                    <ul>
                        <li><span data-path="default"> Filter by Status</span></li>
                        <li><span data-path=".Downloaded">Downloaded</span></li>
                        <li><span data-path=".Downloading">Downloading</span></li>
                        <li><span data-path=".NoTorrents">No Torrents</span></li>
                        <li><span data-path=".Wanted">Wanted</span></li>
                    </ul>
                </div>
                <div class="jplist-drop-down genre" data-control-type="filter-drop-down" data-control-name="genre-filter" data-control-action="filter">
                    <ul>
                        <li>
                            <span data-path="default"> Filter by Genre</span>
                        </li>
                        <% if getGenres %>$getGenres <% end_if %>
                    </ul>
                </div>

                <div class="text-filter-box">
                    <i class="fa fa-search jplist-icon"></i>

                    <!--[if lt IE 10]>
                    <div class="jplist-label">Filter by Title:</div>
                    <![endif]-->

                    <input data-path=".list--media-title" type="text" value="" placeholder="Filter by Title" data-control-type="textbox" data-control-name="title-filter" data-control-action="filter">
                </div>

                <div class="text-filter-box">
                    <i id="keywordSearch" class="fa fa-search jplist-icon"></i>

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
                            <li class="active"><span data-number="3"> 3 per page </span></li>
                            <li><span data-number="5"> 5 per page </span></li>
                            <li><span data-number="10"> 10 per page </span></li>
                            <li><span data-number="all"> View All </span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <ul class="imageList list">
                <% loop $movies %>
                <li class="list-item">
                    <div class="list-box">
                        <div class="list--media-poster">
                           <% loop $posters %>
                               $Me.setWidth(90)
                           <% end_loop %>
                        </div>
                        <div class="list--media-title">
                            <a href="{$Up.ProfileURL}title/{$ID}"><h2>$VideoTitle <% if $Year%>($Year)<% end_if%></h2></a>
                        </div>
                        <div class="list--media-metadata">
                            <% if $Status %><strong>Status: </strong><span class="status {$Status}">$Status</span>
                            <br>
                            <% end_if%>
                            <% if $Source %><strong>Source: </strong><span class="source">$Source</span>
                            <br>
                            <% end_if%>
                            <% if $Quality %><strong>Quality: </strong><span class="quality">$Quality</span>
                            <br>
                            <% end_if%> <span class="small">Last updated $lastupdatedreadable ago by <a href="mailto: {$Email}?subject=Can I get {$VideoTitle} off you?<eom>">$FirstName $Surname</a></span>
                            <span class="hide keywords">$keywords</span>
                             $genres
                        </div>
                        <div class="list--media-maintenance">
                            <p>
                                <span title="view Comments here" class="comments glyphicon glyphicon-comment" data-toggle="modal" data-comments="<% if $Comments %>$Comments<% else %>No Comments available<% end_if%>" data-target="#myModal"></span>
                                <a href="{$Top.MaintenanceFormPageLink}edit/{$ID}"><span title="edit this title" class="glyphicon glyphicon-edit"></span></a>
                            </p>

                        </div>

                    </div>
                </li>

                <% end_loop %>
            </ul>
            <hr>
            <div class="jplist-panel box panel-bottom">
                <!-- pagination -->
                <div data-control-animate-to-top="true" data-control-action="paging" data-control-name="paging" data-control-type="pagination" class="jplist-pagination"></div>
            </div>

            <!-- no results found -->
            <div class="jplist-no-results">
                <p>
                    No results found
                </p>
            </div>

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
    <div class="films">
        <div class="jplist-panel">
        <p>User does not have any films/series in their catalogue. Try another Catalogue?</p>

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

<script type="text/javascript">
$('document').ready(function(){
$('.films').jplist({
itemsBox: '.list'
,itemPath: '.list-item'
,panelPath: '.jplist-panel'
});

});

$('#myModal').on('show.bs.modal', function (event) {
var button = $(event.relatedTarget) // Button that triggered the modal
var comment = button.data('comments') // Extract info from data-* attributes

var modal = $(this)
modal.find('.modal-body').html(comment.replace(/[']+/g, '').split(",").join("<br>"))
})

</script>
