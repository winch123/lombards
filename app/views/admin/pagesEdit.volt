<div class="page-header">
    <h4>
        Редактирование
        <small>(<a href="{{page.getUrl()}}" target="_blank">Перейти на страницу на сайте</a>) </small>
    </h4>
</div>


<form class="form-horizontal" id="edit-page">
    <div class="form-group">
        <label for="inputAction" class="col-sm-2 control-label">Url</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputAction"
                name="action"
                placeholder="Например: contacts"
                value="{{ page.action | e }}">
        </div>
    </div>

    <div class="form-group">
        <label for="inputTitle" class="col-sm-2 control-label">Заголовок</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputTitle"
                name="title"
                placeholder="Например: Контакты"
                value="{{ page.title | e }}">
        </div>
    </div>

    <div class="form-group">
        <label for="inputTitleExtra" class="col-sm-2 control-label">Подзаголовок</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputTitleExtra"
                name="title_extra"
                placeholder="Например: Наши адреса и телефоны"
                value="{{ page.title_extra | e }}">
        </div>
    </div>

    <div class="form-group">
        <label for="inputKeywords" class="col-sm-2 control-label">Keywords</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputKeywords"
                name="keywords"
                placeholder="Например: "
                value="{{ page.keywords | e }}">
        </div>
    </div>

    <div class="form-group">
        <label for="inputDescription" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputDescription"
                name="description"
                placeholder="Например: Наши адреса и телефоны"
                value="{{ page.description | e }}">
        </div>
    </div>

    <div class="form-group">
        <label for="inputContent" class="col-sm-2 control-label">Шапка страницы</label>
        <div class="col-sm-10">
            <textarea class="form-control editor" id="inputPreContent" name="top_content">{{ page.top_content | e }}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="inputContent" class="col-sm-2 control-label">Контент</label>
        <div class="col-sm-10">
            <textarea class="form-control editor" id="inputContent" name="content">{{ page.content | e }}</textarea>
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
            '/admin/api/pagesEditSave', {
                data: {
                    title: $('#inputTitle').val(),
                    title_extra: $('#inputTitleExtra').val(),
                    keywords: $('#inputKeywords').val(),
                    description: $('#inputDescription').val(),
                    top_content: CKEDITOR.instances.inputPreContent.getData(),
                    content: CKEDITOR.instances.inputContent.getData(),
                    action: $('#inputAction').val(),
                    id: {{page.id}}
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
