<% if $recentTitles('added').count > 0 %>
    <h2>Recently Added</h2>
    <div class="recently-updated row">
        <div class="recently-added--container row__inner">
            <% loop $recentTitles('added') %>
                    <div class="tile">
                        <div class="tile__media">
                            <a href="{$Top.ProfileURL}title/$ID">
                                <img src="$Poster.setHeight(220).Link" alt="Browse {$Title} (Updated {$LastEdited.Ago})" class="loader">
                            </a>
                        </div>
                    </div>
            <% end_loop %>
        </div>
    </div>
<% end_if %>

<% if $recentTitles('updated').count > 0 %>
    <h2>Recently Updated</h2>
    <div class="recently-updated row">
        <div class="recently-updated--container row__inner">
            <% loop $recentTitles('updated') %>
                    <div class="tile">
                        <div class="tile__media">
                            <a href="{$Top.ProfileURL}title/$ID">
                                <img src="$Poster.setHeight(350).Link" alt="Browse {$Title} (Updated {$LastEdited.Ago})" class="loader">
                            </a>
                        </div>
                    </div>
            <% end_loop %>
        </div>
    </div>
<% end_if %>
