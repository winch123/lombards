<div class="row">
    <div class="col-md-12 col-xs-12 goods-big-preview goods-cards goods-preview-carousel">
        {% if last_goods|length > 0 %}
            <div class="wrapper_analog_h4">
                <h4 class="main_category_header">Последние поступления</h4>
            </div>
            <div class="row all_products">
                {% for item in last_goods %}
                    <div class="col-md-2 col-sm-4 col-xs-6 good-card">
                        {{ partial('partials/goods_card', ['item': item]) }}
                    </div>
                {% endfor %}
            </div>
        {% endif %}

        {% for category_name, category_goods in las_goods_per_category %}
            <div class="wrapper_analog_h4">
                <h4 class="main_category_header"><a href="/category/{{category_name}}" title="{{category_name}}">{{category_name}}</a></h4>
                {% if sluser %}
                    <a class="sluser_main_category_link" href="/article/howto">Что нужно чтобы ваш товар попал на первую страницу</a>
                {% endif %}
            </div>
            <div class="row">
                {% for item in category_goods %}
                    <div class="col-md-2 col-sm-4 col-xs-6 good-card">
                        {{ partial('partials/goods_card', ['item': item]) }}
                    </div>
                {% endfor %}
            </div>
        {% endfor %}
        <h4>Поломбардам.ру — это гид по ломбардам и комиссионным магазинам.</h4>
        <p>
            На этом сайте представлены товары из ломбардов и комиссионных магазинов России, Украины и Казахстана. Абсолютно все товары есть в наличии, это гарантируется постоянной синхронизацией с программным обеспечением ломбардов и комиссионных магазинов: после продажи товара в ломбарде, он сразу исчезает и с нашего сайта. Сейчас представлено <b>{{countGoods}}</b> товаров.
            Помимо этого на сайте представлены все ломбарды России. Сейчас на сайте <b>{{countMerchants}}</b> ломбардов.
            Для того чтобы посмотреть все товары и ломбарды вашего города, выберите его на этой странице:
        </p>
    </div>
</div>

{% include 'partials/choose_city.volt' %}
