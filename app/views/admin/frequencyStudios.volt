<div class="page-header">
    <h4>Запросы на студийное изображение</h4>
    <a href="/admin/studios/addFromFrequency">Добавить новое студийное изображение</a>
</div>

<table id="merchant-frequency-list" class="table table-striped table-bordered" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Название</th>
            <th class="good-count">Количество объявлений</th>
            <th>Добавить файл</th>
        </tr>
    </thead>
</table>

<script type="text/javascript">
    $(document).ready(function() {
        $('.good-count.sorting').click();
        $('.good-count.sorting_asc').click();
    });
</script>