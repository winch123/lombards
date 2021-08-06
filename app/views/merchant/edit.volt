<div class="page-header">
    <h1>Обновить информацию о ломбарде</h1>
</div>

<p>
    Мы обновим/уточним информацию по первому запросу,
    но нам необходимо убедиться что вы действительно имеете отношение к данному ломбарду.
    Заполните форму ниже, мы проверим введенную информацию.
    После проверки мы свяжемся с вами и актуализируем информацию.
    Запрашиваемую информацию мы не передаем третьим лицам и не публикуем на сайте.
    Она нужна лишь для проверки.
</p>

<br><br>
<h4>Шаг 1</h4>
<p>Давайте убедимся, что вы действительно имеете отношение к этому ломбарду.</p>
<br><br>

<div class="row">
    <div class="col-md-9">
    <form class="form-horizontal" id="add-merchant-1">
        <div class="form-group">
            <label for="inputName" class="col-sm-3 control-label">
                ФИО руководителя <span class="text-red">*</span></label>
            <div class="col-sm-6">
                <input type="text" class="form-control"
                    id="inputName" placeholder="Пример: Иванов Иван Иванович" name="name">
                <span class="help-block" id="input-name-help">
                    Укажите имя руководителя ломбарда.
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="inputEmail" class="col-sm-3 control-label">
                Электронная почта руководителя <span class="text-red">*</span></label>
            <div class="col-sm-6">
                <span class="help-block" id="input-email-help">
                    Укажите электронную почту руководителя ломбарда.
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="inputPhone" class="col-sm-3 control-label">
                Контактный телефон руководителя <span class="text-red">*</span></label>
            <div class="col-sm-6">
                <input type="text" class="form-control"
                    id="inputPhone" placeholder="Пример: +7 123 4567890, 81234567890" name="phone">
                <span class="help-block" id="input-phone-help">
                    Укажите контактный телефон руководителя.
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-8">
                <button type="submit" class="btn btn-success" id="sendButton">Отправить</button>
            </div>
        </div>
    </form>
    </div>
</div>

<script type="text/javascript">
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
            fields = ['name', 'phone', 'email'];

        $.ajax(
            '/api/edit-merchant-step-1', {
                data: {
                    name: $('#inputName').val(),
                    phone: $('#inputPhone').val(),
                    email: $('#inputEmail').val(),
                    city: "{{cityName}}",
                    id: {{id}},
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
                    } else if (result && result.success) {
                        $('.form-control').parent().addClass('has-success');
                        $('.help-block').html('&nbsp;');
                        $('#sendButton').prop('disabled', false);

                        if (submit == 1) {
                            try {
                                yaCounter21293509.reachGoal('EditMerchantSubmitPrivateInfo');
                            } catch (e) {
                                console.error(e);
                            }

                            window.location.href = result.success;
                        }
                    }
                }
            }
        );
    }
</script>
