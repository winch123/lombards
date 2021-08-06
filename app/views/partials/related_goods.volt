{% if related_goods_count %}
    <br/>
    <div class="row goods-cards goods-preview-carousel">
        {% for item in related_goods %}
            <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 good-card">
                {{ partial('partials/goods_card', ['item': item]) }}
            </div>
        {% endfor %}
    </div>
{% endif %}
