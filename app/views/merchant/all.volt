{% extends "partials/city_tabs.volt" %}

{% block tabs_nav %}
    <li><a href="/city/{{ currentCity.name_translit }}">Товары из ломбардов {{ cityTitle }}</a></li>
    <li class="active"><a href="#">Все ломбарды {{ cityTitle }}</a></li>
{% endblock %}

{% block tab_content %}
    {% include "partials/page_bar.volt" %}
    <div class="page-header no-border">
        {% if currentTag %}
            <h1>{{ currentTag.getTitle(currentCity) }}</h1>
            <p>{{ currentTag.getText(currentCity) }}</p>
        {% else %}
            <h1>{{ title }}</h1>
            <p>{{ titleSub }}</p>
        {% endif %}

        <h4 class="block_desktop"><a href="/{{ currentCity.name_translit }}/add-merchant/step-2" title="Добавить ломбард в список">Добавить ломбард в список</a></h4>
        <h4 class="block_mobile"><a href="/city/{{ currentCity.name_translit }}">Товары из ломбардов {{ cityTitle }}</a></h4>
    </div>

    <div class="row">
        {% if merchantTags is defined and merchantTags|length %}
            <div class="col-md-3 col-sm-6 col-xs-12">
                <h4>Категории</h4>
                <div class="list-group">
                    <a href="{% if currentTag %}/{{currentCity.name_translit}}/spisok_lombardov#content{% else %}#{% endif %}" class="list-group-item {% if not currentTag %}active{% endif %}">
                        <span class="badge">{{ merchantTagsAllCount }}</span>
                        Все ломбарды
                    </a>

                    {% for merchantTag in merchantTags %}
                        <a href="/{{ currentCity.name_translit }}/tag/{{ merchantTag.id }}#content" class="list-group-item {% if currentTag and currentTag.id == merchantTag.id %}active{% endif %}">
                            <span class="badge">{{ merchantTag.count_merchants }}</span>
                            {{ merchantTag.name }}
                        </a>
                    {% endfor %}
                </div>
            </div>
        {% endif %}

        <div class="{% if merchantTags is defined and merchantTags|length %}col-md-9 col-sm-6{% endif %} col-xs-12">
            <div class="row">
                <div class="col-xs-12" style="margin-bottom: 20px;">
                    <h4>На карте</h4>
                    <div id="map" style="height:350px"></div>
                </div>

                <div class="col-xs-12 lombards-list merchant-big-preview">
                    <div class="row all_pawnshops_of_city">
                        {% for item in page.getItems() %}
                            <div class="{% if merchantTags is defined and merchantTags|length %}col-lg-3{% else %}col-lg-2{% endif %} col-md-3 col-xs-6 merchant-card">
                                {{ partial('partials/merchant_card', ['item': item]) }}
                            </div>
                        {% endfor %}

                        {% if not page.getItems() %}
                            <div class="col-md-12">
                                <div class="alert alert-warning" role="alert">
                                    Нет ломбардов, <a href="/" class="alert-link">вернуться на главную</a>.
                                </div>
                            </div>
                        {% endif %}
                    </div>
                </div>
            </div>

            {{ page_bar(page, pageUrl) }}

            <h4 class="block_mobile"><a href="/{{ currentCity.name_translit }}/add-merchant/step-2" title="Добавить ломбард в список">Добавить ломбард в список</a></h4>
        </div>
    </div>

    <script type="text/javascript">
        ymaps.ready(initMap);

        function initMap() {
            var myMap = new ymaps.Map('map', {
                center: {% if currentCity.map is defined %}'{{currentCity.map}}'.split(' ').reverse(){% else %}[55.76, 37.64]{% endif %},
                zoom: 10,
                controls: ['zoomControl', 'typeSelector', 'fullscreenControl', 'routeEditor']
            });

            var om = new ymaps.ObjectManager({
                clusterize: true
            });

            var geoObjectsData = {
                "type": "FeatureCollection",
                "features": []
            };

            var merchants = {{merchants_json}};

            for (var index in merchants) {
                var merchant = merchants[index];

                if (merchant.map && merchant.map.length > 0) {
                    geoObjectsData.features.push({
                        "type": "Feature",
                        "id": merchant.id,
                        "geometry": {
                            "type": "Point",
                            "coordinates": merchant.map.split(' ').reverse()
                        },
                        "properties": {
                            clusterCaption: merchant.name,
                            balloonContentHeader: merchant.name,
                            balloonContentBody:
                                    '<img src="' + merchant.image_preview + '" alt="' + merchant.name + '" style="max-height: 75px; margin-bottom: 10px;">' +
                                    '<div>' + merchant.address + '</div>' +
                                    '<div>т. ' + merchant.phone + '</div>' +
                                    '<div style="margin-top: 10px;"><a href="' + merchant.url + '">Перейти на страницу ломбарда</a></div>'
                        },
                        "options": {
                            "preset": (merchant.custom == 1 ? "islands#blueIcon" : "islands#redDotIcon")
                        }
                    });
                }
            }

            om.add(geoObjectsData);
            myMap.geoObjects.add(om);

            myMap.setBounds(om.getBounds(), {
                checkZoomRange: true
            });
        }
    </script>
{% endblock %}
