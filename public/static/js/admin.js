$(document).ready(function() {
    var table_plugin_lang = {
            "processing": "Подождите...",
            "search": "Поиск:",
            "lengthMenu": "Показать _MENU_ записей",
            "info": "Записи с _START_ до _END_ из _TOTAL_ записей",
            "infoEmpty": "Записи с 0 до 0 из 0 записей",
            "infoFiltered": "(отфильтровано из _MAX_ записей)",
            "infoPostFix": "",
            "loadingRecords": "Загрузка записей...",
            "zeroRecords": "Записи отсутствуют.",
            "emptyTable": "В таблице отсутствуют данные",
            "paginate": {
                "first": "Первая",
                "previous": "Предыдущая",
                "next": "Следующая",
                "last": "Последняя"
            },
            "aria": {
                "sortAscending": ": активировать для сортировки столбца по возрастанию",
                "sortDescending": ": активировать для сортировки столбца по убыванию"
            }
        };

    $('#merchant-list').DataTable({
        "ajax": '/admin/api/merchantList',
        "language": table_plugin_lang,
        "stateSave": true
    });

    $('#citys-list').DataTable({
        "ajax": '/admin/api/citysList',
        "language": table_plugin_lang,
        "stateSave": true
    });

    $('#studios-list').DataTable({
        "ajax": '/admin/api/studiosList',
        "language": table_plugin_lang,
        "stateSave": true
    });

    $('#tags-list').DataTable({
        "ajax": '/admin/api/tagsList',
        "language": table_plugin_lang,
        "stateSave": true
    });

    $('#merchant-frequency-list').DataTable({
        "ajax": '/admin/api/merchantFrequencyList',
        "language": table_plugin_lang,
        "stateSave": true
    });

    $(document).on('click', '.remove-city', function(event) {
        console.log(event);
        event.preventDefault();

        if (confirm('Удалить?!')) {
            var $this = $(this);

            if ($this.data('merchant-id') && $this.data('tag-id')) {
                var ob = {
                    tag: $this.data('tag-id'),
                    merchant: $this.data('merchant-id')
                };
            } else {
                var ob = {};
            }

            $.ajax(
                $this.attr('href'), {
                    data: ob,
                    type: "POST",
                    dataType: "json",
                    success: function (result) {
                        if (result.success) {
                            alert('Удалено');

                            window.location.reload();
                        } else {
                            alert(result.errors);
                        }
                    }
                }
            );
        }
    });

    $(document).on('click', '.admin-add-tag', function(event) {
        event.preventDefault();

        var tagId = $('.admin-add-tag-value').val(),
            merchantId = $('.admin-add-tag-merchant-value').val();

        $.ajax(
            '/admin/api/tagsAddToMerchant', {
                data: {
                    tag: tagId,
                    merchant: merchantId
                },
                type: "POST",
                dataType: "json",
                success: function (result) {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.errors);
                    }
                }
            }
        );
    });

    CKEDITOR.replaceAll( 'editor' );
});

function pageEnable (id, status) {
    if (status) {
        var link = '/admin/api/pageEnable?id=' + id;
    } else {
        var link = '/admin/api/pageDisable?id=' + id;
    }

    $.ajax(
        link, {
            data: {},
            type: "POST",
            dataType: "json",
            success: function (result) {
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.errors);
                }
            }
        }
    );
}