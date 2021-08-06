<div class="thumbnail thmb alm merchant-thumb">
    <a href="{{item.getUrl()}}" title="{{item.name|e}}">
        <div class="thumb-top">
            <img src="{{item.getImagePreview()}}" class="good-image-preview default-image" alt="{{item.name|e}}">
        </div>
        <div class="thumb-bottom">
            <div class="caption">
                <p>{{item.name}}</p>
            </div>
            <div class="address">{{item.address}}</div>
            {% if not item.custom %}
                <div class="goods-count"><span class="badge">{{item.count_good | default(0)}}</span> товара(ов)</div>
            {% endif %}
        </div>
    </a>
</div>
