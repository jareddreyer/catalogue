<!DOCTYPE html>
<!--[if !IE]><!-->
<html lang="$ContentLocale">
<!--<![endif]-->
<head>
	<% base_tag %>
	<title><% if $MetaTitle %>$MetaTitle<% else %>$Title<% end_if %> &raquo; $SiteConfig.Title</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	$MetaTags(false)
	<% require themedCSS('reset') %>
	<% require themedCSS('typography') %>
	<% require themedCSS('form') %>
	<% require themedCSS('layout') %>
	<% require css('themes/simple/css/jquery.tagit.css') %>
	<% require css('themes/simple/css/jquery-ui-overrides.css') %>
	<% require css('themes/simple/css/profile.css') %>
	<% require css('http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css') %>
	<link rel="shortcut icon" href="$ThemeDir/images/favicon.ico" />
	<% require javascript("framework/thirdparty/jquery/jquery.min.js") %>
	
	
</head>
<body class="$ClassName<% if not $Menu(2) %> no-sidebar<% end_if %>" <% if $i18nScriptDirection %>dir="$i18nScriptDirection"<% end_if %>>
<% include Header %>
<div class="main" role="main">
	<div class="inner typography line">
		$Layout
	</div>
</div>
<% include Footer %>

<script type="text/javascript">
$("#Form_Form_Seasons").tagit({
    singleFieldDelimiter: " | ",
    availableTags: ["Season 1", "Season 2", "Season 3", "Season 4", "Season 5", "Season 6", "Season 7", "Season 8", "Season 9" , "Season 10", "Season 11", "Season 12", "Season 13", "Season 14", "Season 15", "Season 16"]
});

$("#Form_Form_Genre").tagit({
    singleFieldDelimiter: " | ",
    availableTags: ["Comedy", "Drama", "Horror", "Science Fiction", "Comic/Super Heroes", "Action", "Thriller", "Crime", "Documentary" , "Family", "Animated", "Romance", "Adventure", "War", "Sitcom"]
});
</script>

<% require javascript("themes/simple/javascript/jquery-ui-1.10.4.custom.min.js") %>
<% require javascript("themes/simple/javascript/tag-it.min.js") %>

</body>
</html>
