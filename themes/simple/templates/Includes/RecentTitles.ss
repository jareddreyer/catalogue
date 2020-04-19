<% if $recentTitles('added').count > 1 %>
    <h2>Recently Added</h2>
    <hr>
    <div id="added" class="row">
        <a id="scroll-left" class="scroll-arrow">&lt;</a>
        <div class="row__inner">
            <% loop $recentTitles('added') %>
                    <div class="tile">
                        <div class="tile__media">
                            <a href="{$Top.ProfileURL}title/$ID">
                                <img src="$Poster.setHeight(220).Link" alt="Browse {$VideoTitle} (Updated {$LastEdited.Ago})" class="loader">
                            </a>
                        </div>
                    </div>
            <% end_loop %>
        </div>
        <a id="scroll-right" class="scroll-arrow">&gt;</a>
    </div>
<% end_if %>
<% if $recentTitles('updated').count > 1 %>
    <h2>Recently Updated</h2>
    <hr>
    <div id="updated" class="row">
        <a id="scroll-left" class="scroll-arrow">&lt;</a>
        <div class="row__inner">
            <% loop $recentTitles('updated') %>
                    <div class="tile">
                        <div class="tile__media">
                            <a href="{$Top.ProfileURL}title/$ID">
                                <img src="$Poster.setHeight(220).Link" alt="Browse {$VideoTitle} (Updated {$LastEdited.Ago})" class="loader">
                            </a>
                        </div>
                    </div>
            <% end_loop %>
        </div>
        <a id="scroll-right" class="scroll-arrow">&gt;</a>
    </div>
<% end_if %>
