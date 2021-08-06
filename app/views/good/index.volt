{# Макрос "Пагинации" #}
{%- macro page_bar(page, pageUrl) %}
<nav>
    <ul class="pagination pagination-sm">
    {% if page.getCurrent() > 1 %}
        <li>{{ linkTo(pageUrl ~ 1, "Первая") }}</li>
        <li>{{ linkTo(pageUrl ~ page.getPrevious(), "Предыдущая") }}</li>
    {% endif %}

    {% for i in 1..page.getLast() %}
        {% if i >= (page.getCurrent() - 3) and i <= (page.getCurrent() + 3) %}
            {% if i == page.getCurrent() %}
                <li class="active"><a>{{i}}</a></li>
            {% else %}
                <li>{{ linkTo(pageUrl ~ i, i) }}</li>
            {% endif %}
        {% endif %}
    {% endfor %}

    {% if page.getCurrent() < page.getLast() %}
        <li>{{ linkTo(pageUrl ~ page.getNext(), "Следующая") }}</li>
        <li>{{ linkTo(pageUrl ~ page.getLast(), "Последняя") }}</li>
    {% endif %}
    </ul>
</nav>
{%- endmacro %}

<div class="page-header">
    <h1>Товары <small>({{cityName}})</small></h1>
</div>

{{ page_bar(page, pageUrl) }}

<div class="row">
    {% for item in page.getItems() %}
        <div class="col-md-3">
            <div class="thumbnail">
                <img src="{{item.getImage()}}" style="max-height: 100px;" alt>
                <div class="caption">
                    {{ linkTo(pageUrlClear ~ item.id, item.name)}}
                    <p><b>{{item.price}} {{item.currency}}</b></p>
                </div>
            </div>
        </div>
    {% endfor %}
</div>

{{ page_bar(page, pageUrl) }}

