<div class="content-container unit size3of4 lastUnit">
        <div class="content">
            $Content
            <% if recentlyAddedTitles %>
                <h2>Recently Added</h2>
                <hr>
                   <div id="added" class="row">
                        <a id="scroll-left" class="scroll-arrow">&lt;</a>
                        <div class="row__inner">
                            <% loop recentlyAddedTitles %>
                                <div class="tile">
                                    <div class="tile__media">
                                      <a href="$profileLink/title/$ID"><img src="$path$Poster" class="loader"></a>
                                    </div>
                                </div>
                            <% end_loop %>
                        </div>
                        <a id="scroll-right" class="scroll-arrow">&gt;</a>
                </div>
            <% end_if %>
            <% if recentlyUpdatedTitles %>
                <h2>Recently Updated</h2>
                <hr>
                <div id="updated" class="row">
                    <a id="scroll-left" class="scroll-arrow">&lt;</a>
                    <div class="row__inner">
                            <% loop recentlyUpdatedTitles %>
                                <div class="tile">
                                    <div class="tile__media">
                                      <a href="$profileLink/title/$ID"><img src="$path$Poster" class="loader"></a>
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
