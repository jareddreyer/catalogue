<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
        <h1>$Title</h1>
        $LastEdited
        <div class="content">
                <% loop getIMDBMetadata %><p class="message $errorType" id="Form_Form_error"><strong>$error</strong></p><% end_loop %>
                <% loop profile %>
                <div>
                    
                    <div class="videoPoster"><% loop Up.getIMDBMetadata %><img src="$VideoPoster" alt="" title="" /><% end_loop %></div>
                    <h2 class="name">$Video_title (<% loop Up.getIMDBMetadata %><span id="year">$Year</span><% end_loop %>)</h2>
                    <p class="videoDetails"><% loop Up.getIMDBMetadata %>$Runtime<% end_loop %> - $Genre</p>
					<div class="videoDetailsContainer">
						
						<p><% if $seasonLinks %><strong>Season(s):</strong> $seasonLinks</p><% end_if %>
						<hr />
						<p id="plot"><% loop Up.getIMDBMetadata %>$Plot<% end_loop %></p>
						<hr />
						<ul class="videoDetails">
							<li class="director">
								<span id="director"><% loop Up.getIMDBMetadata %><strong>Director(s):</strong> $Director<% end_loop %></span>
							</li>
							<li class="starring"><% loop Up.getIMDBMetadata %><strong>Starring:</strong> $Actors<% end_loop %></li>
							<li class="status"><strong>Status:</strong> $Status</li>
							<li class="source">
								<strong>Source:</strong> $Source
							</li>
							<li class="quality">
								<strong>Quality:</strong> $Quality resolution
							</li>
							<li>Last updated $lastupdatedreadable ago</li>
							<li><strong>Comments:</strong><p>$Comments</p>
							</li>
							<li>
								<a href="mailto: $Email?subject=Can I get $Video_title off you?<eom>">Request a copy</a>
							</li>
							<li>
								<a href="catalogue-maintenance/edit/$ID">[ edit ]</a>
							</li>
						</ul>
					</div>
                </div>
                <% end_loop %>

        </div>
</div>