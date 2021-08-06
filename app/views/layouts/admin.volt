<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        {{ meta.getTitle() }}
        <link rel="icon" type="image/x-icon" href="/static/img/favicon.ico">
        <link rel="stylesheet" href="/static/css/bootstrap.min.css">
        <link rel="stylesheet" href="/static/css/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
        <link rel="stylesheet" type="text/css" href="/static/css/datatables.min.css"/>
        <link rel="stylesheet" href="/static/css/main.css">
        <link rel="stylesheet" href="/static/css/admin.css">
        <script src="/static/js/jquery.min.js"></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="/static/js/bootstrap.min.js"></script>
        <script src="//cdn.ckeditor.com/4.5.8/standard/ckeditor.js"></script>
        <script src="/static/js/jquery.fancybox.pack.js?v=2.1.5"></script>
        <script type="text/javascript" src="/static/js/datatables.min.js"></script>
        <script src="/static/js/admin.js?v=1"></script>
    </head>
    <body>
        <nav class="navbar navbar-static-top navbar-inverse margin-zero">
            <div class="container padding-zero">
                <div class="navbar-header">
                    <a class="navbar-brand color-white" href="/admin/">Polombardam : admin</a>
                </div>

                {% include "layouts/main_menu_admin.volt" %}
            </div>
        </nav>
        <div class="container background-white main-content">
            <ol class="breadcrumb border-round-bottom">
                {{ crumbs.render() }}
            </ol>

            {{ content() }}
        </div>

        <div class="container background-gray">
            <div class="row" style="margin: 8px -15px;"></div>
        </div>
    </body>
</html>
