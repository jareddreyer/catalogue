<div class="content-container size3of4">
    <div class="content">
        <% loop $Metadata %>
            <% if $error %>
                <div class="alert alert-$errorType error" role="alert"><strong>Title</strong> - $error</div>
            <% end_if %>
        <% end_loop %>

        <% if $profile %>
            <% loop $profile %>
                <div class="video-details__container">
                    <div class="video-details__poster-wrapper">
                        <div class="video-details__poster">
                            $Poster.setWidth(300)
                        </div>
                        <div class="video-details__media-metadata">
                            <% if $Status %>
                                <span class="badge" title="The title availability is $Status">$Status</span>
                            <% end_if %>
                            <% if $Source %>
                                <span class="badge" title="Source of media">$Source</span>
                            <% end_if %>
                            <% if $Quality %>
                                <span class="badge" title="Quality of download:">$Quality</span>
                            <% end_if %>
                            <p class="video-details__metadata-updated">Updated $LastEdited.Ago by
                                <a href="mailto:{$Owner.Email}?subject=Can I get {$Title} off you?<eom>">$Owner.FirstName</a>
                            </p>
                        </div>
                    </div>

                    <div class="video-details__metadata">
                        <h1 class="video-details__title">$Title <span class="year">($Year)</span></h1>
                        <% loop $Up.Metadata %>
                            <p class="video-details__ratings">
                                <span class="video-details__ratings-classification">$Rated</span> |
                                $Runtime |
                                <span title="IMDB rating">$Rating <i class="fa fa-star rating" aria-hidden="true"></i></span>
                            </p>
                            <div class="video-details__plot">
                                $Plot
                            </div>
                            <div class="video-details__credits">
                                <strong>Director(s):</strong> $Director<br>
                                <strong>Stars:</strong> $Actors<br>
                                <% if $Top.seasonLinks %><strong>Seasons: </strong>$Top.seasonLinks<% end_if %>
                            </div>
                        <% end_loop %>
                        <div class="video-details__keywords">
                            <% if $genres != 0 %>$genres <% end_if %> <% if $keywords != 0 %>$keywords <% end_if %>
                        </div>
                        <div class="video-details__utilities">
                            <% if $Owner.ID != $CurrentMember.ID %>
                                <a href="mailto:$Owner.Email?subject=Can I get $Title off you?<eom>" title="Request a copy of {$Title}"><i class="fa fa-plus-circle"></i></a>
                            <% end_if %>
                            <a href="{$Up.MaintenanceFormPageLink}edit/$ID" title="Edit {$Title}"><i class="fa fa-pencil-square-o"></i></a>
                            <a href="http://www.imdb.com/title/{$IMDBID}" title="View this on imdb.com" target="_blank"><i class="fa fa-external-link"></i></a>
                        </div>

                        <div class="tab-wrap">
                            <% if $Up.relatedTitles %>
                                <input type="radio" id="tab1" name="tabGroup1" class="tab" checked>
                                <label for="tab1">Collection</label>
                            <% end_if %>

                            <% if $Up.seeAlsoTitles %>
                                <input type="radio" id="tab2" name="tabGroup1" class="tab" <% if not $Up.relatedTitles %>checked<% end_if %>>
                                <label for="tab2">Related</label>
                            <% end_if %>

                            <% if $Up.trailers %>
                                <input type="radio" id="tab3" name="tabGroup1" class="tab" <% if not $Up.relatedTitles && not $Up.seeAlsoTitles %>checked<% end_if %>>
                                <label for="tab3">Trailers</label>
                            <% end_if %>

                            <% if $Up.relatedTitles %>
                                <div class="tab__content">
                                    <div class="related-titles__container">
                                        <h2>Part of the $Trilogy collection...</h2>
                                        <div class="related-titles__titles">
                                            <% loop $Up.relatedTitles %>
                                                <a href="{$Up.ProfileURL}title/$ID" title="Browse {$Title} (Updated {$LastEdited.Ago})">
                                                    <div
                                                            class="related-titles__poster"
                                                            style="background-image:url('$Poster.setHeight(120).Link'), url('/{$ThemeDir}/images/blank.png')"
                                                    ></div>
                                                </a>
                                            <% end_loop %>
                                        </div>
                                    </div>
                                </div>
                            <% end_if %>

                            <% if $Up.seeAlsoTitles %>
                                <div class="tab__content">
                                    <div class="see-also__container">
                                        <h3>You may also like...</h3>
                                        <div class="see-also__titles">
                                            <% loop $Up.seeAlsoTitles %>
                                                <a href="{$Top.ProfileURL}title/$ID" title="Browse {$Title} (Updated {$LastEdited.Ago})">
                                                    <div
                                                            class="see-also__poster"
                                                            style="background-image:url('$Poster.setHeight(120).Link'), url('/{$ThemeDir}/images/blank.png')"
                                                    ></div>
                                                </a>
                                            <% end_loop %>
                                        </div>
                                    </div>
                                </div>
                            <% end_if %>

                            <% if $Up.trailers %>
                                <div class="tab__content">
                                    <h3>Youtube video links</h3>
                                    <ul class="video-details__trailers">
                                        <% loop $Up.trailers %>
                                            <li>
                                                <a class="video-details__trailer" href="//www.youtube.com/watch?v={$key}" target="_blank">
                                                    <img class="video-details__trailer-thumb" src="//img.youtube.com/vi/{$key}/0.jpg" alt="View $name" width="150px" height="150px">
                                                    <span class="video-details__trailer-play"><i class="fa fa-youtube-play fa-5x"></i></span>
                                                </a>
                                            </li>
                                        <% end_loop %>
                                    </ul>
                                </div>
                            <% end_if %>

                        </div>
                    </div>
                </div>
            <% end_loop%>

        <% else %>
            <p> Title does not exist in catalogue</p>
        <% end_if %>
    </div>
</div>
