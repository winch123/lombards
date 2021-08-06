<div class="page-header">
    {{ linkTo("/merchant/" ~ currentMerchant.id, '<img src="/static/img/arrow_back_to_category.png">') }}
    <h1>{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}</h1>
</div>

<div class="row">
    <div class="col-md-4 col-sm-6 col-xs-12" style="text-align: center;">
        {% if currentGood.getImageType() == 'self' %}
            <div class="slider_good_mobile thumbnail">
                <img src="{{currentGood.getMainImageSrc()}}" class="good-image sold" alt="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}"
                     title="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}">
                {% for secondImage in currentGood.getImages() %}
                    {% if not secondImage.main %}
                        <img src="{{secondImage.src}}" class="good-image sold" alt="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}"
                     title="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}">
                    {% endif %}
                {% endfor %}
            </div>

            <div class="slick-counter block_mobile"></div>

            <div class="gallery_good_desktop">
                <a class="fancybox-button thumbnail" rel="fancybox-button" href="{{currentGood.getMainImageSrc()}}">
                    <img src="{{currentGood.getMainImageSrc()}}" class="good-image sold" alt="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}"
                     title="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}">
                </a>

                <div class="good-preview row">
                    {% for secondImage in currentGood.getImages() %}
                        {% if not secondImage.main %}
                            <div class="col-md-6 col-xs-6">
                                <a class="fancybox-button thumbnail" rel="fancybox-button" href="{{secondImage.src}}">
                                    <img src="{{secondImage.preview}}" class="good-image sold" alt="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}"
                     title="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}">
                                </a>
                            </div>
                        {% endif %}
                    {% endfor %}
                </div>
            </div>
        {% endif %}

        {% if currentGood.getImageType() == 'none' %}
            <a class="fancybox-button thumbnail" rel="fancybox-button">
                <img src="/static/img/noimage.gif" class="good-image sold" alt="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}"
                     title="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}">
            </a>
        {% endif %}

        {% if currentGood.getImageType() == 'studio' %}
            <a class="fancybox-button thumbnail" rel="fancybox-button" href="{{currentGood.getStudioImage()}}">
                <img src="{{currentGood.getStudioImage()}}" class="good-image sold" alt="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}"
                     title="{{currentGood.name}} {% if currentCity.name_case2 %}{{ currentCity.name_case2 }}{% endif %}">
            </a>
        {% endif %}
        {% if currentGood.getImageType() == 'studio' %}
            <i>Примечание: Фотография подставилась автоматически,
            цвет и состояние могут отличаться</i>
            {% if (settings['show_get_photo'] is not defined or settings['show_get_photo']) %}<a href="/api/need-photo" class="api get-photo">Запросить настоящую фотографию</a>{% endif %}
        {% elseif currentGood.getImageType() == 'none' %}
            {% if (settings['show_get_photo'] is not defined or settings['show_get_photo']) %}<a href="/api/need-photo" class="api get-photo">Запросить фотографию</a>{% endif %}
        {% endif %}
        <br><br>
    </div>

    <div class="col-md-8 col-sm-6 col-xs-12">
        <div class="row">
            <div class="col-md-7 col-sm-12 col-xs-12 good-info-block">
                {% if currentGood.sold %}
                    <div class="good-price bg-warning">
                        <s>Цена: {{currentGood.price}} {{currentGood.currency}}</s>
                        <span class="label label-danger">Товар продан</span>
                    </div>
                {% elseif currentGood.withdrawn %}
                    <div class="good-price bg-warning">
                        <s>Цена: {{currentGood.price}} {{currentGood.currency}}</s>
                        <span class="label label-danger">Товар снят с продажи</span>
                    </div>
                {% elseif currentGood.hidden %}
                    <div class="good-price bg-warning">
                        <s>Цена: {{currentGood.price}} {{currentGood.currency}}</s>
                        <span class="label label-warning">
                            {% if currentGood.hidden_reason == 1 %}
                                Товар на ремонте
                            {% elseif currentGood.hidden_reason == 2 %}
                                Товар зарезервирован
                            {% else %}
                                Товар скрыт
                            {% endif %}
                        </span>
                    </div>
                {% else %}
                    {% if currentGood.price > 0 %}
                        <div class="good-price btn btn-warning btn-price">
                            Цена: {{currentGood.price}} {{currentGood.currency}}
                        </div>
                    {% else %}
                        <div class="good-price bg-warning">Цена не указана</div>
                    {% endif %}
                {% endif %}
                <div class="good-summary">
                    <b>Описание:</b> {{currentGood.features}} <br>

                    {% if currentGood.size %}
                        <b>Размер/Длина:</b> {{currentGood.size}} <br>
                    {% endif %}

                    {% if good_specs is defined and good_specs %}
                        <b>Характеристики:</b> {{good_specs.specification}} <br>
                    {% endif %}

                    <b>Артикул:</b> {{currentGood.article}} <br>
                    <b>Размещено:</b> {{ currentGood.getDateCustom() }} <br>
                    <b>Количество просмотров:</b> {{currentGood.counter_all}} <br>
                    {% if (settings['show_get_answer'] is not defined or settings['show_get_answer']) %}
                        <a href="javascript:void(0)" class="api get-description ask-question">Задать вопрос по товару</a>
                    {% endif %}
                </div>
            </div>

            <div class="col-md-5 col-sm-12 col-xs-12 merchant-info-block">
                <button class="btn get-phone">
                    <span class="good-phone">Показать телефон<br>{{ currentMerchant.getMaskedPhone() }}</span>
                </button>

                {% if (settings['show_get_callback'] is not defined or settings['show_get_callback']) %}
                    <button class="btn get-call">
                        Заказать обратный звонок от продавца
                    </button>
                {% endif %}

                <h4>Информация о продавце</h4>
                <a href="{{currentMerchant.getUrl()}}"><b>{{currentMerchant.name}}</b></a> <br>
                {{currentMerchant.city}}, {{currentMerchant.address}} <br><br>

                <span>
                    <svg version="1.1" id="Слой_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 14 10"
                         style="enable-background:new 0 0 14 10; width: 30px; vertical-align: middle;" xml:space="preserve"
                         >
                    <style type="text/css"> .st0-bd{fill:none;stroke:#1E1E1E;stroke-width:6;stroke-miterlimit:10;} .st1-bd{fill:#d9534f;} </style>
                    <g>
                    <g><path class="st0-bd" d="M2.102,8.442"></path></g>
                    <g><path class="st1-bd" d="M10.722,10c-0.824,0-1.495-0.671-1.495-1.495c0-0.824,0.671-1.495,1.495-1.495 c0.796,0,1.452,0.622,1.493,1.417c0.001,0.026,0.002,0.052,0.002,0.078C12.217,9.329,11.547,10,10.722,10z M10.722,7.618 c-0.489,0-0.887,0.398-0.887,0.887c0,0.489,0.398,0.887,0.887,0.887c0.489,0,0.887-0.398,0.887-0.887c0-0.016,0-0.031-0.001-0.047 C11.584,7.987,11.195,7.618,10.722,7.618z"></path></g>
                    <g><path class="st1-bd" d="M3.568,9.985c-0.824,0-1.495-0.671-1.495-1.495s0.671-1.495,1.495-1.495S5.063,7.666,5.063,8.49 S4.392,9.985,3.568,9.985z M3.568,7.603c-0.489,0-0.887,0.398-0.887,0.887c0,0.489,0.398,0.887,0.887,0.887 S4.455,8.979,4.455,8.49C4.455,8.001,4.057,7.603,3.568,7.603z"></path></g><g><path class="st0-bd" d="M9.143,2.441"></path></g>
                    <g><path class="st1-bd" d="M9.53,8.759C8.945,8.757,8.633,8.755,8.557,8.755H4.759V8.147l3.477,0V0.855c0-0.134-0.12-0.244-0.267-0.244 L1.223,0.608c-0.134,0-0.243,0.12-0.243,0.267l-0.002,7.02c0,0.134,0.12,0.243,0.267,0.243h1.134v0.608H1.244 c-0.482,0-0.874-0.382-0.874-0.851l0.002-7.02C0.372,0.392,0.754,0,1.224,0L7.97,0.004c0.482,0,0.874,0.382,0.874,0.851v7.293 C9.005,8.149,9.233,8.15,9.532,8.151L9.53,8.759z"></path></g>
                    <g><path class="st1-bd" d="M11.913,8.755l-0.002-0.608c0.541-0.001,0.917-0.005,0.981-0.008c0.1-0.005,0.125-0.063,0.13-0.101V5.271 l-1.829-2.418c-0.041-0.055-0.107-0.087-0.175-0.087H8.531V2.158h2.487c0.258,0,0.505,0.123,0.66,0.329l1.952,2.581l-0.001,3.007 c-0.019,0.256-0.222,0.646-0.704,0.672C12.826,8.751,12.32,8.753,11.913,8.755z"></path></g>
                    <g><path class="st1-bd" d="M13.326,5.95h-2.683C9.93,5.95,9.47,5.274,9.47,4.227c0-0.747,0.552-1.033,0.844-1.08l0.048-0.004h1.662 v0.608h-1.631c-0.091,0.023-0.316,0.117-0.316,0.476c0,0.114,0.016,1.115,0.566,1.115h2.683V5.95z"></path></g>
                    </g>
                    </svg>
                </span>
                <span style="margin-left: 5px;"><b><a href="/article/Delivery">Возможна доставка</a></b></span>
                {% if sluser %}
                    <br>
                    <a class="sluser_link_color" href="/article/up"><b>Какие объявления показываются выше других?</b></a>
                {% endif %}
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12 share-block clearfix">
                <h4>Поделиться:</h4>
                <a onclick="Share.vkontakte('{{currentGood.name}}','{{currentGood.getImage()}}','')" class="vkontakte" title="Вконтакте"></a>
                <a onclick="Share.facebook('{{currentGood.name}}','{{currentGood.getImage()}}','')" class="facebook" title="facebook"></a>
                <a onclick="Share.mailru('{{currentGood.name}}','{{currentGood.getImage()}}','')" class="mailru" title="Mailru"></a>
                <a onclick="Share.odnoklassniki('{{currentGood.name}}')" class="odnoklassniki" title="Одноклассники"></a>
                <a onclick="Share.twitter('{{currentGood.name}}')" class="twitter" title="twitter"></a>
                <a onclick="Share.google()" class="google" title="Google Plus"></a>
            </div>

            {% if sluser == currentGood.organization and ( (currentGood.getImageType()=='none') and !currentGood.features ) %}
                <div class="col-md-12 col-sm-12 col-xs-12 sluser-info-block clearfix">
                    <font color="#b74a33">
                    Информация для продавца: Вы можете продать этот товар намного быстрее.
                    Сейчас вы не используете все возможности интернет-магазина.
                    Добавьте фотографию, описание и цену товара и число просмотров (а значит и скорость продажи) резко увеличится.
                    </font>
                </div>
            {% endif %}
        </div>
    </div>
</div>

<br/>
<div class="row" id="relatedblock">
    <div class="col-md-12">
        <div class="related-block">
            <h4>Похожие товары</h4>
            {% include "partials/related_goods.volt" %}
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        $(document).on('click', '.get-phone', function(e) {
            const $this = $(this);

            try {
                yaCounter21293509.reachGoal('click_show_me_phone');
            } catch (e) {
                console.error(e);
            }

            $.ajax({
                url: '/api/need-phone',
                data: {
                    good: {{currentGood.id}}
                },
                type: "POST",
                dataType: "json",
                success: function (response) {
                    var new_content = '<button class="btn call-phone-button">'
                            + '<a href="tel:'+ response.phone +'" target="_blank">' + response.phone + '</a>'
                            + '</button>';

                    $this.replaceWith(new_content);
                }
            });
        });

        $('.get-description').click(function (e) {
            e.preventDefault();
            $('.gdf-hidden').hide();

            try {
                yaCounter21293509.reachGoal('request_info');
            } catch (e) {
                console.error(e);
            }

            $.fancybox({
                autoSize: true,
                closeBtn: true,
                content: $('.get-description-form'),
                scrolling: 'no',
            });
        });

        $('.get-price').click(function (e) {
            e.preventDefault();
            $('.gdf-hidden').hide();

            try {
                yaCounter21293509.reachGoal('request_price');
            } catch (e) {
                console.error(e);
            }

            $.fancybox({
                autoSize: true,
                closeBtn: true,
                content: $('.get-price-form'),
                scrolling: 'no',
            });
        });

        $('.get-call').click(function (e) {
            e.preventDefault();
            $('.gdf-hidden').hide();

            if ($(this).hasClass('disabled')) {
                alert('Заявка на обратный звонок уже отправлена');
            } else {
                try {
                    yaCounter21293509.reachGoal('request_call');
                } catch (e) {
                    console.error(e);
                }

                $.fancybox({
                    autoSize: true,
                    closeBtn: true,
                    content: $('.get-call-form'),
                    scrolling: 'no',
                });
            }
        });

        $('.get-photo').click(function (e) {
            e.preventDefault();
            $('.gdf-hidden').hide();

            try {
                yaCounter21293509.reachGoal('request_photo');
            } catch (e) {
                console.error(e);
            }

            $.fancybox({
                autoSize: true,
                closeBtn: true,
                content: $('.get-photo-form'),
                scrolling: 'no',
            });
        });

        $(document).on('change', '.consent-personal-data', function (e) {
            var $btnSuccess = $(this).closest('.form-group').find('.btn-success');

            if ($(this).is(':checked')) {
                $btnSuccess.removeClass('disabled');
                $btnSuccess.removeAttr('disabled');
            } else {
                $btnSuccess.addClass('disabled');
                $btnSuccess.attr('disabled', 'disabled');
            }
        });
    });
</script>

<div class="get-description-form">
    <div class="form-header-big"><strong>Задайте нам вопрос по товару</strong></div>
    <div>И мы вам ответим :)</div>
    <div class="text-danger">* Обязательно</div>
    <br>
    <form class="form-horizontal" id="need-answer-about-good" data-submit-hidden="0">

        <div class="form-group">
            <label for="inputNameDescription" class="col-sm-3 control-label">
                Имя
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="inputNameDescription" name="name" placeholder="Ваше имя">
            </div>
        </div>

        <div class="form-group">
            <label for="inputContactsDescription" class="col-sm-3 control-label">
                Контактные данные <span class="text-red">*</span>
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="inputContactsDescription" name="contacts" placeholder="Ваш телефон или e-mail" data-error="Введите ваши контактные данные" required>
                <div class="help-block with-errors"></div>
            </div>
        </div>

        <div class="form-group">
            <label for="inputQuestionText" class="col-sm-3 control-label">
                Текст вопроса <span class="text-red">*</span>
            </label>
            <div class="col-sm-9">
                <textarea rows="7" class="form-control" id="inputQuestionText" name="question_text" placeholder="Текст вопроса" data-error="Введите текст вопроса" required></textarea>
                <div class="help-block with-errors"></div>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox">
                <div class="col-sm-1">
                </div>
                <div class="col-sm-11">
                    <label class="form-check-label" for="description-form-agreement"><input class="form-check-input" type="checkbox" value="" id="description-form-agreement" required>&nbsp;Нажимая кнопку "Отправить", вы соглашаетесь с условиями обработки и хранения персональных данных</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-8">
                <button type="submit" class="btn btn-success">Отправить</button>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(function () {
            $('#need-answer-about-good').validator({focus: false}).on('submit', function (e) {
                if (!e.isDefaultPrevented()) {
                    e.preventDefault();
                    var check_extra_field = Boolean($('#inputNameDescription').val() || $('#inputContactsDescription').val())

                    $.ajax(
                        '/api/need-answer-about-good', {
                            data: {
                                name: $('#inputNameDescription').val(),
                                question_text: $('#inputQuestionText').val(),
                                contacts: $('#inputContactsDescription').val(),
                                good: {{ currentGood.id }}
                            },
                            type: "POST",
                            dataType: "json",
                            success: function (response) {
                                if (response.status) {
                                    try {
                                        if (check_extra_field) {
                                            yaCounter21293509.reachGoal('request_info2');
                                            yaCounter21293509.reachGoal('request_info3');
                                        } else {
                                            yaCounter21293509.reachGoal('request_info2');
                                        }
                                    } catch (e) {
                                        console.error(e);
                                    }

                                    Swal.fire({title: 'Запрос отправлен!', icon: 'success'}).then(function () {
                                        $.fancybox.close();
                                    });
                                } else {
                                    Swal.fire({title: response.message, icon: 'error'}).then(function () {
                                        $.fancybox.close();
                                    });
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                Swal.fire({title: 'К сожалению во время выполнения вашего запроса произошла ошибка. ' +
                                    'Если ошибка повторяется обратитесь в техническую поддержку.', icon: 'error'})
                                .then(function () {
                                    $.fancybox.close();
                                });

                                console.error('AJAX запрос завершился с ошибкой. Ответ: ', jqXHR);
                                console.error('Тип ошибки: ', textStatus);
                                console.error('Описание ошибки: ', errorThrown);
                            }
                        }
                    );
                }
            });
        });
    </script>
</div>

<div class="get-price-form">
    <h4>Мы сами с вами свяжемся и все расскажем</h4>
    <br>
    <form class="form-horizontal" id="need-price" data-submit-hidden="0">

        <div class="form-group">
            <label for="inputPhonePrice" class="col-sm-3 control-label">
                Телефон <span class="text-red">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="inputPhonePrice" placeholder="Пример: +7 123 4567890, 81234567890" name="phone">
                <span class="help-block">
                    Телефон необходим для связи продавца с вами.
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-8">
                <button type="submit" class="btn btn-success btn-price-more-fields">Жду звонка</button>
            </div>
        </div>

        <div class="gdf-hidden">
            <br/>
            <div class="form-group">
                <label for="inputNamePrice" class="col-sm-3 control-label">Имя</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="inputNamePrice" placeholder="Пример: Василий" name="name">
                    <span class="help-block">
                        Представьтесь, пожалуйста, чтобы мы знали как к вам обращаться.
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmailPrice" class="col-sm-3 control-label">Электронная почта</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="inputEmailPrice" name="email" placeholder="Пример: vasha@pochta.ru">
                    <span class="help-block">
                        Сюда мы можем отправить вам запрашиваемую информацию.
                    </span>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-12" style="margin-bottom: 10px;">
                    <input type="checkbox" class="consent-personal-data" name="consent_personal_data" style="margin-right: 5px;">Нажимая кнопку "Отправить", вы соглашаетесь с условиями обработки и хранения персональных данных
                </div>

                <div class="col-sm-offset-3 col-sm-8">
                    <button type="submit" class="btn btn-success disabled">Отправить</button>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript">

        $('.btn-price-more-fields').click(function (e) {
            e.preventDefault();
            var need_price = $('#need-price');

            $.fancybox({
                width: 550,
                maxHeight: 470,
                autoSize: false,
                closeBtn: true,
                openEffect: 'none',
                content: $('.get-price-form')
            });

            $('.gdf-hidden').show();

            $('.fancybox-close, .fancybox-overlay').click(function() {
                if (need_price.find('[name="phone"]').val()) {
                    need_price.data('submit-hidden', 1);
                    need_price.submit();
                }
            });
        });

        $('#need-price').submit(function (e) {
            e.preventDefault();
            var submit_hidden = $('#need-price').data('submit-hidden');
            var check_extra_field = Boolean($('#inputNamePrice').val() || $('#inputEmailPrice').val())

            $.ajax(
                '/api/need-price', {
                    data: {
                        name: $('#inputNamePrice').val(),
                        phone: $('#inputPhonePrice').val(),
                        email: $('#inputEmailPrice').val(),
                        good: {{ currentGood.id }}
                    },
                    type: "POST",
                    dataType: "json",
                    success: function (response) {
                        if (response.status) {
                            try {
                                if (check_extra_field) {
                                    yaCounter21293509.reachGoal('request_price2');
                                    yaCounter21293509.reachGoal('request_price3');
                                } else {
                                    yaCounter21293509.reachGoal('request_price2');
                                }
                            } catch (e) {
                                console.error(e);
                            }

                            alert('Сообщение отправлено продавцу.');
                        } else {
                            alert(response.message);
                        }

                        $.fancybox.close();

                        $('#need-price').data('submit-hidden', 0);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $.fancybox.close();
                        alert('К сожалению во время выполнения вашего запроса произошла ошибка. Если ошибка повторяется обратитесь в техническую поддержку.');

                        console.error('AJAX запрос завершился с ошибкой. Ответ: ', jqXHR);
                        console.error('Тип ошибки: ', textStatus);
                        console.error('Описание ошибки: ', errorThrown);
                    }
                }
            );
        });
    </script>
</div>

<div class="get-call-form">
    <div class="form-header-big"><strong>Оставьте ваши контактные данные</strong></div>
    <div>И мы вам перезвоним :)</div>
    <div class="text-danger">* Обязательно</div>
    <br>
    <form class="form-horizontal" id="need-call">

        <div class="form-group">
            <label for="inputPhoneCall" class="col-sm-3 control-label">
                Телефон <span class="text-red">*</span>
            </label>
            <div class="col-sm-9">
                <input type="tel" pattern="^(\+)?[0-9]+$" class="form-control" id="inputPhoneCall" name="phone" placeholder="+79123456789" data-error="Введите телефон" required>
                <div class="help-block with-errors"></div>
            </div>
        </div>

        <div class="form-group">
            <label for="inputNameCall" class="col-sm-3 control-label">
                Имя
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="inputNameCall" name="name" placeholder="Как к вам обратиться?">
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox">
                <div class="col-sm-1">
                </div>
                <div class="col-sm-11">
                    <label class="form-check-label" for="get-call-form-agreement"><input class="form-check-input" type="checkbox" value="" id="get-call-form-agreement" required>&nbsp;Нажимая кнопку "Отправить", вы соглашаетесь с условиями обработки и хранения персональных данных</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-8">
                <button type="submit" class="btn btn-success">Отправить</button>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(function () {
            $('#need-call').validator({focus: false}).on('submit', function (e) {
                if (!e.isDefaultPrevented()) {
                    e.preventDefault();

                    var check_extra_field = Boolean($('#inputNameCall').val() || $('#inputPhoneCall').val());
                    var $get_call = $(document).find('.get-call');

                    $.ajax(
                        '/api/need-call', {
                            data: {
                                name: $('#inputNameCall').val(),
                                phone: $('#inputPhoneCall').val(),
                                good: {{ currentGood.id }}
                            },
                            type: "POST",
                            dataType: "json",
                            success: function (response) {
                                if (response.status) {
                                    try {
                                        if (check_extra_field) {
                                            yaCounter21293509.reachGoal('request_call2');
                                            yaCounter21293509.reachGoal('request_call3');
                                        } else {
                                            yaCounter21293509.reachGoal('request_call2');
                                        }
                                    } catch (e) {
                                        console.error(e);
                                    }

                                    $get_call.addClass('disabled');

                                    Swal.fire({title: 'Запрос отправлен!', icon: 'success'}).then(function () {
                                        $.fancybox.close();
                                    });
                                } else {
                                    Swal.fire({title: response.message, icon: 'error'}).then(function () {
                                        $.fancybox.close();
                                    });
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                Swal.fire({
                                    title: 'К сожалению во время выполнения вашего запроса произошла ошибка. ' +
                                        'Если ошибка повторяется обратитесь в техническую поддержку.', icon: 'error'
                                })
                                    .then(function () {
                                        $.fancybox.close();
                                    });

                                console.error('AJAX запрос завершился с ошибкой. Ответ: ', jqXHR);
                                console.error('Тип ошибки: ', textStatus);
                                console.error('Описание ошибки: ', errorThrown);
                            }
                        }
                    );
                }
            });
        });
    </script>
</div>

<div class="get-photo-form">
    <div class="form-header-big"><strong>Оставьте ваши контактные данные</strong></div>
    <div>И мы отправим вам фото товара :)</div>
    <div class="text-danger">* Обязательно</div>
    <br>
    <form class="form-horizontal" id="need-photo" data-submit-hidden="0">

        <div class="form-group">
            <label for="inputEmailPhoto" class="col-sm-3 control-label">
                Электронная почта <span class="text-red">*</span>
            </label>
            <div class="col-sm-9">
                <input type="email" pattern="^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([A-Za-z]{2,6}(?:\.[A-Za-z]{2})?)$" class="form-control" id="inputEmailPhoto" name="email" placeholder="email@mail.ru" data-error="Введите электронную почту" required>
                <div class="help-block with-errors"></div>
            </div>
        </div>

        <div class="form-group">
            <label for="inputNamePhoto" class="col-sm-3 control-label">
                Имя
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="inputNamePhoto" name="name" placeholder="Как к вам обратиться?">
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox">
                <div class="col-sm-1">
                </div>
                <div class="col-sm-11">
                    <label class="form-check-label" for="get-photo-form-agreement"><input class="form-check-input" type="checkbox" value="" id="get-photo-form-agreement" required>&nbsp;Нажимая кнопку "Отправить", вы соглашаетесь с условиями обработки и хранения персональных данных</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-8">
                <button type="submit" class="btn btn-success">Отправить</button>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(function () {
            $('#need-photo').validator({focus: false}).on('submit', function (e) {
                if (!e.isDefaultPrevented()) {
                    e.preventDefault();
                    // валидация пройдена

                    var submit_hidden = $('#need-photo').data('submit-hidden'),
                        check_extra_field = Boolean($('#inputNamePhoto').val() || $('#inputEmailPhoto').val());

                    $.ajax(
                        '/api/need-photo', {
                            data: {
                                name: $('#inputNamePhoto').val(),
                                email: $('#inputEmailPhoto').val(),
                                good: {{ currentGood.id }}
                            },
                            type: "POST",
                            dataType: "json",
                            success: function (response) {
                                if (response.status) {
                                    try {
                                        if (check_extra_field) {
                                            yaCounter21293509.reachGoal('request_photo2');
                                            yaCounter21293509.reachGoal('request_photo3');
                                        } else {
                                            yaCounter21293509.reachGoal('request_photo2');
                                        }
                                    } catch (e) {
                                        console.error(e);
                                    }

                                    Swal.fire({title: 'Запрос отправлен!', icon: 'success'}).then(function () {
                                        $.fancybox.close();
                                    });
                                } else {
                                    Swal.fire({title: response.message, icon: 'error'}).then(function () {
                                        $.fancybox.close();
                                    });
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                Swal.fire({title: 'К сожалению во время выполнения вашего запроса произошла ошибка. ' +
                                    'Если ошибка повторяется обратитесь в техническую поддержку.', icon: 'error'})
                                .then(function () {
                                    $.fancybox.close();
                                });

                                console.error('AJAX запрос завершился с ошибкой. Ответ: ', jqXHR);
                                console.error('Тип ошибки: ', textStatus);
                                console.error('Описание ошибки: ', errorThrown);
                            }
                        }
                    );
                }
            })
        });
    </script>
</div>
