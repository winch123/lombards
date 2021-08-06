{% include "partials/page_bar.volt" %}

<div class="page-header">
    <h1>{{categoryName}}</h1>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            {% for category in categories %}
                {% if currentCategory.id == category.id and (not currentSubCategory or subcategories|length == 0) and (currentSubCategoryName|length == 0)%}
                    <button class="list-group-item active">
                        <span class="badge">{{category.rowcount}}</span>
                        {{category.name}}
                    </button>
                {% else %}
                    <a href="/category/{{category.name}}" class="list-group-item">
                        <span class="badge">{{category.rowcount}}</span>
                        {{category.name}}
                    </a>
                {% endif %}

                <!-- подкатегории выбранной категории -->
                {% if currentCategory.id == category.id and subcategories %}
                    <div class="list-subgroup">
                        {% for subcategory in subcategories %}
                            {% if currentSubCategory is defined and currentSubCategory.id == subcategory.id %}
                                 <button class="list-group-item active">
                                    <span class="badge">{{subcategory.rowcount}}</span>
                                    {{subcategory.name}}
                                </button>
                            {% else %}
                                <a href="/category/{{category.name}}/{{subcategory.name}}" class="list-group-item">
                                    <span class="badge">{{subcategory.rowcount}}</span>
                                    {{subcategory.name}}
                                </a>
                            {% endif %}
                        {% endfor %}

                        {% if subcategory_other_count > 0 %}
                            {% if currentSubCategoryName == 'Прочие подкатегории' %}
                                <button class="list-group-item active">
                                    <span class="badge">{{subcategory_other_count}}</span>
                                    Прочие подкатегории
                                </button>
                            {% else %}
                                <a href="/category/{{category.name}}/Прочие подкатегории" class="list-group-item">
                                    <span class="badge">{{subcategory_other_count}}</span>
                                    Прочие подкатегории
                                </a>
                            {% endif %}
                        {% endif %}

                        {% if total_without_subcat > 0 %}
                            {% if currentSubCategoryName == 'Без подкатегории' %}
                                <button class="list-group-item active">
                                    <span class="badge">{{total_without_subcat}}</span>
                                    Без подкатегории
                                </button>
                            {% else %}
                                <a href="/category/{{category.name}}/Без подкатегории" class="list-group-item">
                                    <span class="badge">{{total_without_subcat}}</span>
                                    Без подкатегории
                                </a>
                            {% endif %}
                        {% endif %}

                    </div>
                {% endif %}
            {% endfor %}
        </div>
    </div>
    <div class="col-md-9 goods-big-preview goods-cards goods-preview-carousel">
        <div class="row">
            {% for item in page.getItems() %}
                <div class="col-md-3 col-xs-6 good-card">
                    {{ partial('partials/goods_card', ['item': item]) }}
                </div>
            {% endfor %}
        </div>
        {{ page_bar(page, pageUrl) }}
    </div>
</div>
