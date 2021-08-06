<div class="page-header">
    <h4>Редактирование мерчанта <small>(<a href="{{merchant.getUrl()}}" target="_blank">Страница мерчанта</a>) </small></h4>
</div>

<div class="row">
    <div class="col-md-8">
        <form class="form-horizontal" id="add-merchant">

            <div class="form-group">
                <label for="inputNew" class="col-sm-4 control-label">Промодерирован</label>
                <label class="col-sm-7">
                    <input type="checkbox" name="new" id="inputNew"
                        {% if not merchant.new %}checked{% endif %}>
                </label>
            </div>

            <div class="form-group">
                <label for="inputDeleted" class="col-sm-4 control-label">Отображать на сайте</label>
                <label class="col-sm-7">
                    <input type="checkbox" name="deleted" id="inputDeleted"
                        {% if not merchant.deleted %}checked{% endif %}>
                </label>
            </div>

            <script type="text/javascript">
                $(document).ready(function() {
                    $('input[type="checkbox"]').trigger('change');
                });

                $('input[type="checkbox"]').change(function() {
                    if ($(this).is(':checked')) {
                        $(this).parent().removeClass('alert-danger').addClass('alert-success');
                    } else {
                        $(this).parent().removeClass('alert-success').addClass('alert-danger');
                    }
                });
            </script>

            <div class="form-group">
                <label for="inputName" class="col-sm-4 control-label">Название</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="inputName"
                        name="name"
                        placeholder="Например: Ломбард бла бла"
                        value="{{ merchant.name | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputCity" class="col-sm-4 control-label">Город</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="inputCity"
                        name="city"
                        placeholder="Например: Тольятти"
                        value="{{ merchant.city | e }}">
                    <span id="inputCityHelp" class="help-block">Введите название города,
                    если такого города в базе не существует, этот город создаётся автоматически.</span>
                </div>
            </div>

            <div class="form-group">
                <label for="inputAddress" class="col-sm-4 control-label">Адрес</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="inputAddress"
                        name="address"
                        placeholder="Например: ул. Спортивная 23"
                        value="{{ merchant.address | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputPhone" class="col-sm-4 control-label">Телефон</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="inputPhone"
                        name="phone"
                        placeholder="Например: +7 919 810 27 98"
                        value="{{ merchant.phone | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputSite" class="col-sm-4 control-label">Ссылка на сайт</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="inputSite"
                        name="site"
                        placeholder="Например: https://smartlombard.ru"
                        value="{{ merchant.site | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputDescription" class="col-sm-4 control-label">Описание</label>
                <div class="col-sm-7">
                    <textarea rows="5" class="form-control" id="inputDescription"
                        name="description">{{ merchant.description | e }}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="inputShortlink" class="col-sm-4 control-label">Короткая ссылка</label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="inputShortlink"
                        name="shortlink"
                        placeholder="Например: ЛомбардКарат"
                        value="{{ merchant.shortlink | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputWorkingHours" class="col-sm-4 control-label">Режим работы</label>
                <div class="col-sm-7">
                    <textarea rows="5" class="form-control" id="inputWorkingHours"
                        name="working_hours">{{ merchant.working_hours | e }}</textarea>
                </div>
            </div>

            <script type="text/javascript">
                $('#inputShortlink').on('input', function (event) {
                    $('.shortlink-example').text($(this).val());
                });
            </script>

            <div class="form-group">
                <label for="inputFile" class="col-sm-4 control-label">
                    Фотография или логотип ломбарда </label>
                <div class="col-sm-7">
                    <div class="uploaded-image-container">
                        {% if merchant.logo %}
                            <img src="{{ merchant.getImagePreview() }}" alt><br>
                        {% else %}
                            <img src="/static/img/noimage.gif" alt><br>
                        {% endif %}
                    </div>

                    <input type="hidden" name="filename" id="inputFile" value="{{ merchant.logo }}">

                    {% if merchant.custom %}
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
                    </script>
                    {% else %}
                        <span class="help-block" id="input-file-help">
                            Ломбард загружен из smartlombard.ru (картинка подставляется автоматически)
                        </span>
                    {% endif %}
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-offset-2 col-sm-7">
                    <button type="submit" class="btn btn-success">Сохранить</button>
                </div>
            </div>
        </form>

        {% if merchant.custom %}
            <div class="row">
                <div class="col-md-offset-2 col-sm-7">
                    <button id="remove-merchant" class="btn btn-sm btn-danger">Удалить</button>
                </div>
            </div>
        {% endif %}
    </div>
    <div class="col-md-3">
        {% if not merchant.custom %}
            <a target="_blank" href="//{{config.smartlombard.domain}}/management/?place=panel_profile_new&view_id={{merchant.organization}}">
                Профиль на {{config.smartlombard.domain}}
            </a>
        {% endif %}

        <h4>Теги:</h4>
        <ul class="list-group">
            {% if tags is defined %}
                {% for merchantTag in tags %}
                    <li class="list-group-item">
                        <a href="/admin/tags/edit{{merchantTag.id}}">{{merchantTag.name}}</a>
                        <a class="remove-city remove-city-icon"
                            href="/admin/api/tagsRemoveFromMerchant"
                            data-merchant-id="{{merchant.id}}"
                            data-tag-id="{{merchantTag.id}}"
                            title="Удалить тег">
                            <i class="glyphicon glyphicon-remove"></i></a>
                    </li>
                {% endfor %}
            {% endif %}
        </ul>

        <input type="hidden" value="{{merchant.id}}" class="admin-add-tag-merchant-value">
        <div class="input-group input-group-sm">
            <select class="form-control admin-add-tag-value">
                {% for allTag in allTags %}
                    <option value="{{allTag.id}}">{{allTag.name}}</option>
                {% endfor %}
            </select>
            <span class="input-group-btn">
                <button class="btn btn-success admin-add-tag">Добавить тег</button>
            </span>
        </div>

        {% if parents | length %}
        <h4>Версии мерчанта:</h4>
        <div class="list-group">
            {% for parent in parents %}
                <a href="/admin/merchants/edit{{parent.id}}" class="list-group-item{% if merchant.id == parent.id%} active{% endif %}">
                    {{parent.name}} <br>
                    <span>{{parent.added}}</span>
                </a>
            {% endfor %}
        </div>
        {% endif %}
    </div>
</div>

<script type="text/javascript">
    $('#remove-merchant').on('click', function (event) {
        $.ajax(
            '/admin/api/merchantEditRemove', {
                data: {
                    id: {{ merchant.id }}
                },
                type: "POST",
                dataType: "json",
                success: function (result) {
                    if (result && result.success) {
                        window.location.href = '/admin/merchants/';
                    } else {
                        alert(result.errors);
                    }
                }
            }
        );
    });

    $('#add-merchant').on('submit', function (event) {
        validateForm(event);
    });

    function validateForm (event) {
        event.preventDefault();

        $.ajax(
            '/admin/api/merchantEditSave', {
                data: {
                    name: $('#inputName').val(),
                    city: $('#inputCity').val(),
                    address: $('#inputAddress').val(),
                    phone: $('#inputPhone').val(),
                    site: $('#inputSite').val(),
                    description: $('#inputDescription').val(),
                    working_hours: $('#inputWorkingHours').val(),
                    filename: $('#inputFile').val(),
                    shortlink: $('#inputShortlink').val(),
                    deleted: (!$('#inputDeleted').is(':checked')) ? 1 : 0,
                    new: (!$('#inputNew').is(':checked')) ? 1 : 0,
                    merchant: {{ merchant.id }}
                },
                type: "POST",
                dataType: "json",
                success: function (result) {
                    if (result && result.success) {
                        window.location.reload();
                    } else {
                        alert(result.errors);
                    }
                }
            }
        );
    }
</script>