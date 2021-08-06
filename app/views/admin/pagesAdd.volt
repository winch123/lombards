<div class="page-header">
    <h4>Добавление страницы</h4>
</div>


<form class="form-horizontal" id="edit-page">
    <div class="form-group">
        <label for="inputType" class="col-sm-2 control-label">Куда добавлять</label>
        <div class="col-sm-3">
            <select class="form-control" id="inputType"
                name="type">
                <option value="index">В меню</option>
                <option value="static">В статьи</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="inputAction" class="col-sm-2 control-label">Url</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputAction"
                name="action"
                placeholder="Например: contacts">
        </div>
    </div>

    <div class="form-group">
        <label for="inputTitle" class="col-sm-2 control-label">Заголовок</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputTitle"
                name="title"
                placeholder="Например: Контакты">
        </div>
    </div>

    <div class="form-group">
        <label for="inputTitleExtra" class="col-sm-2 control-label">Подзаголовок</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputTitleExtra"
                name="title_extra"
                placeholder="Например: Наши адреса и телефоны">
        </div>
    </div>

    <div class="form-group">
        <label for="inputKeywords" class="col-sm-2 control-label">Keywords</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputKeywords"
                name="keywords"
                placeholder="Например: ">
        </div>
    </div>

    <div class="form-group">
        <label for="inputDescription" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputDescription"
                name="description"
                placeholder="Например: Наши адреса и телефоны">
        </div>
    </div>

    <div class="form-group">
        <label for="inputPreContent" class="col-sm-2 control-label">Шапка страницы</label>
        <div class="col-sm-10">
            <textarea class="form-control editor" id="inputPreContent" name="top_content"></textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="inputContent" class="col-sm-2 control-label">Контент</label>
        <div class="col-sm-10">
            <textarea class="form-control editor" id="inputContent" name="content"></textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-offset-2 col-sm-5">
            <button type="submit" class="btn btn-success">Сохранить</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $('#edit-page').on('submit', function (event) {
        validateForm(event);
    });

    function validateForm (event) {
        event.preventDefault();

        $.ajax(
            '/admin/api/pagesAddSave', {
                data: {
                    title: $('#inputTitle').val(),
                    title_extra: $('#inputTitleExtra').val(),
                    keywords: $('#inputKeywords').val(),
                    description: $('#inputDescription').val(),
                    top_content: CKEDITOR.instances.inputPreContent.getData(),
                    content: CKEDITOR.instances.inputContent.getData(),
                    action: $('#inputAction').val(),
                    type: $('#inputType').val()
                },
                type: "POST",
                dataType: "json",
                success: function (result) {
                    if (result && result.success) {
                        window.location.href = '/admin/pages/';
                    } else {
                        alert(result.errors);
                    }
                }
            }
        );
    }
</script>
