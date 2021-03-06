<% if $recentTitles('added').count > 0 %>
    <h2>Recently Added</h2>
    <div id="added" class="row">
        <a id="scroll-left" class="scroll-arrow">&lt;</a>
        <div class="row__inner">
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
        <a id="scroll-right" class="scroll-arrow">&gt;</a>
    </div>
<% end_if %>
<% if $recentTitles('updated').count > 0 %>
    <h2>Recently Updated</h2>
    <div id="updated" class="row">
        <a id="scroll-left" class="scroll-arrow">&lt;</a>
        <div class="row__inner">
            <% loop $recentTitles('updated') %>
                    <div class="tile">
                        <div class="tile__media">
                            <a href="{$Top.ProfileURL}title/$ID">
                                <img src="$Poster.setHeight(220).Link" alt="Browse {$Title} (Updated {$LastEdited.Ago})" class="loader">
                            </a>
                        </div>
                    </div>
            <% end_loop %>
        </div>
        <a id="scroll-right" class="scroll-arrow">&gt;</a>
    </div>
<% end_if %>
