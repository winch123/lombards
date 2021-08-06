<div class="page-header">
    <h4>
        Ошибка у мерчанта {{ error.merchant_name }}
        {% if merchant.id %}
            <small>(<a href="{{ merchant.getUrl() }}" target="_blank">Страница мерчанта</a>)</small>
        {% endif %}
    </h4>
</div>

<div class="row">
    <div class="col-md-8">
        <form class="form-horizontal" id="add-merchant">

            <div class="form-group">
                <label for="inputNew" class="col-sm-4 control-label">Организация закрылась</label>
                <label class="col-sm-7">
                    <input type="checkbox" name="close" id="inputClose" {% if error.closed %}disabled{% endif %}
                        {% if error.merchant_close %}checked{% endif %}>
                </label>
            </div>

            <div class="form-group">
                <label for="inputDeleted" class="col-sm-4 control-label">Неправильный адрес организации</label>
                <label class="col-sm-7">
                    <input type="checkbox" name="bad_adress" id="inputBadAdress" {% if error.closed %}disabled{% endif %}
                        {% if error.merchant_bad_adress %}checked{% endif %}>
                </label>
            </div>

            <div class="form-group">
                <label for="inputDeleted" class="col-sm-4 control-label">Не работает телефон организации</label>
                <label class="col-sm-7">
                    <input type="checkbox" name="phone_dntwork" id="inputPhoneDntwork" {% if error.closed %}disabled{% endif %}
                        {% if error.merchant_phone_dntwork %}checked{% endif %}>
                </label>
            </div>

            <script type="text/javascript">
                $(document).ready(function() {
                    $('input[type="checkbox"]').trigger('change');
                });

                $('input[type="checkbox"]').change(function() {
                    if ($(this).is(':checked')) {
                        $(this).parent().removeClass('alert-danger').addClass('alert-danger');
                    } else {
                        $(this).parent().removeClass('alert-success').addClass('alert-success');
                    }
                });
            </script>

            <div class="form-group">
                <label for="inputDescription" class="col-sm-4 control-label">Подробнее</label>
                <div class="col-sm-7">
                    <textarea rows="5" class="form-control" id="inputDescription" {% if error.closed %}disabled{% endif %}
                        name="description">{{ error.more | e }}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label for="inputDescription" class="col-sm-4 control-label">Комментарий к закрытию ошибки. Заполнить обязательно! <font color="red">*</font></label>
                <div class="col-sm-7">
                    <textarea rows="5" class="form-control" id="comment" name="description" {% if error.closed %}disabled{% endif %}>{{ error.comment | e }}</textarea>
                </div>
            </div>


            <div class="form-group">
                <div class="col-md-offset-2 col-sm-7">
                    <button class="btn btn-success" id="close_error_report" {% if error.closed %}style="display: none;"{% endif %} disabled>Закрыть ошибку</button>
                </div>
            </div>
        </form>

        {% if merchant.custom %}
            <div class="row">
                <div class="col-md-offset-2 col-sm-7">
                    <button id="remove-merchant" class="btn btn-sm btn-danger" {% if error.closed %}style="display: none;"{% endif %} disabled>Закрыть ошибку и удалить мерчанта</button>
                </div>
            </div>
        {% endif %}
    </div>

</div>

<script type="text/javascript">
    $('#comment').on('keyup', function () {
       if ($(this).val() != ''){
           $('#close_error_report').prop('disabled','');
           $('#remove-merchant').prop('disabled','');
       } else {
           $('#close_error_report').prop('disabled','disabled');
           $('#remove-merchant').prop('disabled','disabled');
       }
    });

    $('#close_error_report').on('click', function (event) {
        if ($('#comment').val() == '') {
            alert('Вы не заполнили комментарий к ошибке');
        } else {
            $.ajax(
                '/admin/api/errorReportClose', {
                    data: {
                        error_id : {{ error.id }},
                        comment : $('#comment').val()
                    },
                    type: "POST",
                    dataType: "json",
                    success: function (result) {
                        if (result && result.success) {
                            window.location.href = '/admin/';
                        } else {
                            alert(result.errors);
                        }
                    }
                }
            );
        }

        return false;
    });

    $('#remove-merchant').on('click', function (event) {
        $.ajax(
            '/admin/api/merchantEditRemove', {
                data: {
                    id: {% if merchant.id %}{{ merchant.id }}{% else %}0{% endif %},
                    error_id : {{ error.id }}
                },
                type: "POST",
                dataType: "json",
                success: function (result) {
                    if (result && result.success) {
                        window.location.href = '/admin';
                    } else {
                        alert(result.errors);
                    }
                }
            }
        );
        return false;
    });
</script>