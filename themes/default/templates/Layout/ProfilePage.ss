<div class="content-container size3of4">
    <div class="content">
        <% loop $IMDBMetadata %>
            <% if $error %>
                <div class="alert alert-$errorType error" role="alert"><strong>Title</strong> - $error</div>
            <% end_if %>
        <% end_loop %>
        <% if $profile %>
            <% loop $IMDBMetadata %>
                <div class="videoPoster">
                    $Poster.setWidth(250)
                </div>

                <div class="videoDetailsContainer">
                    <h2 class="name"><% loop $Up.profile %>$Title<% end_loop %> <span class="year">($Year)</span></h2>
                    <p class="videoDetails">$Rated | $Runtime | <% loop $Up.profile %>$Genre<% end_loop %> | <span title="IMDB rating">$Rating <i class="fa fa-star rating" aria-hidden="true"></i></span></p>
                    <p>$Plot</p>
                    <hr>
                    <ul class="videoDetails">
                        <li class="director">
                            <strong>Director(s):</strong> $Director
                        </li>
                        <li class="starring"><strong>Stars:</strong> $Actors</li>

                        <% loop $Up.profile %>
                            <% if $Status %>
                                <li class="status"><strong>Status:</strong> $Status</li>
                            <% end_if %>
                            <% if $Source %>
                               <li class="source"><strong>Source:</strong> $Source</li>
                            <% end_if %>
                            <% if $Quality %>
                                <li class="quality"><strong>Quality:</strong> $Quality</li>
                            <% end_if %>
                            <li>Last updated $LastEdited.Ago</li>
                        <% end_loop %>
                        <li>
                            <a href="mailto: $Email?subject=Can I get $Title off you?<eom>">Request a copy</a>
                        </li>
                        <li>
                            <a href="{$MaintenanceFormPageLink}edit/<% loop $Up.profile %>$ID<% end_loop %>">[ edit ]</a>
                        </li>
                    </ul>
                </div>
            <% end_loop %>
            <div class="clear">
                <% if $relatedTitles %>
                    <h3>Part of the following trilogy:</h3>
                    <% loop $relatedTitles %>
                        <a href="{$Up.ProfileURL}title/$ID"><img src="$Poster.setHeight(80).Link" alt="" title="View more about $Title" style="width:100px; height: 150px"></a>
                    <% end_loop %>
                <% end_if %>

                <% if $seeAlsoTitles %>
                    <h4>See also:</h4>
                    <% loop $seeAlsoTitles.Sort('Title', 'ASC') %>
                        <a href="{$Up.ProfileURL}title/$ID"><img src="$Poster.setHeight(80).Link" alt="" title="View more about $Title" style="height:100px;width: 80px; margin-bottom: .3em" height="80"></a>
                    <% end_loop %>
                <% end_if %>
            </div>
        <% else %>
            <p> Title does not exist in catalogue</p>
        <% end_if %>
    </div>
</div>
