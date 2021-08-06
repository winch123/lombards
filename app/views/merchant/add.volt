<div class="page-header">
    <h1>Добавление ломбарда</h1>
</div>

<br><br>

<div class="row">
    <div class="col-md-9">
        <div class="row">
            <form class="form-horizontal" id="add-merchant-1">
                <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label">
                        Город <span class="text-red">*</span></label>
                    <div class="col-sm-6">
                        <select class="form-control input-sm" name="city" id="city-input">
                            {% for city in cities %}
                                <option value="{{city.name}}" {% if city.name == cityName %}selected{% endif %}>{{city.name}}</option>
                            {% endfor %}
                        </select>
                        <span class="help-block" id="input-name-help">
                            Укажите город, в который вы хотите добавить ломбард.
                        </span>

                        <p>
                            Перед добавлением нового ломбарда, убедитесь что он отсутствует в <a class="city-lombard-link" href="/{{ cityName }}/spisok_lombardov">списке ломбардов города<span class="selected-city-name">{% if cityName %} {{ cityName }}{% endif %}</span></a>,
                            если ваш ломбард уже есть в списке, то вы можете отредактировать информацию о нем со страницы ломбарда.
                            Если вашего ломбарда нет в списке, то нажмите на кнопку ниже.
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-8">
                        <button type="submit" class="btn btn-success" id="sendButton">Добавить ломбард</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function updateLink() {
        var cityName = $('#city-input').val();

        $('.city-lombard-link').attr('href', '/' + cityName + '/spisok_lombardov');
        $('.selected-city-name').text(' ' + cityName);
    }

    $(function () {
        $('#add-merchant-1').on('submit', function (e) {
            e.preventDefault();

            location.href = '/' + $('#city-input').val() + '/add-merchant/step-2'
        });

        $('#city-input').on('change', updateLink);

        updateLink();
    });
</script>