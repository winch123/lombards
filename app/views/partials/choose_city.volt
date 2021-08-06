<div class="choose-city">
    <div class="col-md-12">
        <ul class="nav nav-tabs tab-country">
            {% for country in countries_for_tabs %}
                <li class="{% if country['code'] == current_country_code %}active{% endif %}" data-toggle="tab">
                    <a href="#{{ id_prefix_country_tabs }}tab-country-{{ country['code'] | e }}">{{ country['name'] | e }}</a>
                </li>
            {% endfor %}
            <li class="{% if current_country_code == 'other' %}active{% endif %}" data-toggle="tab"><a href="#{{ id_prefix_country_tabs }}tab-country-other">Другие</a></li>
        </ul>
    </div>
    <div class="col-md-12 tab-content">
        {% for country_code, city_columns in city_countries %}
            <div class="tab-pane{% if country_code == current_country_code %} active{% endif %}" id="{{ id_prefix_country_tabs }}tab-country-{{ country_code | e }}">
                <div class="tab-content-country">
                    <div class=""><span>Выбрать все города страны </span>
                        {% for country in countries_for_links[country_code] %}
                            <a href="/search?city=all_country_{{ country['code'] }}">{{ country['name'] }}</a>
                            {% if !loop.last %}<span>,&nbsp;</span>{% endif %}
                        {% endfor %}
                    </div>
                </div>
                {% for column_letters in city_columns %}
                    <div class="col-md-3">
                        {% for letter, letter_cities in column_letters %}
                            <h5><b>{{ letter }}</b></h5>
                            {% for city in letter_cities %}
                                {% if city.count > 0 %}
                                    <a href="/city/{{ city.name_translit }}" class="remember-city-link" data-id="{{ city.id }}">{{ city.name }}</a> ({{ city.count }}) <br>
                                {% else %}
                                    <a href="/{{ city.name_translit }}/spisok_lombardov" class="remember-city-link" data-id="{{ city.id }}">{{ city.name }}</a> <br>
                                {% endif %}
                            {% endfor %}
                        {% endfor %}
                    </div>
                {% endfor %}
            </div>
        {% endfor %}
    </div>
</div>
