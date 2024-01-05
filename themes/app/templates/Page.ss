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

		<% require css('//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.min.css') %>
		<link rel="shortcut icon" href="{$resourceURL('themes/app/images/favicon.ico')}" />
		<link rel="prefetch" as="image" href="{$resourceURL('themes/app/images/blank.png')}">

	</head>

	<body class="$ClassName<% if not $Menu(2) %> no-sidebar<% end_if %>" <% if $i18nScriptDirection %>dir="$i18nScriptDirection"<% end_if %>>

		<img class="hidden" src="{$resourceURL('themes/app/images/blank.png')}">
		<% include Header %>
		<div class="main" role="main">
			<div class="inner typography line">
				$Layout
			</div>
		</div>
		<% include Footer %>

	</body>
</html>
