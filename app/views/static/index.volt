<div class="col-md-9">
    <div class="page-header">
        <h1>{{meta.page.title}}</h1>
    </div>

    {% for page in pages %}
        <div class="media">
            <div class="media-body">
                <h4 class="media-heading">
                    {{ linkTo(pagesUrl ~ page.action, page.title) }}
                </h4>
                {{page.title_extra}}
            </div>
        </div>
    {% endfor %}

</div>
