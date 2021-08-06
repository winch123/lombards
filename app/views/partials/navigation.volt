<div class="container background-white container-header">
    <div class="navbar-header row">
        <div class="col-md-3 col-md-push-9 col-xs-6 col-xs-push-6">
            <ul class="nav navbar-nav pull-right navbar-text chut-menshe">
                <li class="active-choose-city">
                    <a href="javascript:chooseCity('{{ (currentCountry ? currentCountry.code : 'RU') }}')">
                        {% if is_country is defined and currentCountry is defined %}
                            {{ currentCountry.name }}
                        {% elseif currentRememberedCity is defined %}
                            {{ currentRememberedCity.name }}
                        {% elseif detectedCity is defined %}
                            {{ detectedCity.name }}
                        {% elseif detectedCountry is defined %}
                            {{ detectedCountry.name }}
                        {% else %}
                            Выбрать город
                        {% endif %}
                    </a>
                </li>
            </ul>
            {% if detectedCity is defined and detectedCity.count_good > 0 and not(is_country is defined) %}
                <div class="city-detect-confirm">
                    <div class="confirm-message">Ваш город <img src="/static/img/location.png" style="width: 10px;"> <strong>{{ detectedCity.name }}</strong>?</div>
                    <div>
                        <button class="btn btn-danger btn-confirm">Да</button>
                        <span class="link-action" onclick="javascript:chooseCity('{{ (currentCountry ? currentCountry.code : 'RU') }}')">Выбрать другой</span>
                    </div>
                </div>
            {% endif %}
        </div>

        <div class="col-md-4 col-lg-3 col-md-pull-3 col-xs-6 col-xs-pull-6">
            <a class="navbar-left color-white" href="/"><img class="site-logo" src="/static/img/logo.png" /></a>
        </div>

        <div class="col-md-5 col-lg-6 col-xs-12 col-md-pull-3">
            <div class="navbar-text chut-menshe feature-text">
                <div>Товары из ломбардов и комиссионных магазинов</div>
                <div>{{ countGoods }} <b>товаров</b>, {{ countMerchants }} <b>ломбардов</b></div>
            </div>
        </div>
    </div>
</div>
