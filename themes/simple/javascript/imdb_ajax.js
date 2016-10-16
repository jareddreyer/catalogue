//arrays of select options
	var filmarr = [
		  {val : 'Bluray', text: 'BD/BRRip'},
		  {val : 'DVD', text: 'DVD-R'},
		  {val : 'screener', text: 'SCR/SCREENER/DVDSCR/DVDSCREENER/BDSCR'},
		  {val : 'cam', text: 'CAMRip/CAM/TS/TELESYNC'},
		  {val : 'vod', text: 'VODRip/VODR'},
		  {val : 'web', text: 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL'}
		];
		
	var tvarr = [
		  {val : 'Bluray', text: 'BD/BRRip'},
		  {val : 'DVD', text: 'DVD-R'},
		  {val : 'HDTV', text: 'HD TV'},
		  {val : 'SDTV', text: 'SD TV'},
		  {val : 'web', text: 'WEB-Rip/WEBRIP/WEB Rip/WEB-DL'}
		];
		
$(function()
{
	($('#Form_Form_Video_type').val() == 'series') ? $('#Seasons').show() : $('#Seasons').hide(); //if tv/series then show, else hide seasons if film
	
	// main autocomplete function
	$("#Form_Form_Video_title").autocomplete({
				delay: 500,
				minLength: 3,
				source: function(request, response) {
					$.getJSON("http://www.omdbapi.com", {
						s: $('#Form_Form_Video_title').val()
					},
					 function(data)
					 {
					 	if(data.Response == 'False')
						{
							var data = [{ value: "0", label: "Title not found, please try again" }];
							// data is an array of objects and must be transformed for autocomplete to use
							response(data);
						} else {
							var array = data.Error ? [] : $.map(data.Search, function(m) {
								return {
									label: m.Title + " (" + m.Year + ")" + " [" + m.Type + "]",
									id: m.imdbID,
									poster: m.Poster,
									title: m.Title,
									year: m.Year
								};
							});
							response(array);
						}
					});
					
				},
				focus: function(event, ui) {
					// prevent autocomplete from updating the textbox
					event.preventDefault();
				},
				select: function(event, ui) {
					// prevent autocomplete from updating the textbox
					event.preventDefault();
					$('#Form_Form_imdbID').val(ui.item.id); //add imdb ID to field
					$('#Form_Form_Year').val(ui.item.year); //add year of release to field
					var media = ui.item.title.replace(/[^a-zA-Z0-9-_\.]/gi, ''); //clean up the title so its local filename safe
					(ui.item.poster != 'N/A') ?	getPosterThumb(ui.item.poster, media) : $('.poster').html('<img src="themes/simple/images/blank.png">'); //get the poster as  base64 curl request and display it
					imdblookup(ui.item.id); //get all metadata from imdb
					
				}
			});
			
		// control for source field
		$('#Form_Form_Video_type').on('change', function()
		{
			$('#Form_Form_Source').find('option:not(:first)').remove(); //remove all options except for placeholder option
			
			if($('#Form_Form_Video_type').val() == 'series')
			{
				$("#Form_Form_Seasons").tagit("removeAll");
				$('#Seasons').show();
				populateSelect(tvarr, '#Form_Form_Source');
			}
			
			if($('#Form_Form_Video_type').val() == 'film')
			{
				$('#Seasons').hide();
				populateSelect(filmarr, '#Form_Form_Source');
			}
		});
		
		
		$('#Form_Form_keywords').on('change', function()
		{
			 $("#Form_Form_trilogy").tagit({
                    singleFieldDelimiter: " , ",
                    allowSpaces: true,
                    tagLimit: 1,
                    availableTags: $('#Form_Form_keywords').tagit('assignedTags')
                    
                });
			
		});
		
		populateComments();
			
});
function getPosterThumb (poster, media)
{
	$.ajax({
    type: "GET",
    url: "/poster/savePosterPreview",
    
    data: {poster: poster, title: media},
    success: function(data)
    {
    	$('.poster').html(data);
    },
    error: function(){
        console.log("The request failed");
    }
});
	
}

function imdblookup(id)
{
	$.getJSON("http://www.omdbapi.com", {			
				i: id
			 },
			 function(data)
			 {
			 	if (data != 'false')
    			{
    				var title = data.Title.replace(/[^a-zA-Z0-9-_\.]/gi, '');
    				$('#Form_Form_Poster').val(title + '.jpg');
				 	$('#Form_Form_Video_title').val(data.Title);
				 	
				 	//if tv hide unnessecary fields/values
				 	if(data.Type == 'series') 
				 	{ 
				 		$('#Form_Form_Video_type').val('series'); $('#Seasons').show();
				 		
				 		//check how many seasons IMDB returned and put value in seasons box
					 	var seasonNumber = data.totalSeasons;
					 	for (i = 1; i <= seasonNumber; i++)
					 	{
						    $('#Form_Form_Seasons').tagit('createTag', 'Season '+i);
						}
						
						$('#Form_Form_Source').find('option:not(:first)').remove(); //remove all options except for placeholder option
						populateSelect(tvarr, '#Form_Form_Source');
				 		 
				 	}
				 	
				 	if(data.Type == 'movie') 
				 	{ 
				 		$('#Form_Form_Video_type').val('film'); 
				 		$('#Seasons').hide();
				 		$('#Form_Form_Source').find('option:not(:first)').remove(); //remove all options except for placeholder option
				 		populateSelect(filmarr, '#Form_Form_Source');
				 	}
					
					if(data.Type == 'game') { $('#Form_Form_Video_type').val(''); $('#Seasons').hide(); } // hide seasons if not tv
								 	
				 	//tags
				 	$("#Form_Form_Genre").tagit("removeAll"); //get rids of last tags
				 	
				 	var tag = data.Genre.split(",");
					$.each( tag, function( key, single_tag ) {
						$('#Form_Form_Genre').tagit('createTag', single_tag);
					});
					
				}
			 });
}

function populateComments ()
{
	var date = new Date().toLocaleDateString();
	var time = new Date().toLocaleTimeString();
	var dateTime = date + " @  " + time;
	
	var user = $('.user').text();
	var originalComments = $('#Form_Form_Comments');
	var newComments = $('#Form_Form_CommentsEnter');
	
	
	$.fn.appendVal = function( TextToAppend ) {
		return $(this).val(
			$(this).val() + TextToAppend
		);
	};
	
	newComments.on('change', function()
	{
		originalComments.appendVal(",'"+user+ " ("+ dateTime +") - " + newComments.val() +"'" );
	});
	
}
function populateSelect (elements, selector)
{
	var sel = $(selector);
	$(elements).each(function()
	{
		 sel.append($("<option>").attr('value',this.val).text(this.text));
	});
}
