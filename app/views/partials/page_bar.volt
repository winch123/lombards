{%- macro linkToPage(url, pageNum, text) %}
    {% if pageNum > 1 %}
        {{ linkTo(url ~ ('?' in url ? '&' : '?') ~ 'page=' ~ pageNum, text) }}
    {% else %}
        {{ linkTo(url, text) }}
    {% endif %}
{%- endmacro %}

{# Макрос "Пагинации" #}
{%- macro page_bar(page, pageUrl) %}
{% if page.getLast() > 1 %}
    <nav>
        <ul class="pagination pagination-sm">
            {% if page.getCurrent() > 1 %}
                <li>{{ linkToPage(pageUrl, 1, "Первая") }}</li>
                <li>{{ linkToPage(pageUrl, page.getPrevious(), "Предыдущая") }}</li>
            {% endif %}

            {% for i in 1..page.getLast() %}
                {% if i >= (page.getCurrent() - 3) and i <= (page.getCurrent() + 3) %}
                    {% if i == page.getCurrent() %}
                        <li class="active"><a>{{i}}</a></li>
                            {% else %}
                        <li>{{ linkToPage(pageUrl, i, i) }}</li>
                        {% endif %}
                    {% endif %}
                {% endfor %}

            {% if page.getCurrent() < page.getLast() %}
                <li>{{ linkToPage(pageUrl, page.getNext(), "Следующая") }}</li>
                <li>{{ linkToPage(pageUrl, page.getLast(), "Последняя") }}</li>
                {% endif %}
        </ul>
    </nav>
{% endif %}
{%- endmacro %}
