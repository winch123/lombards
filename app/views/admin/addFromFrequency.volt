<div class="page-header">
    <h4>
        Редактирование
    </h4>
</div>

<form class="form-horizontal" id="edit-request" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="inputName" class="col-sm-2 control-label">Название</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputName" name="name" {% if good_name is defined %}value="{{ good_name | e }}" readonly{% endif %} placeholder="Например: Nokia 3310">
        </div>
    </div>

    <div class="form-group">
        <label for="inputFile" class="col-sm-2 control-label">Изображение</label>
        <div class="col-sm-5">
            <div class="uploaded-image-container">
                <img src="/static/img/noimage.gif" alt><br>
            </div>

            <input type="hidden" name="filename" id="inputFile" value="">
            <span class="btn btn-sm btn-default fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                Загрузить изображение
                <input id="fileupload" type="file" name="files[]" data-url="/api/add-studio-image">
            </span>
            <span class="help-block" id="input-file-help"></span>
        </div>
    </div>

    {% if good_name is defined %}
        <div class="form-group">
            <label for="inputFile" class="col-sm-2 control-label">Изображения из google-images</label>
            <div class="col-sm-10">
                <div class="row">
                    {% for imageUrl in googleImages %}
                        <div class="col-xs-2 col-md-2">
                            <a href="{{imageUrl}}" target="_blank">
                                <img src="{{imageUrl}}" style="max-width: 100%; max-height: 75px;" class="google-image"><br>
                            </a>

                            <a href="javascript:uploadGoogleImage({{loop.index}})" class="upload-google-image">
                                Прикрепить изображение
                            </a>
                        </div>

                        {% if loop.index == 6 %}
                            </div><br><div class="row">
                        {% endif %}

                    {% endfor %}
                </div>
            </div>
        </div>
    {% endif %}

    <div class="form-group">
        <div class="col-md-offset-2 col-sm-5">
            <button type="submit" class="btn btn-success">Сохранить</button>
        </div>
    </div>
</form>

<script src="/js/jquery.ui.widget.js"></script>
<script src="/js/jquery.iframe-transport.js"></script>
<script src="/js/jquery.fileupload.js"></script>
<script type="text/javascript">
    $(function () {
        console.log('init fileupload');

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

<script type="text/javascript">
    'use strict';

    function uploadGoogleImage(image_number) {
        var $images = $('.google-image');
        var $image = $($images[image_number-1]);

        $('.uploaded-image-container').html('<img src="' + $image.attr('src') + '">');
        $('#inputFile').val($image.attr('src'));
    };

    $('#edit-request').on('submit', function (event) {
        validateForm(event);
    });

    function validateForm (event) {
        event.preventDefault();

        $.ajax(
            '/admin/api/studiosRequestSave', {
                data: {
                    name: $('#inputName').val(),
                    filename: $('#inputFile').val()
                },
                type: "POST",
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        window.location.href = '/admin/studios/frequency';
                    } else {
                        alert(response.errors.join("\n"));
                    }
                }
            }
        );
    }
</script>
