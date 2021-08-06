<div class="page-header">
    <h4>
        Редактирование
        <small></small>
    </h4>
</div>

<div class="row">
    <div class="col-md-9">
        <form class="form-horizontal" id="edit-tag">
            <div class="form-group">
                <label for="inputName" class="col-sm-3 control-label">Название</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="inputName"
                        name="name"
                        placeholder="Например: Антикварные ломбарды"
                        value="{{ merchantTag.name | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputTitle" class="col-sm-3 control-label">Тайтл</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="inputTitle"
                        name="title"
                        value="{{ merchantTag.title | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputKeywords" class="col-sm-3 control-label">Keywords</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="inputKeywords"
                        name="keywords"
                        value="{{ merchantTag.keywords | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputDescription" class="col-sm-3 control-label">Description</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="inputDescription"
                        name="description"
                        value="{{ merchantTag.description | e }}">
                </div>
            </div>

            <div class="form-group">
                <label for="inputText" class="col-sm-3 control-label">Описание</label>
                <div class="col-sm-9">
                    <textarea class="form-control editor" id="inputText"
                        name="text"
                        >{{ merchantTag.text | e }}</textarea>
                </div>
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
                <div class="col-md-offset-3 col-sm-5">
                    <button type="submit" class="btn btn-success">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-3">
        <p><i>Во всех полях этой формы можно использовать слудующие теги:</i></p>
        <p>{САМАРА}, {САМАРЫ}, {ВСАМАРЕ}</p>
        <p><i>При отображении этих тегов будут подставлены названия городов в нужном склонении.</i></p>
    </div>
</div>

<script type="text/javascript">
    $('#edit-tag').on('submit', function (event) {
        validateForm(event);
    });

    function validateForm (event) {
        event.preventDefault();

        $.ajax(
            '/admin/api/tagsEditSave', {
                data: {
                    name: $('#inputName').val(),
                    title: $('#inputTitle').val(),
                    keywords: $('#inputKeywords').val(),
                    description: $('#inputDescription').val(),
                    text: CKEDITOR.instances.inputText.getData(),
                    id: {{merchantTag.id}}
                },
                type: "POST",
                dataType: "json",
                success: function (result) {
                    if (result && result.success) {
                        window.location.href = '/admin/tags/';
                    } else {
                        alert(result.errors);
                    }
                }
            }
        );
    }
</script>
