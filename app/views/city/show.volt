{% extends "partials/city_tabs.volt" %}

{% block tabs_nav %}
    <li class="active"><a href="#">Товары из ломбардов {{ cityTitle }}</a></li>
    <li><a href="/{{ currentCity.name_translit }}/spisok_lombardov">Все ломбарды {{ cityTitle }}</a></li>
{% endblock %}

{% block tab_content %}
    {% include "partials/page_bar.volt" %}
    <div class="page-header no-border">
        {% if not currentCategory %}
            <h1>{{ tabLombardTitle }}</h1>
        {% else %}
            <div class="arrow_back_head_category">
                <a href="{% if currentSubCategory %}/city/{{currentCity.name_translit}}/{{currentCategory.name_translit}}{% else %}/city/{{currentCity.name_translit}}{% endif %}">
                    <img src="/static/img/arrow_back_to_category.png">
                </a>
                <h1>{% if (currentCategory.name == 'Мобильные телефоны' or currentCategory.name == 'Ноутбуки и нетбуки')
                    and currentSubCategory
                    and currentSubCategory.name != 'Другие марки'
                    and currentSubCategory.name != 'Модемы и роутеры'
                    and currentSubCategory.name != 'Рации' %}
                    {{ currentCategory.name }} {% endif %}{{ tabLombardTitle }}</h1>
            </div>
        {% endif %}
        <p>{{ headerTitleSub }}</p>
    </div>
    <div class="row">
        <div class="col-md-3{% if currentCategory and not currentSubCategory %} selected_category{% elseif currentSubCategory %} selected_subcategory{% endif %}">
            {% if not currentCategory %}
                <h4 class="block_mobile link_all_lombards_city"><a href="/{{ currentCity.name_translit }}/spisok_lombardov">Все ломбарды {{ cityTitle }}</a></h4>
            {% endif %}

            <h4>Товары по категориям</h4>

            <div class="list-group categoriesgoods{% if currentCategory and not hasSubcategoriesInCategory %} no-subcategories{% endif %}">
                {% if not currentCategory and categoryName == 'Все' %}
                    <button class="list-group-item active">
                        <span class="badge">{{categoriesAllCount}}</span>
                        Все
                    </button>
                {% else %}
                    <a href="/city/{{currentCity.name_translit}}#content" class="list-group-item">
                        <span class="badge">{{categoriesAllCount}}</span>
                        Все
                    </a>
                {% endif %}

                {% for category in categories %}
                    <div class="category-wrapper-custom {% if subcategoriesData[category.id]['subcategories'] %}exists-dropdown-list-custom{% endif %} {% if currentCategory.id == category.id %}active{% endif %}">
                        {% if currentCategory.id == category.id and not currentSubCategory and subcategoryName != 'Прочие подкатегории' and subcategoryName != 'Prochie-podcategorii' and subcategoryName != 'Без подкатегории' and subcategoryName != 'Prochie-podcategorii' and subcategoryName != 'Bez-podkategorii' %}
                            <button class="list-group-item active">
                                <span class="badge">{{category.rowcount}}</span>
                                {{category.name}}
                            </button>
                        {% else %}
                            <a href="/city/{{currentCity.name_translit}}/{{category.name_translit}}#content" class="list-group-item">
                                <span class="badge">{{category.rowcount}}</span>
                                {{category.name}}
                            </a>
                        {% endif %}

                        <!-- подкатегории текущей категории -->
                        {% if currentCategory.id != category.id and (subcategoriesData[category.id]['subcategories'] or subcategoriesData[category.id]['subcategory_other_count']) %}
                            <div class="list-subgroup categoriesgoods dropdown-list-custom">
                                {% if subcategoriesData[category.id]['subcategories'] is defined %}
                                    {% for subcategory in subcategoriesData[category.id]['subcategories'] %}
                                        {% if currentSubCategory is defined and currentSubCategory.id == subcategory.id %}
                                            <button class="list-group-item active">
                                                <span class="badge">{{subcategory.rowcount}}</span>
                                                {{subcategory.name}}
                                            </button>
                                        {% else %}
                                            <a href="/city/{{currentCity.name_translit}}/{{category.name_translit}}/{{subcategory.name_translit}}#content" class="list-group-item">
                                                <span class="badge">{{subcategory.rowcount}}</span>
                                                {{subcategory.name}}
                                            </a>
                                        {% endif %}
                                    {% endfor %}
                                {% endif %}

                                {% if subcategoriesData[category.id]['subcategory_other_count'] > 0 %}
                                    {% if subcategoryName == 'Прочие подкатегории' or subcategoryName == 'Prochie-podcategorii' %}
                                        <button class="list-group-item active">
                                            <span class="badge">{{subcategoriesData[category.id]['subcategory_other_count']}}</span>
                                            Прочие подкатегории
                                        </button>
                                    {% else %}
                                        <a href="/city/{{currentCity.name_translit}}/{{category.name_translit}}/Prochie-podcategorii#content" class="list-group-item">
                                            <span class="badge">{{subcategoriesData[category.id]['subcategory_other_count']}}</span>
                                            Прочие подкатегории
                                        </a>
                                    {% endif %}
                                {% endif %}

                                {% if subcategoriesData[category.id]['total_without_subcat'] > 0 %}
                                    <a href="/city/{{currentCity.name_translit}}/{{category.name_translit}}/Bez-podkategorii#content" class="list-group-item">
                                        <span class="badge">{{subcategoriesData[category.id]['total_without_subcat']}}</span>
                                        Без подкатегории
                                    </a>
                                {% endif %}
                            </div>
                        {% endif %}

                        <!-- подкатегории выбранной категории -->
                        {% if currentCategory.id == category.id and (subcategoriesData[category.id]['subcategories'] or subcategoriesData[category.id]['subcategory_other_count'])%}
                            <div class="list-subgroup categoriesgoods">
                                {% if subcategoriesData[category.id]['subcategories'] is defined %}
                                    {% for subcategory in subcategoriesData[category.id]['subcategories'] %}
                                        {% if currentSubCategory is defined and currentSubCategory.id == subcategory.id %}
                                            <button class="list-group-item active">
                                                <span class="badge">{{subcategory.rowcount}}</span>
                                                {{subcategory.name}}
                                            </button>
                                        {% else %}
                                            <a href="/city/{{currentCity.name_translit}}/{{category.name_translit}}/{{subcategory.name_translit}}#content" class="list-group-item">
                                                <span class="badge">{{subcategory.rowcount}}</span>
                                                {{subcategory.name}}
                                            </a>
                                        {% endif %}
                                    {% endfor %}
                                {% endif %}

                                {% if subcategoriesData[category.id]['subcategory_other_count'] > 0 %}
                                    {% if subcategoryName == 'Прочие подкатегории' or subcategoryName == 'Prochie-podcategorii' %}
                                        <button class="list-group-item active">
                                            <span class="badge">{{subcategoriesData[category.id]['subcategory_other_count']}}</span>
                                            Прочие подкатегории
                                        </button>
                                    {% else %}
                                        <a href="/city/{{currentCity.name_translit}}/{{category.name_translit}}/Prochie-podcategorii#content" class="list-group-item">
                                            <span class="badge">{{subcategoriesData[category.id]['subcategory_other_count']}}</span>
                                            Прочие подкатегории
                                        </a>
                                    {% endif %}
                                {% endif %}

                                {% if subcategoriesData[category.id]['total_without_subcat'] > 0 %}
                                    {% if subcategoryName == 'Без подкатегории' or subcategoryName == 'Bez-podkategorii' %}
                                        <button class="list-group-item active">
                                            <span class="badge">{{subcategoriesData[category.id]['total_without_subcat']}}</span>
                                            Без подкатегории
                                        </button>
                                    {% else %}
                                        <a href="/city/{{currentCity.name_translit}}/{{category.name_translit}}/Bez-podkategorii#content" class="list-group-item">
                                            <span class="badge">{{subcategoriesData[category.id]['total_without_subcat']}}</span>
                                            Без подкатегории
                                        </a>
                                    {% endif %}
                                {% endif %}
                            </div>
                        {% endif %}
                    </div>
                {% endfor %}

                {% if categoriesOtherCount > 0 %}
                    {% if categoryName == 'Прочее' or categoryName == 'Prochee' %}
                        <button class="list-group-item active">
                            <span class="badge">{{categoriesOtherCount}}</span>
                            Прочее
                        </button>
                    {% else %}
                        <a href="/city/{{currentCity.name_translit}}/Prochee#content" class="list-group-item">
                            <span class="badge">{{categoriesOtherCount}}</span>
                            Прочее
                        </a>
                    {% endif %}
                {% endif %}
            </div>
            <h4>Товары по ломбардам</h4>

            <div class="list-group">
                {% if not currentMerchant %}
                    <a class="list-group-item active">
                        <span class="badge">{{merchantsAllCount}}</span>
                        Все
                    </a>
                {% else %}
                    <a href="/city/{{currentCity.name_translit}}#content" class="list-group-item">
                        <span class="badge">{{merchantsAllCount}}</span>
                        Все
                    </a>
                {% endif %}

                {% for merchant in merchants %}
                    {% if currentMerchant.id == merchant.id %}
                        <a class="list-group-item active">
                            <span class="badge">{{merchant.rowcount}}</span>
                            {{merchant.name}}
                        </a>
                    {% else %}
                        <a href="{% if merchant.shortlink %}/{{merchant.shortlink}}{% else %}/merchant/{{merchant.id}}{% endif %}" class="list-group-item">
                            <span class="badge">{{merchant.rowcount}}</span>
                            {{merchant.name}}
                        </a>
                    {% endif %}
                {% endfor %}
            </div>
        </div>

        <div class="col-md-9 goods-big-preview goods-cards goods-preview-carousel" id="goods">
            {% if page.getItems() %}
                <div class="row all_products">
                    {% for item in page.getItems() %}
                        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-6 good-card">
                            {{ partial('partials/goods_card', ['item': item]) }}
                        </div>
                    {% endfor %}
                </div>
                {{ page_bar(page, pageUrl) }}
            {% else %}
                <div class="row">
                    <div class="alert alert-warning" role="alert">
                        В этой категории товаров нет, <a href="/" class="alert-link">вернуться на главную</a>.
                    </div>
                </div>
            {% endif %}
        </div>
    </div>
{% endblock %}
