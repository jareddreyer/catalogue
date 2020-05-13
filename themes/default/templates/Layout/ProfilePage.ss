<div class="content-container size3of4">
    <div class="content">
        <% loop $Metadata %>
            <% if $error %>
                <div class="alert alert-$errorType error" role="alert"><strong>Title</strong> - $error</div>
            <% end_if %>
        <% end_loop %>

        <% if $profile %>
            <% loop $profile %>
                <div class="videoPoster">
                    $Poster.setWidth(250)
                </div>

                <div class="video-details__container">
                    <h2 class="video-details__title">$Title <span class="year">($Year)</span></h2>
                    <p class="video-details__ratings">
                        <% loop $Up.Metadata %>$Rated<% end_loop%> |
                        <% loop $Up.Metadata %>$Runtime<% end_loop%> |
                        $genres |
                        <% loop $Up.Metadata %><span title="IMDB rating">$Rating <i class="fa fa-star rating" aria-hidden="true"></i></span><% end_loop%>
                    </p>

                    <% loop $Up.Metadata %>
                        <p>$Plot</p>

                        <ul class="video-details__credits">
                            <li class="director">
                                <strong>Director(s):</strong> $Director
                            </li>
                            <li class="starring"><strong>Stars:</strong> $Actors</li>
                            <% if $Top.seasonLinks %><li><strong>Seasons: </strong>$Top.seasonLinks</li><% end_if %>
                        </ul>
                    <% end_loop %>
                    <div class="video-details__utilities">
                        <a href="mailto: $Email?subject=Can I get $Title off you?<eom>" title="Request a copy of {$Title}"><i class="fa fa-plus-circle"></i></a>
                        <a href="{$Up.MaintenanceFormPageLink}edit/$ID" title="Edit {$Title}"><i class="fa fa-pencil-square-o"></i></a>
                    </div>
                    <div class="video-details__metadata">
                        <p class="video-details__metadata-updated">Updated $LastEdited.Ago by <a href="mailto:{$Owner.Email}?subject=Can I get {$Title} off you?<eom>">$Owner.FirstName</a></p>
                        <% if $Status %>
                            <span class="badge" title="The title availability is $Status">$Status</span>
                        <% end_if %>
                        <% if $Source %>
                            <span class="badge" title="Source of media">$Source</span>
                        <% end_if %>
                        <% if $Quality %>
                            <span class="badge" title="Quality of download:">$Quality</span>
                        <% end_if %>
                    </div>
                </div>
            <% end_loop%>
            <% if $relatedTitles || $seeAlsoTitles %>
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
                            <% loop $seeAlsoTitles %>
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
