<div class="content-container unit size3of4 lastUnit">
        <h1>$Title</h1>

        <% if $countTitles > 0 %>
            <p>$countTitles
        <% loop $CurrentMember %>
             Series are listed in $CurrentMember.FirstName $Surname's catalogue.</p>
        <% end_loop %>

        <div class="content">
            <div class="catalogue">

                </div>
                <ul class="imageList list">
                <% loop $television %>
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
                              <% if $Seasons %><strong>Season(s) available: </strong><span class="seasons small">$seasonLinks</span><br><% end_if%>
                              <% if $Status %><strong>Status: </strong><span class="status {$Status}">$Status</span><br><% end_if%>
                              <% if $Source %><strong>Source: </strong><span class="source">$Source</span><br><% end_if%>
                              <% if $Quality %><strong>Quality: </strong><span class="quality">$Quality</span><br><% end_if%>
                              <span class="small">Last updated $lastupdatedreadable ago by <a href="mailto: {$Email}?subject=Can I get {$Video_title} off you?<eom>">$FirstName $Surname</a></span>
                              <span class="hide keywords">$keywords</span>
                              $genres
                          </div>
                          <div class="list--media-maintenance">
                              <p>
                                  <span title="view Comments here" class="comments glyphicon glyphicon-comment" data-toggle="modal" data-comments="<% if $Comments %>$Comments<% else %>No Comments available<% end_if%>" data-target="#myModal"></span>
                                  <a href="{$Up.MaintenanceFormPageLink}edit/{$ID}"><span title="edit this title" class="glyphicon glyphicon-edit"></span></a>
                              </p>
                          </div>

                      </div>
                  </li>

                <% end_loop %>
                </ul>
                <hr>
                <div class="jplist-panel box panel-top">

                    <!-- pagination -->
                    <div data-control-animate-to-top="true" data-control-action="paging" data-control-name="paging" data-control-type="pagination" class="jplist-pagination"></div>

                </div>
                  <!-- no results found -->
                   <div class="jplist-no-results">
                      <p>No results found</p>
                   </div>

                </div>

            </div>


</div>

 <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Comments</h4>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>
<% else %>
<div class="catalogue">
    <div class="jplist-panel">
    <p>User does not have any tv series in their catalogue. Try another user?</p>

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
