{% if searchForm %}
    <div class="search-form">
        <form role="search" action="/search" id="searchForm">
            <div class="form-group form-group-lg row">
                <div class="col-md-3 search-left custom-search wrapper-field-city search-collapse collapse">
                    <select class="form-control input-sm" name="city" id="SearchCity">
                        {% if currentCountry %}
                            <option value="all_country_{{ currentCountry.code }}">Все города {{ (currentCountry.name_case1 ? currentCountry.name_case1 : currentCountry.name) }}</option>
                        {% else %}
                            <option value="all">Все города</option>
                        {% endif %}
                        {% for city in searchForm.cities_list %}
                            <option {% if currentCity and city.id == currentCity.id %}selected{% endif %} value="{{ city.name_translit }}">{{ city.name }}</option>
                        {% endfor %}
                    </select>
                </div>

                <div class="col-md-3 search-center custom-search wrapper-field-merchant search-collapse collapse">
                    <select class="form-control input-sm" name="merchant" id="SearchMerchant">
                        <option value="">Все ломбарды</option>
                        {% if currentMerchantNetwork %}
                            <option value="network{{ currentMerchantNetwork.id }}" selected>Все ломбарды сети {{ currentMerchantNetwork.merchant_name }}</option>
                        {% endif %}
                        {% for network_id, merchants_group in searchForm.merchant_list %}
                            <optgroup label='{{ merchants_group['merchant_name'] }}'>
                                {% for merchant in merchants_group['merchants'] %}
                                    <option {% if currentMerchant and merchant.id == currentMerchant.id %}selected{% endif %} value="{{ merchant.id }}">{{ merchant.name }}</option>
                                {% endfor %}
                            </optgroup>
                        {% endfor %}
                    </select>
                </div>

                <div class="col-md-3 search-center custom-search wrapper-field-category search-collapse collapse">
                    <select class="form-control input-sm" name="category_id" id="SearchCategory">
                        <option value="all" {% if selectedAllCategories %}select{% endif %}>Все категории</option>

                        {% for category in searchForm.categories_list %}
                            {% if currentCategory and category.id == currentCategory.id %}
                                <option class="category-group-option" value="{{ category.id }}" selected>{{ category.name }}</option>
                            {% else %}
                                <option class="category-group-option" value="{{ category.id }}">{{ category.name }}</option>
                            {% endif %}

                            {% if searchForm.subcategories_list[category.id] is defined %}
                                {% for subcategory in searchForm.subcategories_list[category.id] %}
                                    {% if currentSubCategory and subcategory.id == currentSubCategory.id %}
                                        <option value="{{ subcategory.id }}" selected>{{ subcategory.name }}</option>
                                    {% else %}
                                        <option value="{{ subcategory.id }}">{{ subcategory.name }}</option>
                                    {% endif %}
                                {% endfor %}
                            {% endif %}
                        {% endfor %}

                        {% if not currentMerchant %}
                            <option value="other" {% if searchForm.show_custom_categories %}selected{% endif %}>Прочее</option>
                        {% endif %}
                    </select>
                </div>

                <div class="col-md-3 search-right btn-danger clearfix small_display">
                    <!-- hack: use btn-danger to avoid chrome bug when round 0.01 to 1 px -->
                    <input class="form-control search-field input-sm" type="text" placeholder="Поиск{% if currentMerchant.custom %} по городу{% elseif currentMerchantNetwork %} по сети{% elseif currentMerchant %} по ломбарду{% elseif currentCity %} по городу{% endif %}" name="text" value="{{ searchForm.search_text }}">
                    <button class="btn btn-lg btn-danger search-button"><img src="/static/img/search.png" /></button>
                    <img src="/static/img/open_filter.png" class="open_filter block_mobile" data-toggle="collapse" data-target=".search-collapse" />
                </div>
            </div>

            <input type="hidden" id="fakeSearchMinSize" name="min_size" value="{{ filterSizeMin }}">
            <input type="hidden" id="fakeSearchMaxSize" name="max_size" value="{{ filterSizeMax }}">
            <input type="hidden" id="fakeSearchZeroAllowedSize" name="zero_size" value="{% if searchForm.show_only_with_size is defined and searchForm.show_only_with_size %}1{% else %}0{% endif %}">

            <input type="hidden" id="fakeSearchMinPrice" name="min_price" value="{{ filterPriceMin }}">
            <input type="hidden" id="fakeSearchMaxPrice" name="max_price" value="{{ filterPriceMax }}">
            <input type="hidden" id="fakeSearchZeroAllowedPrice" name="zero_price" value="{% if searchForm.show_only_with_price is defined and searchForm.show_only_with_price %}1{% else %}0{% endif %}">

            <input class="search-field-sorter" type="hidden" name="sorter" value="quality">
            <input class="search-field-metal" type="hidden" name="metal" value="{% if currentMetal %}{{ currentMetal.id }}{% else %}0{% endif %}">
            <input class="search-field-metal-standart" type="hidden" name="metal_standart" value="{% if currentMetalStandart %}{{ currentMetalStandart.id }}{% else %}0{% endif %}">
            <input class="search-field-phonemodel" type="hidden" name="phone_model_id" value="{% if currentSubCategory and searchForm.additional_subcategories_list|length > 0 %}{{ currentSubCategory.id }}{% endif %}">
        </form>

        <div class="row search-collapse collapse">
            <div class="col-xs-6 col-sm-6 col-md-2 custom-search-bottom wrapper-field-metal" {% if searchForm.metals_list %}style="display:block;"{% endif %}>
                <select class="form-control input-sm" name="metal" id="SearchMetal">
                    <option value="all">Все металлы</option>
                    {% for metal in searchForm.metals_list %}
                        <option value="{{ metal['id'] }}" {% if currentMetal and metal['id'] == currentMetal.id %}selected{% endif %}>{{ metal['name'] }}</option>
                    {% endfor %}
                </select>
            </div>

            <div class="col-xs-6 col-sm-6 col-md-2 custom-search-bottom wrapper-field-metal-standart" {% if currentMetal and searchForm.metal_standarts_list %}style="display:block;"{% endif %}>
                <select class="form-control input-sm" name="metal_standart" id="SearchMetalStandarts">
                    <option value="all">Все пробы</option>
                    {% for metal_standart in searchForm.metal_standarts_list %}
                        <option value="{{ metal_standart['id'] }}" {% if currentMetalStandart and metal_standart['id'] == currentMetalStandart.id %}selected{% endif %}>{{ metal_standart['name'] }}</option>
                    {% endfor %}
                </select>
            </div>

            <div class="col-md-8 text-right wrapper-field-size" style="float: right; margin-bottom: 5px; {% if searchForm.show_filter_size %}display: block;{% endif %}">
                <label class="search-with-interval text-left">
                    <input
                        id="searchZeroAllowedSize"
                        type="checkbox"
                        name="zero_size"
                        value="{% if searchForm.show_only_with_size is defined and searchForm.show_only_with_size %}1{% else %}0{% endif %}"
                        {% if searchForm.show_only_with_size %}checked{% endif %}
                        >
                    Искать только с размером
                </label>

                <div class="slider-input-container">
                    <span>Размер:</span>
                    <input
                        id="searchMinSize"
                        name="min_size"
                        min="0"
                        class="form-control input-sm search-min search-control-size"
                        placeholder="{{ searchForm.size_min }}"
                        value="{% if filterSizeMin is defined and filterSizeMin %}{{ filterSizeMin }}{% endif %}"
                        >
                    <span>—</span>
                    <input
                        id="searchMaxSize"
                        name="max_size"
                        min="0"
                        class="form-control input-sm search-max search-control-size"
                        placeholder="{{ searchForm.size_max }}"
                        value="{% if filterSizeMax is defined and filterSizeMax %}{{ filterSizeMax }}{% endif %}"
                        >
                </div>

                <div class="slider-container">
                    <input id="slider-size" type="hidden"
                           class="span2"
                           value=""
                           data-slider-min="{{ searchForm.size_min | default(0) }}"
                           data-slider-max="{{ searchForm.size_max | default(0) }}"
                           data-slider-step="{{ searchForm.jewelry_size_step }}"
                           data-slider-tooltip="hide"
                           data-slider-value="[
                           {% if filterSizeMin is defined and filterSizeMin %}{{ filterSizeMin }}{% else %}{{ searchForm.size_min | default(0) }}{% endif %},
                           {% if filterSizeMax is defined and filterSizeMax %}{{ filterSizeMax }}{% else %}{{ searchForm.size_max | default(0) }}{% endif %}
                           ]"
                           >
                </div>
            </div>

            <div class="col-xs-6 col-sm-6 col-md-2 custom-search-bottom wrapper-field-phonemodel" {% if searchForm.additional_subcategories_list|length == 0 %}style="display:none;"{% endif %}>
                <select class="form-control input-sm" name="phone_model_id" id="fakeSearchPhoneModel">
                    <option value="all">Все подкатегории</option>
                    {% for category in searchForm.additional_subcategories_list %}
                        <option value="{{ category.id }}" {% if currentSubCategory and category.id == currentSubCategory.id %}selected{% endif %}>{{ category.name }}</option>
                    {% endfor %}
                </select>
            </div>

            <div class="col-md-8 text-right price-filter-block" style="float: right;">
                <label class="search-with-interval text-left">
                    <input
                        id="searchZeroAllowedPrice"
                        type="checkbox"
                        name="zero_price"
                        value="{% if searchForm.show_only_with_price is defined and searchForm.show_only_with_price %}1{% else %}0{% endif %}"
                        {% if searchForm.show_only_with_price %}checked{% endif %}
                        >
                    Искать только с ценой
                </label>

                <div class="slider-input-container">
                    <span>Цена:</span>
                    <input
                        id="searchMinPrice"
                        name="min_price"
                        min="0"
                        class="form-control input-sm search-min search-control-price"
                        placeholder="{{ searchForm.price_min }}"
                        value="{% if filterPriceMin is defined and filterPriceMin %}{{ filterPriceMin }}{% endif %}"
                        >
                    <span>—</span>
                    <input
                        id="searchMaxPrice"
                        name="max_price"
                        min="0"
                        class="form-control input-sm search-max search-control-price"
                        placeholder="{{ searchForm.price_max }}"
                        value="{% if filterPriceMax is defined and filterPriceMax %}{{ filterPriceMax }}{% endif %}">
                </div>

                <div class="slider-container">
                    <input id="slider-price" type="hidden"
                           class="span2"
                           value=""
                           data-slider-min="{{ searchForm.price_min | default(0) }}"
                           data-slider-max="{{ searchForm.price_max | default(0) }}"
                           data-slider-step="1"
                           data-slider-tooltip="hide"
                           data-slider-value="[
                           {% if filterPriceMin is defined and filterPriceMin %}{{ filterPriceMin }}{% else %}{{ searchForm.price_min | default(0) }}{% endif %},
                           {% if filterPriceMax is defined and filterPriceMax %}{{ filterPriceMax }}{% else %}{{ searchForm.price_max | default(0) }}{% endif %}
                           ]"
                           >
                </div>
            </div>
        </div>
    </div>
{% endif %}
