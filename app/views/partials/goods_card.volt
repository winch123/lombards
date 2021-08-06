<div class="thumbnail thmb">
    <a href="{{item.getUrl()}}" title="{{item.name|e}}">
        {% set preview_images = item.getUploadedImagesSrc(true) %}

        {% if preview_images | length %}
            <div class="thumbnail-img-wrap">
                <img data-src="{{preview_images[0]}}" class="good-image-preview lazy" alt="{{item.name|e}}" title="{{item.name|e}}">
                    <div class="wrap-title">
                        {% for image in preview_images %}
                            <div class="js-tile-photo" data-title="{{loop.index0}}" data-src="{{image}}"></div>
                        {% endfor %}
                    </div>

                    <div class="circle-block">
                        {% for image in preview_images %}
                            <span class="circle js-circle" data-circle="{{loop.index0}}"></span>
                        {% endfor %}
                    </div>
            </div>
        {% else %}
            <div class="thumbnail-img-wrap">
                <img data-src="{{item.getMainImagePreviewSrc()}}" class="good-image-preview default-image lazy" alt="{{item.name|e}}" title="{{item.name|e}}">
            </div>
        {% endif %}

        <div class="caption">
            <p>{{item.name}}</p>
        </div>

        <div class="price">
            {% if item.price %}
                {{item.price}} {{item.currency}}
            {% else %}
                цена не указана
            {% endif %}
        </div>
    </a>
</div>
