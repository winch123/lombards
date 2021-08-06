{% include "partials/page_bar.volt" %}

<div class="page-header search_page_header_fix">
    <div class="row">
        <div class="col-md-12 breadcrumb_sort_section">
            <div class="breadcrumb_category_page_search">
                {% for filter_breadcrumb in filter_breadcrumbs %}
                    {% if filter_breadcrumb['url'] %}
                        <a href="?{{ filter_breadcrumb['url'] }}">{{ filter_breadcrumb['text'] }}</a>
                    {% else %}
                        {{ filter_breadcrumb['text'] }}
                    {% endif %}
                    {% if not loop.last %}
                        /
                    {% endif %}
                {% endfor %}

                <span class="search-header-text-gray">{{ page.total_items }}</span>
            </div>

            <div class="section_sort">
                <span>Сортировка</span>
                {# это поле относится к форме #searchForm #}
                <select class="form-control input-sm search_header_sorter" name="sorter" form="searchForm" onchange="this.form.submit();">
                    <option value="quality" {% if field_sorter == 'quality' %}selected{% endif %}>По качеству</option>
                    <option value="date" {% if field_sorter == 'date' %}selected{% endif %}>Новинки</option>
                    <option value="cheaper" {% if field_sorter == 'cheaper' %}selected{% endif %}>Дешевле</option>
                    <option value="expensive" {% if field_sorter == 'expensive' %}selected{% endif %}>Дороже</option>
                </select>
            </div>
        </div>
    </div>

    {% if goods_filter and goods_filter|length %}
        <div class="row" style="margin: 10px 0;">
            {% for filter_element in goods_filter %}
                <a href="{{ filter_element.url }}" style="margin-right: 10px; white-space: nowrap;">{{ filter_element.name }} <span class="search-header-text-gray">{{ filter_element.goods_count }}</span></a>
            {% endfor %}
        </div>
    {% endif %}
</div>

<div class="row">
    <div class="col-md-12 goods-big-preview goods-cards goods-preview-carousel">
        <div class="row all_products">
            {% for item in page.getItems() %}
                <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 good-card">
                    {{ partial('partials/goods_card', ['item': item]) }}
                </div>
            {% endfor %}
        </div>

        {% if not page.getItems() %}
            <div class="alert alert-warning" role="alert">
                По данному запросу, ничего не найдено <a href="/" class="alert-link">вернуться на главную</a>.
            </div>
        {% endif %}

        {{ page_bar(page, pageUrl) }}
    </div>
</div>
