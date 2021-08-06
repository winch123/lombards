<div class="page-header">
    <h4>
        Редактирование
    </h4>
</div>

<form class="form-horizontal" id="edit-studio">
    <div class="form-group">
        <label for="inputName" class="col-sm-2 control-label">Ключевая строка</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputName"
                name="name"
                placeholder="Например: Samsung d880"
                value="{{ studio.good_name | e }}">
        </div>
    </div>

    <div class="form-group">
        <label for="inputFile" class="col-sm-2 control-label">
            Изображение </label>
        <div class="col-sm-5">

            <div class="uploaded-image-container">
                {% if studio.getImage() %}
                    <img src="{{studio.getImage()}}" alt><br>
                {% else %}
                    <img src="/static/img/noimage.gif" alt><br>
                {% endif %}
            </div>

            <input type="hidden" name="filename" id="inputFile" value="{{ studio.getImageName() }}">

            <span class="btn btn-sm btn-default fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                Прикрепить другое изображение
                <input id="fileupload" type="file" name="files[]" data-url="/api/add-studio-image">
            </span>
            <script src="/js/jquery.ui.widget.js"></script>
            <script src="/js/jquery.iframe-transport.js"></script>
            <script src="/js/jquery.fileupload.js"></script>
            <span class="help-block" id="input-file-help"></span>
            <script type="text/javascript">
                $(function () {
                    $('#fileupload').fileupload({
                        dataType: 'json',
                        done: function (e, data) {
                            if (data.result && data.result.success) {
                                $('#inputFile').val(data.result.success.name);
                                $('.uploaded-image-container').html('<img src="' + data.result.success.src + '">');
                            } else if (data.result.errors && data.result.errors.length > 0) {
                                $.fancybox('<strong>При загрузке файла произошли следующие ошибки</strong>:'
                                        + '<br><br>'
                                        + '<ul><li>'
                                        + data.result.errors.join('</li><li>')
                                        + '</li></ul>');
                            } else {
                                alert('Произошла неизвестная ошибка');
                            }
                        }
                    });
                });
            </script>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-offset-2 col-sm-5">
            <button type="submit" class="btn btn-success">Сохранить</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $('#edit-studio').on('submit', function (event) {
        validateForm(event);
    });

    function validateForm (event) {
        event.preventDefault();

        $.ajax(
            '/admin/api/studiosEditSave', {
                data: {
                    name: $('#inputName').val(),
                    filename: $('#inputFile').val(),
                    id: {{studio.id}}
                },
                type: "POST",
                dataType: "json",
                success: function (response) {
                    if (response && response.success) {
                        window.location.href = '/admin/studios/';
                    } else {
                        alert(response.errors.join("\n"));
                    }
                }
            }
        );
    }
</script>
