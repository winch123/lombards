<div class="row">
    <div class="col-md-4">
        <h4>Закрытые ошибки у мерчантов</h4>
        {% if closed_merchants_errors | length %}
        <ul class="list-group">
            {% for merchant in closed_merchants_errors %}
                <a href="/admin/merchants_error/edit{{ merchant.id }}" class="list-group-item">
                    {{ merchant.merchant_name }} - (закрыто - {{ merchant.date_closed }})
                </a>
            {% endfor %}
        </ul>
        {% endif %}
    </div>
</div>
