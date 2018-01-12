<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
    <article>
        <h1>$Title</h1>
        <div class="content">
            <div id="media-form">
                <div class="poster"><span class="loader"></span></div>
                $Form
            </div>
            
    <% loop CurrentMember %>
    <span class="hide user">$CurrentMember.FirstName $Surname</span>
    <% end_loop %>
                
        </div>
    </article>
</div>
<script src="themes/simple/javascript/imdb_ajax.js"></script>