{% include "partials/page_bar.volt" %}

<div class="page-header">
    <h1>{{ page_title }}</h1>
    <p>Список филиалов сети {{ currentMerchantNetwork.merchant_name }}, адреса и телефоны, расположение на карте.</p>
    {% if currentMerchantNetwork.description %}
        <p>{{ currentMerchantNetwork.description }}</p>
    {% endif %}
</div>

<!-- Яндекс Карта -->
<div class="row">
    <div class="col-md-12">
        <h4>На карте</h4>
        <div id="map" style="height:350px"></div>
    </div>
</div>

<br>

<div class="row">
    {% if citys|length > 1 %}
        <div class="col-md-3 col-sm-6">
            <h4>По городам</h4>
            <div class="list-group">
                <a href="/network/{{currentMerchantNetwork.id}}" class="list-group-item {% if cityName == '' %}active{% endif %}">
                    Все города <span class="badge">{{allCitysCount}}</span>
                </a>
                {% for key, cityEntry in citys %}
                    <a href="/{{englishNames[key]}}/network/{{currentMerchantNetwork.id}}" class="list-group-item {% if englishNames[key] == cityName %}active{% endif %}">
                        {{key}} <span class="badge">{{cityEntry}}</span>
                    </a>
                {% endfor %}
            </div>
        </div>
    {% endif %}

    <div class="{% if citys|length > 1 %}col-md-9{% else %}col-md-12{% endif %}">
        <h4>{% if cityRusNameActive == '' %}Все филиалы{% else %}Филиалы в городе {{ cityRusNameActive }}{% endif %}</h4>

        <div class="row lombards-list merchant-big-preview all_filials_of_pawnshop">
            {% for item in page.getItems() %}
                <div class="{% if citys|length > 1 %}col-md-3{% else %}col-md-2{% endif %} col-md-3 col-sm-3 col-xs-6 merchant-card">
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

        {{ page_bar(page, pageUrl) }}
    </div>
</div>

<script type="text/javascript">
    ymaps.ready(initMap);

    function initMap() {
        var myMap = new ymaps.Map('map', {
            center: {% if city.map is defined %}'{{city.map}}'.split(' ').reverse(){% else %}[55.76, 37.64]{% endif %},
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
                        // doesn't work in cluster (bug API 2.1?)
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
        }).then(function () {
            if (myMap.getZoom() > 16) {
                myMap.setZoom(16);
            }
        });
    }
</script>
