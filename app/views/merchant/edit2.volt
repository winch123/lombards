<div class="page-header">
    <h1>Обновить ломбард <small>({{merchant.name | e}})</small></h1>
</div>

<!-- <h4>Шаг 2</h4> -->
<p>Теперь необходимо заполнить данные ломбарда.</p>
<br><br>

<div class="row">
    <div class="col-md-9">
    <form class="form-horizontal" id="add-merchant-1">
        <div class="form-group">
            <label for="inputName" class="col-sm-3 control-label">
                Название <span class="text-red">*</span></label>
            <div class="col-sm-6">
                <input type="text" class="form-control"
                    id="inputName" placeholder="Пример: Лучший ломбард" name="name" value="{{merchant.name | e}}">
                <span class="help-block" id="input-name-help">
                    Укажите название ломбарда.
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="inputAdress" class="col-sm-3 control-label">
                Адрес <span class="text-red">*</span></label>
            <div class="col-sm-6">
                <input type="text" class="form-control"
                    id="inputAdress" placeholder="Пример: ул. Мира, строение 6, офис 5" name="adress" value="{{merchant.address | e}}">
                <span class="help-block" id="input-adress-help">
                    Укажите адрес ломбарда.
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="inputPhone" class="col-sm-3 control-label">
                Телефон <span class="text-red">*</span></label>
            <div class="col-sm-6">
                <input type="text" class="form-control"
                    id="inputPhone" placeholder="Пример: +7 123 4567890, 81234567890" name="phone"  value="{{merchant.phone | e}}">
                <span class="help-block" id="input-phone-help">
                    Укажите телефон по которому можно связаться с администрацией ломбарда.
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="inputSite" class="col-sm-3 control-label">
                Сайт </label>
            <div class="col-sm-6">
                <input type="text" class="form-control"
                    id="inputSite" placeholder="Пример: http://www.site.ru" name="site"  value="{{merchant.site | e}}">
                <span class="help-block" id="input-site-help">
                    Укажите адрес вашего сайта, если он имеется.
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="inputDescription" class="col-sm-3 control-label">
                Описание </label>
            <div class="col-sm-6">
                <textarea class="form-control"
                    id="inputDescription" placeholder="Пример: Здесь напишите про свой ломбард"
                    name="description">{{merchant.description | e}}</textarea>
                <span class="help-block" id="input-description-help">
                    Опишите свой ломбард.
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="inputWorkingHours" class="col-sm-3 control-label">
                Режим работы </label>
            <div class="col-sm-6">
                <textarea class="form-control"
                    id="inputWorkingHours" placeholder="Пример: Укажите режим работы"
                    name="working_hours">{{merchant.working_hours | e}}</textarea>
                <span class="help-block" id="input-working_hours-help">
                    Укажите режим работы.
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="inputFile" class="col-sm-3 control-label">
                Возможные теги</label>
            <div class="col-sm-6">
                <div class="list-group">
                    {% for aTag in tags %}
                        <label class="list-group-item">
                            <input type="checkbox"
                                class="tag-input"
                                data-tag-id="{{aTag['tag'].id}}"
                                {% if aTag['checked'] %}checked{% endif %}> {{aTag['tag'].name}}
                        </label>
                    {% endfor %}
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function() {
                $('input[type="checkbox"]').trigger('change');
            });

            $(document).on('change', 'input[type="checkbox"]', function(event) {
                if ($(this).is(':checked')) {
                    $(this).parent().addClass('active');
                } else {
                    $(this).parent().removeClass('active');
                }
            });
        </script>

        <div class="form-group">
            <label for="inputFile" class="col-sm-3 control-label">
                Фотография или логотип ломбарда </label>
            <div class="col-sm-6">
                <div class="uploaded-image-container">
                    {% if merchant.getImage() %}
                        <img src="{{merchant.getImage()}}" alt><br>
                    {% else %}
                        <img src="/static/img/noimage.gif" alt><br>
                    {% endif %}
                </div>
                <span class="btn btn-sm btn-default fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    Прикрепить изображение
                    <input id="fileupload" type="file" name="files[]" data-url="/api/add-merchant-image" multiple>
                </span>
                <script src="/js/jquery.ui.widget.js"></script>
                <script src="/js/jquery.iframe-transport.js"></script>
                <script src="/js/jquery.fileupload.js"></script>
                <span class="help-block" id="input-file-help">
                    Максимальный размер изображения не должен превышать 10 MB (jpg. gif. png)
                </span>
            </div>
        </div>

        <input type="hidden" name="filename" id="inputFile">

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-8">
                <button type="submit" class="btn btn-success" id="sendButton">Отправить</button>
            </div>
        </div>
    </form>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('#fileupload').fileupload({
            dataType: 'json',
            done: function (e, data) {
                if (data.result && data.result.success) {
                    $('#inputFile').val(data.result.success.name);
                    $('.uploaded-image-container').html('<img src="'+data.result.success.src+'">');
                }
            }
        });
    });

    $('#sendButton').prop('disabled', true);

    $('.form-control').on('blur', function (event) {
        validateForm(event);
    });

    $('#add-merchant-1').on('submit', function (event) {
        validateForm(event);
    });

    function validateForm (event) {
        event.preventDefault();

        var submit = (! $(event.target).hasClass('form-control')) - 0,
            fields = ['name', 'phone', 'adress'];

        var tags = [];

        $('.tag-input').each(function (key, element) {
            var $element = $(element);

            if ($element.is(':checked')) {
                tags.push(parseInt($element.data('tag-id')));
            }
        });

        $.ajax(
            '/api/add-merchant-step-2', {
                data: {
                    name: $('#inputName').val(),
                    phone: $('#inputPhone').val(),
                    adress: $('#inputAdress').val(),
                    site: $('#inputSite').val(),
                    description: $('#inputDescription').val(),
                    working_hours: $('#inputWorkingHours').val(),
                    filename: $('#inputFile').val(),
                    city: "{{currentCity.name}}",
                    parent: {{merchant.id}},
                    tags: tags,
                    submit: submit
                },
                type: "POST",
                dataType: "json",
                success: function (result) {
                    $('.has-error').removeClass('has-error');

                    if (result && result.errors) {

                        $('#sendButton').prop('disabled', true);

                        for (y in fields) {
                            var error = false;
                            for (x in result.errors) {
                                if (fields[y] == result.errors[x].field) {
                                    error = true;

                                    if (! $('#input-' + result.errors[x].field + '-help').parent().hasClass('has-error')) {
                                        $('#input-' + result.errors[x].field + '-help').text(result.errors[x].message);
                                        $('#input-' + result.errors[x].field + '-help').parent().addClass('has-error');
                                    }
                                }
                            }

                            if (! error) {
                                $('#input-' + fields[y] + '-help').html('&nbsp;')
                                    .parent().addClass('has-success');
                            }
                        }

                        for (x in result.errors) {
                            if (! $('#input-' + result.errors[x].field + '-help').parent().hasClass('has-error')) {
                                $('#input-' + result.errors[x].field + '-help').text(result.errors[x].message);
                                $('#input-' + result.errors[x].field + '-help').parent().addClass('has-error');
                            }
                        }
                    } else if (result) {
                        $('.form-control').parent().addClass('has-success');
                        $('.help-block').html('&nbsp;');
                        $('#sendButton').prop('disabled', false);

                        if (submit == 1) {
                            try {
                                yaCounter21293509.reachGoal('EditMerchantSubmitLombardInfo');
                            } catch (e) {
                                console.error(e);
                            }

                            alert('Ваша заявка принята и будет добавлена после модерации');
                            window.location.href = '/';
                        }
                    }
                }
            }
        );
    }
</script>
