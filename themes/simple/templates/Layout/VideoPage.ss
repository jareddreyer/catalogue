<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
		<h1>$Title</h1>
		<div class="content">
			<table cellspacing="0" cellpadding="0" id="movies" class="videos">
                <thead class="ui-widget-header">
                    <tr>
                        <th class="ui-state-default" style="width: 35%">Title</th>
                        <th class="ui-state-default" style="width: 5%">Status</th>
                        <th class="ui-state-default" style="width: 5%">Source</th>
                        <th class="ui-state-default" style="width: 5%">Quality</th>
                        <th class="ui-state-default" style="width: 10%">Owner</th>
                        <th class="ui-state-default" style="width: 10%">Wanted By</th>
                        <th class="ui-state-default" style="width: 25%">Comments</th>
                        <th class="ui-state-default" style="width: 5%"></th>
                    </tr>
                </thead>
<% loop movies %>
                <tbody>
                    <tr>
                        <td class="ui-widget-content">
                        <a href="/video-profile/{$ID}">$Video_title</a> <span class="small">(last updated $lastupdatedreadable ago)</span>
                        <!-- <input type="text" readonly="readonly" id="readOnlyTagsSeasons" value="$Seasons" /> -->
                        
                    </td>
                    <td class="ui-widget-content">$Status</td>
                    <td class="ui-widget-content">$Source</td>
                    <td class="quality ui-widget-content">$Quality</td>
                    <td class="ui-widget-content"><a href="mailto: {$Email}?subject=Can I get {$Video_title} off you?<eom>">$FirstName $Surname</a></td>
                    <td class="ui-widget-content">$Wanted_by</td>
                    <td class="ui-widget-content">$Comments</td>
                    <td class="ui-widget-content"><a href="catalogue-maintenance/edit/{$ID}">[ edit ]</a></td>
                    </tr>
                </tbody>
<% end_loop %> 
			</table>
		</div>
</div>

<script type="text/javascript">
$('#readOnlyTagsSeasons, #readOnlyTagsGenre').tagit({
    readOnly: true
});

$().ready(function(){ 
 $(".videos tr").hover(
     function()
     {
      $(this).children("td").addClass("ui-state-hover");
     },
     function()
     {
      $(this).children("td").removeClass("ui-state-hover");
     }
    );
 $(".videos tr").click(function(){
   
   $(this).children("td").toggleClass("ui-state-highlight");
  });
 
});
</script>
