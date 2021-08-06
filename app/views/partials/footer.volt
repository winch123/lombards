<div class="container container-footer background-white">
    <div class="col-md-12" style="text-align: center;">
        <div class="menu col-md-12">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                {% if menu %}
                    {% for page in menu %}
                        <a {% if page['active'] %}class="active"{% else %}href="{{ page['url'] }}"{% endif %}>{{ page['title'] }}</a>
                    {% endfor %}
                {% endif %}
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="col-md-12">Товары из ломбардов и комиссионных магазинов</div>
        <div class="col-md-12">
            <div>{{ countGoods }} <b>товаров</b>, {{ countMerchants }} <b>ломбардов</b></div>
        </div>
    </div>
</div>

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="{{ static_url("static/js/bootstrap.min.js") }}"></script>
<script src="{{ static_url("static/js/validator.min.js") }}"></script>
<script src="{{ static_url("static/js/jquery.fancybox.pack.js?v=2.1.5") }}"></script>
<script src="{{ static_url("static/js/slider.js") }}"></script>
<script src="{{ static_url("static/js/slick.min.js") }}"></script>
<script src="{{ static_url("static/js/app.js?v=1") }}"></script>
<script src="{{ static_url("static/js/sweetalert2.js") }}"></script>
