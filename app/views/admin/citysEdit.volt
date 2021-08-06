<div class="page-header">
    <h4>
        Редактирование
        <small>(<a href="/city/{{city.name}}" target="_blank">Страница города</a>) </small>
    </h4>
</div>

<form class="form-horizontal" id="edit-city">
    <div class="form-group">
        <label for="inputName" class="col-sm-2 control-label">Название</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputName"
                name="name"
                placeholder="Например: Ломбард бла бла"
                value="{{ city.name | e }}">
        </div>
    </div>

    <div class="form-group">
        <label for="inputNameCase1" class="col-sm-2 control-label">Склонение 1</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputNameCase1"
                name="nameCase1"
                placeholder="Например: Самары"
                value="{{ city.name_case1 | e }}">
            <span id="inputNameCase1Help" class="help-block">Укажите склонение подобное примеру:<br>
            Ломбарды <b>Самары</b></span>
        </div>
    </div>

    <div class="form-group">
        <label for="inputNameCase2" class="col-sm-2 control-label">Склонение 2</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="inputNameCase2"
                name="nameCase2"
                placeholder="Например: в Самаре"
                value="{{ city.name_case2 | e }}">
            <span id="inputNameCase2Help" class="help-block">Укажите склонение подобное примеру:<br>
            Телефон марки X <b>в Самаре</b></span>
        </div>
    </div>

    <div class="form-group">
        <label for="selectCountry" class="col-sm-2 control-label">Страна</label>
        <div class="col-sm-5">
            <select class="form-control" id="selectCountry" name="countryId">
                <option value="0" {% if city.country_id == 0 %}selected{% endif %}>Не выбрана</option>
                {% for country in countries %}
                    <option value="{{ country.id | e }}" {% if country.id == city.country_id %}selected{% endif %} >{{ country.name | e }}</option>
                {% endfor %}
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-offset-2 col-sm-5">
            <button type="submit" class="btn btn-success">Сохранить</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $('#edit-city').on('submit', function (event) {
        validateForm(event);
    });

    function validateForm (event) {
        event.preventDefault();

        $.ajax(
            '/admin/api/citysEditSave', {
                data: {
                    name: $('#inputName').val(),
                    name_case1: $('#inputNameCase1').val(),
                    name_case2: $('#inputNameCase2').val(),
                    country_id: $('#selectCountry').val(),
                    id: {{city.id}}
                },
                type: "POST",
                dataType: "json",
                success: function (result) {
                    if (result && result.success) {
                        window.location.href = '/admin/citys/';
                    } else {
                        alert(result.errors);
                    }
                }
            }
        );
    }
</script>
