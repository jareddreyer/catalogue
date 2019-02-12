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


	<% require css('themes/simple/css/bootstrap.min.css') %>
	<% require css('themes/simple/css/profile.css') %>
	<% require css('themes/simple/css/jquery.tagit.css') %>
	<% require css('themes/simple/css/jquery-ui-overrides.css') %>
	<% require css('themes/simple/css/font-awesome.min.css') %>

	<% require css('http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.min.css') %>
	<link rel="shortcut icon" href="$ThemeDir/images/favicon.ico" />

</head>

<body class="$ClassName<% if not $Menu(2) %> no-sidebar<% end_if %>" <% if $i18nScriptDirection %>dir="$i18nScriptDirection"<% end_if %>>
<% include Header %>
<div class="main" role="main">
	<div class="inner typography line">
		$Layout
	</div>
</div>
<% include Footer %>

<% require javascript("themes/simple/javascript/jquery-1.12.4.min.js") %>
<% require javascript("themes/simple/javascript/jquery-ui-1.10.4.custom.min.js") %>
<% require javascript("themes/simple/javascript/bootstrap.min.js") %>
<% require javascript("themes/simple/javascript/tag-it.min.js") %>

<% require javascript("themes/simple/javascript/jplist.core.min.js") %>
<% require javascript("themes/simple/javascript/jplist.pagination-bundle.min.js") %>
<% require javascript("themes/simple/javascript/jplist.filter-dropdown-bundle.min.js") %>
<% require javascript("themes/simple/javascript/jplist.textbox-filter.min.js") %>
<% require javascript("themes/simple/javascript/jplist.history-bundle.min.js") %>


</body>
</html>
