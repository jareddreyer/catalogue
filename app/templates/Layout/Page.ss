<!-- Page Content -->
<div class="container">
  <div class="row">
    <div class="col-lg-12 text-center">
      
<div class="content-container unit size3of4 lastUnit">       
        <div class="content">
            $Content
                <h2>Recently Added</h2>
                <hr>
                   <div id="added" class="row">
                        <a id="scroll-left" class="scroll-arrow">&lt;</a>
                        <div class="row__inner">
                            <% loop recentTitles('added') %>
                                <div class="tile">
                                    <div class="tile__media">
                                      <a href="$Up.ProfileURL/title/$ID"><img src="$SiteConfig.PostersWebpath$Poster" class="loader"></a>
                                    </div>                        
                                </div>
                            <% end_loop %>
                        </div>
                        <a id="scroll-right" class="scroll-arrow">&gt;</a>
                </div>
                <h2>Recently Updated</h2>
                <hr>
                <div id="updated" class="row">
                    <a id="scroll-left" class="scroll-arrow">&lt;</a>
                    <div class="row__inner">
                            <% loop recentTitles('updated') %>
                                <div class="tile">
                                    <div class="tile__media">
                                      <a href="$Up.ProfileURL/title/$ID"><img src="$SiteConfig.PostersWebpath$Poster" class="loader"></a>
                                    </div>                        
                                </div>
                            <% end_loop %>
                    </div>
                    <a id="scroll-right" class="scroll-arrow">&gt;</a>
                </div>
        $Form
        $PageComments
        
    </div>
</div>

      
    </div>
  </div>
</div>