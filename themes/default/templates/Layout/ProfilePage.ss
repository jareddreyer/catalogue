<div class="content-container size3of4">
    <div class="content">
        <% loop $Metadata %>
            <% if $error %>
                <div class="alert alert-$errorType error" role="alert"><strong>Title</strong> - $error</div>
            <% end_if %>
        <% end_loop %>

        <% if $profile %>
            <% loop $Metadata %>
                <div class="videoPoster">
                    $Poster.setWidth(250)
                </div>

                <div class="videoDetailsContainer">
                    <h2 class="name"><% loop $Up.profile %>$Title<% end_loop %> <span class="year">($Year)</span></h2>
                    <p class="videoDetails">$Rated | $Runtime | <% loop $Up.profile %>$genres<% end_loop %> | <span title="IMDB rating">$Rating <i class="fa fa-star rating" aria-hidden="true"></i></span></p>
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
                            <a href="{$Up.MaintenanceFormPageLink}edit/<% loop $Up.profile %>$ID<% end_loop %>">[ edit ]</a>
                        </li>
                    </ul>
                </div>
            <% end_loop %>
            <% if $relatedTitles && $seeAlsoTitles %>
                <div class="view-also__container clear">
                    <% if $relatedTitles %>
                        <div class="related-titles__container">
                            <h3>Part of the following trilogy:</h3>
                            <div class="related-titles__titles">
                                <% loop $relatedTitles %>
                                    <a href="{$Up.ProfileURL}title/$ID" title="Browse {$Title} (Updated {$LastEdited.Ago})">
                                        <div
                                                class="related-titles__poster"
                                                style="background-image:url('$Poster.setHeight(120).Link'), url('/{$ThemeDir}/images/blank.png')"
                                        ></div>
                                    </a>
                                <% end_loop %>
                            </div>
                        </div>
                    <% end_if %>

                    <% if $seeAlsoTitles %>
                    <div class="see-also__container">
                        <h4>See also:</h4>
                        <div class="see-also__titles">
                            <% loop $seeAlsoTitles.Sort('Title', 'ASC') %>
                                <a href="{$Top.ProfileURL}title/$ID" title="Browse {$Title} (Updated {$LastEdited.Ago})">
                                    <div
                                        class="see-also__poster"
                                        style="background-image:url('$Poster.setHeight(120).Link'), url('/{$ThemeDir}/images/blank.png')"
                                ></div>
                                </a>
                            <% end_loop %>
                        </div>
                    </div>
                    <% end_if %>
                </div>
            <% end_if %>
        <% else %>
            <p> Title does not exist in catalogue</p>
        <% end_if %>
    </div>
</div>
