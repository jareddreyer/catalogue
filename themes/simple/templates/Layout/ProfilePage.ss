<div class="content-container unit size3of4 lastUnit">
    <div class="content">
        <% loop $profile %>
                $errorType
        <% end_loop %>
        <% if $error %>
            <% loop $getIMDBMetadata %>
                <div class="alert alert-$errorType error" role="alert"><strong>$VideoTitle</strong> - $error</div>
            <% end_loop %>
        <% end_if %>
        <% if $profile %>
            <% loop $profile %>
            <div class="clear">
                <div class="videoPoster">
                    <% loop $Up.getIMDBMetadata %>
                        $VideoPoster.setWidth(250)
                    <% end_loop %>
                </div>
                <div class="videoDetailsContainer">
                    <h2 class="name">$VideoTitle (<% loop $Up.getIMDBMetadata %><span id="year">$Year</span><% end_loop %>)</h2>
                    <p class="videoDetails"><% loop $Up.getIMDBMetadata %>$Runtime<% end_loop %> - $Genre</p>
                    <% if $seasonLinks %><p><strong>Season(s):</strong> $seasonLinks</p><% end_if %>
                    <hr />
                    <p id="plot"><% loop $Up.getIMDBMetadata %>$Plot<% end_loop %></p>
                    <hr />
                    <ul class="videoDetails">
                        <li class="director">
                            <span id="director"><% loop $Up.getIMDBMetadata %><strong>Director(s):</strong> $Director<% end_loop %></span>
                        </li>
                        <li class="starring"><% loop Up.getIMDBMetadata %><strong>Starring:</strong> $Actors<% end_loop %></li>
                        <li class="status"><strong>Status:</strong> $Status</li>
                        <li class="source">
                            <strong>Source:</strong> $Source
                        </li>
                        <li class="quality">
                            <strong>Quality:</strong> $Quality
                        </li>
                        <li>Last updated $lastEditedAgo ago</li>
                        <% if $displayComments %><li class="profileComments"><strong>Comments:</strong>$displayComments</li><% end_if %>
                        <li>
                            <a href="mailto: $Email?subject=Can I get $VideoTitle off you?<eom>">Request a copy</a>
                        </li>
                        <li>
                            <a href="{$Up.MaintenanceFormPageLink}edit/$ID">[ edit ]</a>
                        </li>
                    </ul>
                </div>
            </div>
            <% end_loop %>
        <% else %>
        <p> Title does not exist in catalogue</p>
        <% end_if %>
        <div class="clear">
        <% if $relatedTitles %>
            <h3>Part of the following trilogy:</h3>
            <% loop $relatedTitles %>
                 <a href="{$Up.ProfileURL}title/$ID"><img src="$Poster.setHeight(80).Link" alt="" title="View more about $VideoTitle" style="width:100px; height: 150px"></a>
            <% end_loop %>
        <% end_if %>

        <% if $seeAlsoTitles %>
            <h4>See also:</h4>
            <% loop $seeAlsoTitles.Sort('VideoTitle', 'ASC') %>
                <a href="{$Up.ProfileURL}title/$ID"><img src="$Poster.setHeight(80).Link" alt="" title="View more about $VideoTitle" style="height:100px;width: 80px; margin-bottom: .3em" height="80"></a>
            <% end_loop %>
        <% end_if %>
        </div>
    </div>
</div>
