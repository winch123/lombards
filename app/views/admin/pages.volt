<div class="page-header">
    <h4>Страницы <small><a href="/admin/pages/add" title="Добавить страницу"
                    class="btn btn-success btn-xs">
                    <i class="glyphicon glyphicon-plus"></i>
                    Добавить
                </a></small></h4>
</div>

<ul class="list-group">
    {% for page in pages %}
        <li class="list-group-item">
            <h5>
                {% if page['index'].disabled %}
                    <a  class="btn btn-danger btn-xs"
                        title="Страница отключана. Отображать!"
                        href="javascript:pageEnable({{page['index'].id}}, true)">
                        <i class="glyphicon glyphicon-eye-open"></i>
                    </a>
                {% else %}
                    <a  class="btn btn-default btn-xs"
                        title="Страница включена. Скрыть!"
                        href="javascript:pageEnable({{page['index'].id}}, false)">
                        <i class="glyphicon glyphicon-eye-close"></i>
                    </a>
                {% endif %}

                <a href="/admin/pages/edit{{page['index'].id}}" title="Редактировать">
                    {{page['index'].title}}
                </a>
            </h5>
            {% if page['pages'] is defined %}
                <ul class="list-group">
                    {% for subpage in page['pages'] %}
                        <li class="list-group-item">
                            <h5>
                                {% if subpage.disabled %}
                                    <a  class="btn btn-danger btn-xs"
                                        title="Страница отключана. Отображать!"
                                        href="javascript:pageEnable({{subpage.id}}, true)">
                                        <i class="glyphicon glyphicon-eye-open"></i>
                                    </a>
                                {% else %}
                                    <a  class="btn btn-default btn-xs"
                                        title="Страница включена. Скрыть!"
                                        href="javascript:pageEnable({{subpage.id}}, false)">
                                        <i class="glyphicon glyphicon-eye-close"></i>
                                    </a>
                                {% endif %}

                                <a href="/admin/pages/edit{{subpage.id}}" title="Редактировать">
                                    {{subpage.title}}
                                </a>
                            </h5>
                        </li>
                    {% endfor %}
                </ul>
            {% endif %}
        </li>
    {% endfor %}
</ul>
