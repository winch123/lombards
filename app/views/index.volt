<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
    <head>
        {% block head %}
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
            {{ meta.getTitle() }}
            {{ meta.getDescription() }}
            {{ meta.getKeywords() }}
            {% if custom_meta_tags is defined %}{{ custom_meta_tags }}{% endif %}
            <meta property='og:title' content="{{ meta.getTitleContent() }}" />
            <meta property='og:type' content="website" />
            <meta property='og:description' content="{{ meta.getDescriptionContent() }}" />

            {{ rel_alternate }}

            <link rel="icon" type="image/x-icon" href="/static/img/favicon.ico">
            <link rel="stylesheet" href="{{ static_url("static/css/bootstrap.min.css") }}">
            <link rel="stylesheet" href="{{ static_url("static/css/jquery.fancybox.css?v=2.1.5") }}" type="text/css" media="screen" />
            <link rel="stylesheet" href="{{ static_url("static/css/slick.css") }}" type="text/css" media="screen" />
            <link href="//fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">

            <link rel="stylesheet" href="{{ static_url("static/css/main.css") }}">
            <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
            <script src="{{ static_url("/static/js/jquery.min.js") }}"></script>
            <!-- Yandex.Metrika counter -->
            <script type="text/javascript">
                (function (d, w, c) {
                    (w[c] = w[c] || []).push(function () {
                        try {
                            w.yaCounter21293509 = new Ya.Metrika({id: 21293509,
                                webvisor: true,
                                clickmap: true,
                                trackLinks: true,
                                accurateTrackBounce: true});
                        } catch (e) {
                        }
                    });

                    var n = d.getElementsByTagName("script")[0],
                            s = d.createElement("script"),
                            f = function () {
                                n.parentNode.insertBefore(s, n);
                            };
                    s.type = "text/javascript";
                    s.async = true;
                    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

                    if (w.opera == "[object Opera]") {
                        d.addEventListener("DOMContentLoaded", f, false);
                    } else {
                        f();
                    }
                })(document, window, "yandex_metrika_callbacks");

                var reformalOptions = {
                    project_id: 353459,
                    tab_orientation: "left",
                    tab_indent: "50%",
                    tab_bg_color: "#F05A00",
                    tab_border_color: "#FFFFFF",
                    tab_image_url: "http://tab.reformal.ru/T9GC0LfRi9Cy0Ysg0Lgg0L%252FRgNC10LTQu9C%252B0LbQtdC90LjRjw==/FFFFFF/2a94cfe6511106e7a48d0af3904e3090/left/1/tab.png",
                    tab_border_width: 2
                };

                (function () {
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.async = true;
                    script.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'media.reformal.ru/widgets/v3/reformal.js';
                    document.getElementsByTagName('head')[0].appendChild(script);
                })();
            </script>

            {% if yandexMaps is defined and yandexMaps %}
                <script src="//api-maps.yandex.ru/2.1/?lang=ru-RU" type="text/javascript"></script>
            {% endif %}
        {% endblock %}
    </head>
    <body>
        <div id="navigation">
            {% block navigation %}
                {% include 'partials/navigation.volt' %}
            {% endblock %}
        </div>

        <div class="container background-white main-content{% if contentBlockCustomClass %} {{ contentBlockCustomClass }}{% endif %}">
            <div class="col-md-12 col-xs-12">
                {% block breadcrumbs %}
                    <ol class="breadcrumb row">
                        {{ crumbs.render() }}
                    </ol>
                {% endblock %}

                {% block searchForm %}
                    {% include 'partials/search_form.volt' %}
                {% endblock %}

                <div id="content">
                    {% block content %}{{ content() }}{% endblock %}
                </div>
            </div>
        </div>

        <div id="footer">
            {% block footer %}
                {% include 'partials/footer.volt' %}
            {% endblock %}
        </div>
    </body>
</html>
