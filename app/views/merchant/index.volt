{% include "partials/page_bar.volt" %}

<!--
[Type: {% if currentMerchant.custom %}custom{% else %}smartlombard{% endif %} ]
{% if not currentMerchant.custom %}
[Organization id: {{currentMerchant.organization}} ]
[Workplace id: {{currentMerchant.workplace}} ]
{% endif %}
[Merchant id: {{currentMerchant.id}} ]
-->

<div class="row merchant-all-custom">
    <div class="col-md-3 col-sm-6">
        <div class="block_mobile">

            <div class="img_merchant_header_block">
                {% if currentMerchant.isHaveLogo() %}
                    <a class="fancybox-button" rel="fancybox-button" href="{{currentMerchant.getImage()}}" style="background: url({{currentMerchant.getImage()}}) no-repeat #f5f5f5 center/cover"></a>
                {% endif %}

                <h1>{{currentMerchant.name}}</h1>
            </div>

            {% if isInNetwork %}
                <h4><a href="{{ network_url }}">Все филиалы сети</a></h4>
            {% endif %}

            {% if currentMerchant.description %}
                <h4>Описание:</h4>
                <p>{{ currentMerchant.description }}</p>
            {% elseif not currentMerchant.custom %}
                <h4>Описание:</h4>
                <p>Нет описания</p>
            {% endif %}
        </div>
        <div class="text-center-vertical">
            <a class="fancybox-button" rel="fancybox-button" href="{{currentMerchant.getImage()}}">
                <img src="{{currentMerchant.getImage()}}" class="merchant-image" alt="{{currentMerchant.name|e}}">
            </a>
        </div>
        <div class="well">
            <i class="glyphicon glyphicon-home"></i> <b>Адрес:</b><br>
            <span class="block_desktop">{{currentMerchant.city}}, {{currentMerchant.address}}</span>

            <a class="block_mobile show_map_mobile">
                {{currentMerchant.city}}, {{currentMerchant.address}}
                <img src="/static/img/arrow_map.png" alt="{{currentMerchant.name|e}}">
            </a>

            {% if currentMerchant.map %}
                <div class="map_mobile" style="display: none;">
                    <div class="hide_map_head">
                        <img src="/static/img/arrow_back_to_category.png">
                        <h4>{{currentMerchant.name}}</h4>
                    </div>
                    <div id="map_mob" style="height: 100%;"></div>
                </div>
            {% endif %}

            {% if currentMerchant.phone %}
            <br class="block_desktop"><br>
            <i class="glyphicon glyphicon-earphone"></i> <span class="merchant-phone"><b>Телефон: <a href="javascript:void(0)" class="api get-merchant-phone">Показать номер</a></b></span>
            {% endif %}

            {% if currentMerchant.working_hours is defined and currentMerchant.working_hours %}
                <br><br>
                <i class="glyphicon glyphicon-time"></i> <b>Режим работы:</b><br>{{currentMerchant.working_hours}}
            {% endif %}

            {% if currentMerchant.site %}
                <br><br>
                <i class="glyphicon glyphicon-link"></i> <b>Сайт:</b><br> <a href="{{currentMerchant.site}}" target="_blank">{{currentMerchant.site}}</a>
            {% endif %}
        </div>

        {% if currentMerchant.custom %}
            <script>
                function open_error_report() {
                    $('#myModal').modal('show');
                }

                function send_error_report() {
                    $('#myModal').modal('hide');

                    var merchant_close = (!!$('#merchant_close').prop('checked')) ? 1 : 0;
                    var merchant_bad_adress = (!!$('#merchant_bad_adress').prop('checked')) ? 1 : 0;
                    var merchant_phone_dntwork = (!!$('#merchant_phone_dntwork').prop('checked')) ? 1 : 0;
                    var more = $('#more').val();
                    var merchant_name = $('#merchant_name').val();

                    $.ajax(
                        '/api/add-error-report', {
                            data: {id: {{ currentMerchant.id }}, merchant_close: merchant_close, merchant_bad_adress: merchant_bad_adress, merchant_phone_dntwork: merchant_phone_dntwork, more: more, merchant_name: merchant_name},
                            type: "POST",
                            complete: function (result) {
                                $('#error_report')[0].reset();
                            }
                        }
                    );

                }

                function checkFormError() {
                    var merchant_close = (!!$('#merchant_close').prop('checked')) ? 1 : 0;
                    var merchant_bad_adress = (!!$('#merchant_bad_adress').prop('checked')) ? 1 : 0;
                    var merchant_phone_dntwork = (!!$('#merchant_phone_dntwork').prop('checked')) ? 1 : 0;
                    var more = $('#more').val();

                    if ((merchant_close || merchant_bad_adress || merchant_phone_dntwork ) || more != '') {
                        $('#buttonSendError').prop('disabled','');
                    } else {
                        $('#buttonSendError').prop('disabled','disabled');
                    }
                };
            </script>
            <a class="click_here" href="/{{ currentCity.name_translit }}/edit-merchant/{{ currentMerchant.id }}/step-2" >
                Это ваш ломбард? Требуется обновить или уточнить информацию? Щелкайте сюда
            </a>
            <div style="margin-top: 20px;">
                <a class="click_here" onclick="open_error_report(); return false;" style="cursor: pointer;">
                    Сообщить об ошибке
                </a>
            </div>
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title" id="myModalLabel">Сообщить об ошибке</h4>
                        </div>
                        <div class="modal-body">
                            <form id="error_report" onChange="checkFormError();">
                                <input type="hidden" name="merchant_name" id="merchant_name" value="{{ currentMerchant.name|e }}">
                                <input type="checkbox" name="merchant_close" id="merchant_close"> Организация закрылась <span class="help-block"></span>
                                <input type="checkbox" name="merchant_bad_adress" id="merchant_bad_adress"> Неправильный адрес организации <span class="help-block"></span>
                                <input type="checkbox" name="merchant_phone_dntwork" id="merchant_phone_dntwork"> Не работает телефон организации <span class="help-block"></span>
                                <br><br>
                                <textarea name="more" id="more" class="btn-block" onkeyup="checkFormError();"></textarea>
                                <span class="help-block">Подробнее</span>
                            </form>
                        </div>
                        <div class="modal-footer" style="text-align: left">
                            <button id="buttonSendError" type="button" class="btn btn-success" onclick="send_error_report();" disabled>Отправить</button>
                        </div>
                    </div>
                </div>
            </div>
        {% endif %}

        {% if not currentMerchant.custom %}

            {% if abilityRequestDebt == 1 %}
                <div id="check_debt_box" class="learn_debt_box">
                    <h4>Узнать задолженность и оплатить проценты</h4>
                    <p>Введите серию и номер залогового билета/договора</p>
                    <div class="input-group">
                        <input  type="text" id="pawn_number"
                                name="pawn_number" placeholder="Пример: АА001234"
                                class="form-control input-sm">
                        <span class="input-group-btn">
                            <button class="btn btn-default btn-sm get-pawn" >Проверить</button>
                        </span>
                    </div>
                </div>
            {% endif %}

            {% if currentMerchant.map %}
                <br class="block_desktop">
                <h4 class="block_desktop">На карте</h4>
                <div id="map" class="block_desktop" style="height:350px"></div>
                <script type="text/javascript">
                    if (window.innerWidth > 767) {
                        var active_map = 'map';
                        showMap();
                    } else {
                        var active_map = 'map_mob';
                        var href_category;

                        $(document).on('click', '.show_map_mobile', function() {
                            $('.map_mobile').show();
                            showMap();
                        });

                        $(document).on('click', '.map_mobile .hide_map_head img', function() {
                            $('.map_mobile').hide();
                        });

                        $(document).on('click', '.list-group-item', function () {
                            href_category = $(this).attr("href");
                            href_category = href_category.replace('#content', '#category');
                            $(this).attr("href", href_category);
                        });
                    }

                    function showMap() {
                        ymaps.ready(initMap);

                        function initMap () {
                            var myMap = new ymaps.Map(active_map, {
                                center: '{{currentMerchant.map}}'.split(' ').reverse(),
                                zoom: 16,
                                controls: ['zoomControl', 'typeSelector', 'fullscreenControl', 'routeEditor']
                            });

                            var om = new ymaps.ObjectManager({
                                clusterize: true
                            });

                            var geoObjectsData = {
                                "type": "FeatureCollection",
                                "features": []
                            };

                            geoObjectsData.features.push({
                                "type": "Feature",
                                "id": {{currentMerchant.id}},
                                "geometry": {
                                    "type": "Point",
                                    "coordinates": '{{currentMerchant.map}}'.split(' ').reverse()
                                },
                                "properties": {
                                    "balloonContent": "<h4>{{currentMerchant.name|e}}</h4><p>{{currentMerchant.address|e}}</p>",
                                }
                            });

                            om.add(geoObjectsData);
                            myMap.geoObjects.add(om);
                        }
                    }
                </script>
            {% endif %}
            <br>

            <div id="category" class="block_mobile">
                {% if not currentCategory %}
                    <h4>Товары по категориям</h4>
                {% else %}
                    <div class="arrow_back_head_category">
                        <a href="{% if currentSubCategory %}/merchant/{{currentMerchant.id}}/{{currentCategory.name_translit}}{% else %}/merchant/{{currentMerchant.id}}{% endif %}#category">
                            <img src="/static/img/arrow_back_to_category.png">
                        </a>
                        <h4>{% if currentSubCategory %}{{currentSubCategory.name}}{% else %}{{ currentCategory.name }}{% endif %}</h4>
                    </div>
                {% endif %}
            </div>

            <h4 class="block_desktop">Товары по категориям</h4>

            <div class="{% if currentCategory and not currentSubCategory %}selected_category{% elseif currentSubCategory %}selected_subcategory{% endif %}{% if currentCategory and not hasProductWithSubcategory %} no-subcategories{% endif %}">
                <div class="list-group categoriesgoods">
                    {% if not currentCategory %}
                        <a class="list-group-item active">
                            <span class="badge">{{categories_filter_all_count}}</span>
                            Все
                        </a>
                    {% else %}
                        <a href="/merchant/{{currentMerchant.id}}#content" class="list-group-item">
                            <span class="badge">{{categories_filter_all_count}}</span>
                            Все
                        </a>
                    {% endif %}

                    {% for category in categories %}
                        <div class="category-wrapper-custom {% if subcategoriesData[category.id]['subcategories'] %} exists-dropdown-list-custom {% endif %} {% if currentCategory.id == category.id %}active{% endif %}">
                            {% if currentCategory.id == category.id and not currentSubCategory and subcategoryName != 'Без подкатегории' and subcategoryName != 'Bez-podkategorii' %}
                                <button class="list-group-item active">
                                    <span class="badge">{{category.rowcount}}</span>
                                    {{category.name}}
                                </button>
                            {% else %}
                                <a href="/merchant/{{currentMerchant.id}}/{{category.name_translit}}#content" class="list-group-item">
                                    <span class="badge">{{category.rowcount}}</span>
                                    {{category.name}}
                                </a>
                            {% endif %}

                            <!-- подкатегории текущей категории -->
                            {% if currentCategory.id != category.id and subcategoriesData[category.id]['subcategories'] %}
                                <div class="list-subgroup categoriesgoods dropdown-list-custom">
                                    {% for subcategory in subcategoriesData[category.id]['subcategories'] %}
                                        {% if currentSubCategory is defined and currentSubCategory.id == subcategory.id %}
                                            <button class="list-group-item active">
                                                <span class="badge">{{subcategory.rowcount}}</span>
                                                {{subcategory.name}}
                                            </button>
                                        {% else %}
                                            <a href="/merchant/{{currentMerchant.id}}/{{category.name_translit}}/{{subcategory.name_translit}}#content" class="list-group-item">
                                                <span class="badge">{{subcategory.rowcount}}</span>
                                                {{subcategory.name}}
                                            </a>
                                        {% endif %}
                                    {% endfor %}

                                    {% if subcategoriesData[category.id]['total_without_subcat'] > 0 %}
                                        <a href="/merchant/{{currentMerchant.id}}/{{category.name_translit}}/Bez-podkategorii#content" class="list-group-item">
                                            <span class="badge">{{subcategoriesData[category.id]['total_without_subcat']}}</span>
                                            Без подкатегории
                                        </a>
                                    {% endif %}
                                </div>
                            {% endif %}

                            <!-- подкатегории выбранной категории -->
                            {% if currentCategory.id == category.id and subcategoriesData[category.id]['subcategories'] %}
                                <div class="list-subgroup">
                                    {% for subcategory in subcategoriesData[category.id]['subcategories'] %}
                                        {% if currentSubCategory.id == subcategory.id %}
                                             <button class="list-group-item active">
                                                <span class="badge">{{subcategory.rowcount}}</span>
                                                {{subcategory.name}}
                                            </button>
                                        {% else %}
                                            <a href="/merchant/{{currentMerchant.id}}/{{category.name_translit}}/{{subcategory.name_translit}}#content" class="list-group-item">
                                                <span class="badge">{{subcategory.rowcount}}</span>
                                                {{subcategory.name}}
                                            </a>
                                        {% endif %}
                                    {% endfor %}

                                    {% if subcategoriesData[category.id]['total_without_subcat'] > 0 %}
                                        {% if subcategoryName == 'Без подкатегории' or subcategoryName == 'Bez-podkategorii' %}
                                            <button class="list-group-item active">
                                                <span class="badge">{{subcategoriesData[category.id]['total_without_subcat']}}</span>
                                                Без подкатегории
                                            </button>
                                        {% else %}
                                            <a href="/merchant/{{currentMerchant.id}}/{{category.name_translit}}/Bez-podkategorii#content" class="list-group-item">
                                                <span class="badge">{{subcategoriesData[category.id]['total_without_subcat']}}</span>
                                                Без подкатегории
                                            </a>
                                        {% endif %}
                                    {% endif %}

                                </div>
                            {% endif %}
                        </div>
                    {% endfor %}
                </div>
            </div>
        {% endif %}

        {% if tags and tags|length %}
            <div class="block_desktop">
                <h4>Данный ломбард относится к следующим категориям:</h4>
                {%- for merchantTag in tags -%}
                    {% if loop.index > 1 %},{% endif %}
                    <a href="/{{ currentCity.name_translit }}/tag/{{ merchantTag.id }}" title="{{ merchantTag.getTitle(currentCity)|e }}">{{ merchantTag.getName(currentCity) }}</a>
                {%- endfor -%}
            </div>
        {% endif %}
    </div>

    <div class="col-md-9 goods-big-preview goods-cards goods-preview-carousel">
        <h1 class="header-h1-custom">{{currentMerchant.name}}</h1>

        {% if isInNetwork %}
            <h4 class="block_desktop"><a href="{{ network_url }}">Все филиалы сети</a></h4>
        {% endif %}

        {% if currentMerchant.description %}
            <div class="header-separator-custom no-border"></div>

            <h4 class="block_desktop">Описание:</h4>
            <p class="block_desktop">{{ currentMerchant.description }}</p>
        {% elseif not currentMerchant.custom %}
            <div class="header-separator-custom no-border"></div>

            <h4 class="block_desktop">Описание:</h4>
            <p class="block_desktop">Нет описания</p>
        {% endif %}

        <div class="header-separator-custom no-border"></div>

        {% if currentMerchant.custom %}
            <h4 class="block_desktop">На карте:</h4>
            <div id="map" class="block_desktop" style="height:350px"></div>
            {% if currentMerchant.map %}
                <script type="text/javascript">
                    if (window.innerWidth > 767) {
                        var active_map = 'map';
                        showMap();
                    } else {
                        var active_map = 'map_mob';
                    }

                    function showMap() {
                        ymaps.ready(initMap);

                        function initMap () {
                            var myMap = new ymaps.Map(active_map, {
                                center: '{{currentMerchant.map}}'.split(' ').reverse(),
                                zoom: 16,
                                controls: ['zoomControl', 'typeSelector', 'fullscreenControl', 'routeEditor']
                            });

                            var om = new ymaps.ObjectManager({
                                clusterize: true
                            });

                            var geoObjectsData = {
                                "type": "FeatureCollection",
                                "features": []
                            };

                            geoObjectsData.features.push({
                                "type": "Feature",
                                "id": {{currentMerchant.id}},
                                "geometry": {
                                    "type": "Point",
                                    "coordinates": '{{currentMerchant.map}}'.split(' ').reverse()
                                },
                                "properties": {
                                    "balloonContent": "<h4>{{currentMerchant.name|e}}</h4><p>{{currentMerchant.address|e}}</p>",
                                }
                            });

                            om.add(geoObjectsData);
                            myMap.geoObjects.add(om);
                        }
                    }
                </script>
            {% endif %}
        {% else %}
            <div class="row">
                <div class="col-md-12">
                    {% if not currentCategory %}
                        <h4 class="block_desktop">Все товары</h4>
                    {% else %}
                        <h4 class="block_desktop">{% if currentSubCategory %}{{currentSubCategory.name}}{% else %}{{ currentCategory.name }}{% endif %}</h4>
                    {% endif %}
                </div>
            </div>
            <div class="row all_products">
                {% for item in page.getItems() %}
                    <div class="col-md-3 col-sm-3 col-xs-6 good-card">
                        {{ partial('partials/goods_card', ['item': item]) }}
                    </div>
                {% endfor %}

                {% if page.getTotalItems() < 1 %}
                    <div class="col-md-12">
                        <div class="alert alert-warning" role="alert">
                            Этот магазин не выставил товаров на продажу, <a href="/" class="alert-link">вернуться на главную</a>.
                        </div>
                    </div>
                {% endif %}
            </div>
            {{ page_bar(page, pageUrl) }}
        {% endif %}
    </div>
</div>

{% if not currentMerchant.custom %}
<script type="text/javascript">
    $('.get-pawn').click(function(e){
        e.preventDefault();

        $.ajax(
            '{{config.smartlombard.url}}/api/debt/get', {
                data: {
                    "number": $('#pawn_number').val(),
                    "organization": {{currentMerchant.organization}},
                    "workplace": {{currentMerchant.workplace}}
                },
                type: "POST",
                dataType: "json",
                complete: function (result) {
                    var data = JSON.parse(result.responseText);

                    if (data && data.summ) {
                        showPawnTicketDebtPopup(JSON.parse(result.responseText), $('#pawn_number').val());
                    } else {
                        if (data.error) {
                            alert(data.error);
                        }
                    }
                }
            }
        );
    });

    function showPawnTicketDebtPopup(data, pawn) {
        var table = '<div class="pawn-prices-result">' +
            '<h4>Залоговый билет/договор ' + $('<div/>').text(pawn).html() + '</h4>' +
            '<p>Расчет на ' + data.datetime + '</p>' +
            '<p class="bg-success good-price-debt">Сумма процентов к оплате: ' + data.summ + '</p>';

        if (typeof data.price !== 'undefined') {
            table += '<table class="table table-bordered"><tbody>' +
                '<tr><td>Сумма займа</td><td>' + data.price + '</td></tr><tr><td>Сумма на выкуп</td><td>' + data.buyout_price + '</td></tr>' +
                '</tbody></table>';
        }

        table += '<strong>Имущество:</strong>' +
            '<ol class="list-good-items">';

        for (x in data.goods) {
            table += '<li class="list-good-item">' + data.goods[x] + '</li>';
        }

        table += '</ol>';

        if (data.buyout_date) {
            table += '<div class="pawn-additional-info" style="margin-bottom: 20px;">'
                    + '<strong>Заложено до:</strong>'
                    + '<div>' + data.buyout_date + '</div>'
                    + '</div>';
        }

        if (data.online_prolongation_url && data.online_prolongation_url != '') {
            table += '<div>'
                    + '<a class="btn btn-lg btn-success" href="' + data.online_prolongation_url + '" target="_blank">Перейти к оплате процентов</a>'
                    + '</div>';
        }

        table += '</div>';

        $.fancybox(table);
    }
</script>
{% endif %}

<script type="text/javascript">
    $('.get-merchant-phone').click(function(e){
        e.preventDefault();

        try {
            {% if currentMerchant.custom %}
                yaCounter21293509.reachGoal('custom_merchant_phone_request');
            {% else %}
                yaCounter21293509.reachGoal('sl_merchant_phone_request');
            {% endif %}
        } catch (e) {
            console.error(e);
        }

        $.ajax(
            '/api/need-merchant-phone', {
                data: {
                    id: {{currentMerchant.id}}
                },
                type: "POST",
                dataType: "json",
                complete: function (result) {
                    $('.merchant-phone').html('<b>Телефон:</b><br/> ' + result.responseText);
                }
            }
        );
    });
</script>

{% if sluser and currentMerchant.organization == sluser and page.getCurrent() == 1 %}
    {% if !yellow_hint_hide_1 and (currentMerchant.custom and !currentMerchant.logo or !currentMerchant.isHaveLogo()) %}
        <div class="yellow_hint_top" data-priority="1">
            <span class="close_yellow_hint">x</span>
            Вы можете получать больше клиентов из интернета, если загрузите аватар (картинку) для вашего ломбарда. Ломбарды у которых загружена картинка показываются выше остальных.
        </div>
        <script type="text/javascript">
            $('.yellow_hint_top[data-priority="1"]').slideDown();
        </script>
    {% elseif !yellow_hint_hide_2 and !currentMerchant.shortlink %}
        <div class="yellow_hint_top" data-priority="2">
            <span class="close_yellow_hint">x</span>
            Займите себе короткий адрес! Вы можете сделать своему ломбарду короткий адрес "поломбардам.рф/ВАШЕ_ИМЯ". Это бесплатно. Вам нужно только выбрать имя и указать его в настройках интернет-магазина на стороне СмартЛомбарда.
        </div>
        <script type="text/javascript">
            $('.yellow_hint_top[data-priority="2"]').slideDown();
        </script>
    {% endif %}
{% endif %}
