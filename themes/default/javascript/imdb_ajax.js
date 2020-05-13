//OMDBAPI key string www.omdbapi.com - get your own free key
// @todo: refactor to use base64 encryption and move this var into the template.
let apikeyString = 'a0f02af4';
let posterContainer = $('.loader');

$(function()
{
	($('#Form_Form_Type').val() == 'series') ? $('#Form_Form_Seasons_Holder').show() : $('#Form_Form_Seasons_Holder').hide(); //if tv/series then show, else hide seasons if film

	// main autocomplete function
	$("#Form_Form_Title").autocomplete({
				delay: 500,
				minLength: 3,
				source: function(request, response) {
					$.getJSON("http://www.omdbapi.com", {
						s: $('#Form_Form_Title').val(),
						apikey: apikeyString
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
					})
					.fail(function( data) {
						//couldn't connect to json request omdbapi
						console.log( "error, couldn't connect to omdbapi - " + data.responseText);

						$('#media-form').before('<div class="alert alert-danger" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Could not connect to omdbapi.com - '+ data.responseText + '</div>')
				    });

				},
				focus: function(event, ui) {
					// prevent autocomplete from updating the textbox
					event.preventDefault();
				},
				select: function(event, ui) {
					// prevent autocomplete from updating the textbox
					event.preventDefault();
					$('#Form_Form_IMDBID').val(ui.item.id); //add imdb ID to field
					$('#Form_Form_Year').val(ui.item.year); //add year of release to field
					const filename = ui.item.title.replace(/[^a-zA-Z0-9-_\.]/gi, ''); //clean up the title so its local filename safe
					(ui.item.poster != 'N/A') ?	getPosterThumb(ui.item.poster, ui.item.title, filename, ui.item.year, ui.item.id) : $('.poster').html('<img src="themes/simple/images/blank.png">'); //get the poster as  base64 curl request and display it
					imdblookup(ui.item.id); //get all metadata from imdb
				}
			});

		// control for source field
		$('#Form_Form_Type').on('change', function()
		{
			$('#Form_Form_Source').find('option:not(:first)').remove(); //remove all options except for placeholder option

			if($('#Form_Form_Type').val() == 'series')
			{
				$("#Form_Form_Seasons").tagit("removeAll");
				$('#Form_Form_Seasons_Holder').show();
				populateSelect(tvarr, '#Form_Form_Source');
			}

			if($('#Form_Form_Type').val() == 'movie')
			{
				$('#Form_Form_Seasons_Holder').hide();
				populateSelect(filmarr, '#Form_Form_Source');
			}
		});

		if($('#Form_Form_Keywords').val() != '')
		{
			$("#Form_Form_Trilogy").tagit({
				singleFieldDelimiter: " , ",
				allowSpaces: true,
				tagLimit: 1,
				availableTags: $('#Form_Form_Keywords').tagit('assignedTags')

			});
		}

		$('#Form_Form_Keywords').on('change', function()
		{
			 $("#Form_Form_Trilogy").tagit({
					singleFieldDelimiter: " , ",
					allowSpaces: true,
					tagLimit: 1,
					availableTags: $('#Form_Form_Keywords').tagit('assignedTags')
				});
		});

		populateComments();
});

function getPosterThumb (poster, title, filename, year, IMDBID)
{
	const posterlink = $("#Form_Form").data('posterlink');

	$.ajax({
		type: "GET",
		url: posterlink,

		data: {poster: poster, title: title, filename: filename, year: year, IMDBID: IMDBID},
		beforeSend: function() {
			posterContainer.show();
			console.log(filename);
		},
		success: function(data)
		{
			console.log('success');
			$('.poster').html(data);
		},
		error: function(){
			posterContainer.addClass('broken');
			console.log("The request failed");
		}
	});
}

function imdblookup(id)
{
	$.getJSON("http://www.omdbapi.com", {
				i: id,
				apikey: apikeyString
			 },
			 function(data)
			 {
			 	if (data != 'false')
    			{

    				$('#Form_Form_PosterID').val(posterContainer.data('posterid'));
				 	$('#Form_Form_Title').val(data.Title);

				 	//if tv hide unnessecary fields/values
				 	if(data.Type == 'series')
				 	{
				 		$('#Form_Form_Type').val('series'); $('#Form_Form_Seasons_Holder').show();

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
				 		$('#Form_Form_Type').val('movie');
				 		$('#Form_Form_Seasons_Holder').hide();
				 		$('#Form_Form_Source').find('option:not(:first)').remove(); //remove all options except for placeholder option
				 		populateSelect(filmarr, '#Form_Form_Source');
				 	}

					if(data.Type == 'game') { $('#Form_Form_Type').val(''); $('#Seasons').hide(); } // hide seasons if not tv

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
