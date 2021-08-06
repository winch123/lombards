<div class="row">
    {% if (merchants_new | length) or (merchants_edit | length) %}
        <div class="col-md-4">
            {% if merchants_new | length %}
                <div class="col-md-12">
                    <h4>Новые мерчанты</h4>
                    <ul class="list-group">
                        {% for merchant_new in merchants_new %}
                            <a href="/admin/merchants/edit{{ merchant_new.id }}" class="list-group-item">
                                {{ merchant_new.name }}
                            </a>
                        {% endfor %}
                    </ul>
                </div>
            {% endif %}
            {% if merchants_edit | length %}
                <div class="col-md-12">
                    <h4>Редактируемые мерчанты</h4>
                    <ul class="list-group">
                        {% for merchant_edit in merchants_edit %}
                            <a href="/admin/merchants/edit{{ merchant_edit.id }}" class="list-group-item">
                                {{ merchant_edit.name }}
                            </a>
                        {% endfor %}
                    </ul>
                </div>
            {% endif %}
        </div>
    {% endif %}
    <div class="col-sm-6 col-md-4">
        <h4>Ошибки у мерчантов</h4>
        {% if merchants_errors | length %}
            <ul class="list-group">
                {% for merchant in merchants_errors %}
                    <a href="/admin/merchants_error/edit{{ merchant.id }}" class="list-group-item">
                        {{ merchant.merchant_name }} (добавлено {{ merchant.date_create }})
                    </a>
                {% endfor %}
            </ul>
        {% endif %}
        <a href="/admin/closedErrorReports">Закрытые ошибки</a>
    </div>
</div>