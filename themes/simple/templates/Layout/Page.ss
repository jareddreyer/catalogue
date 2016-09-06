<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
        <h1>$Title</h1>
        <div class="content">
            $Content
            <h2>Recently Added</h2>
            <hr>
            <div class="grid--recent-holder">
                <ul class="imageList list">
                <% loop recentlyAddedTitles %>
                    <li class="list-item">
                        <div class="list-box">
                                <span class="poster"><a href="/video-profile/$ID"><img src="/assets/Uploads/$Poster" width="200"></a></span>
                                <br>
                                <span class="small">Last updated $lastupdatedreadable ago by <a href="mailto: {$Email}?subject=Can I get {$Video_title} off you?<eom>">$FirstName $Surname</a></span>
                            </div>
                    </li>
                <% end_loop %>
              </ul>
            </div>
        </div>
        $Form
        $PageComments
        
</div>