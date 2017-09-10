<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
        <h1>$Title</h1>
        <div class="content">
            <% if recentlyAddedTitles %>
            <h2>Recently Added</h2>
            <hr>
               <div id="added" class="row">
                    <a id="scroll-left" class="scroll-arrow">&lt;</a>
                    <div class="row__inner">
                        <% loop recentlyAddedTitles %>
                            <div class="tile">
                                <div class="tile__media">
                                  <a href="/video-profile/$ID"><img src="/assets/Uploads/$Poster" style="width:150px;height: 220px"></a>
                                </div>                        
                            </div>
                        <% end_loop %>
                    </div>
                    <a id="scroll-right" class="scroll-arrow">&gt;</a>
            </div>
            <% end_if %>
            <% if recentlyAddedTitles %>
            <h2>Recently Updated</h2>
            <hr>
                <div id="updated" class="row">
                    <a id="scroll-left" class="scroll-arrow">&lt;</a>
                    <div class="row__inner">
                            <% loop recentlyUpdatedTitles %>
                                <div class="tile">
                                    <div class="tile__media">
                                      <a href="/video-profile/$ID"><img src="/assets/Uploads/$Poster" style="width:150px;height: 220px"></a>
                                    </div>                        
                                </div>
                            <% end_loop %>
                    </div>
                    <a id="scroll-right" class="scroll-arrow">&gt;</a>
                </div>
            <% end_if %>
        $Form
        $PageComments
        
    </div>
</div>
