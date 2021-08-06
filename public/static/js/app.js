$(document).ready(function() {
    $(".fancybox-button").fancybox({'type': 'image', 'minWidth': 50, 'minHeight': 50, 'scrolling': 'no'});

    searchFormSliderReInit();

    $(document).on('change', '#slider-price', function(event) {
        event.stopPropagation();
        event.preventDefault();

        var values = $(this).val().split(',');

        var min = parseInt(values[0]) || 0;
        var max = parseInt(values[1]) || 0;

        $('#searchMinPrice').val(min);
        $('#searchMaxPrice').val(max);

        $('#fakeSearchMinPrice').val(min);
        $('#fakeSearchMaxPrice').val(max);
    });

    $(document).on('change', '#slider-size', function(event) {
        event.stopPropagation();
        event.preventDefault();

        var values = $(this).val().split(',');

        var min = parseFloat(values[0]) || 0;
        var max = parseFloat(values[1]) || 0;

        $('#searchMinSize').val(min);
        $('#searchMaxSize').val(max);

        $('#fakeSearchMinSize').val(min);
        $('#fakeSearchMaxSize').val(max);
    });

    $(document).on('change', '#searchZeroAllowedSize', function(event) {
        $('#fakeSearchZeroAllowedSize').val($(this).is(':checked') ? 1 : 0);
        $('.search-form').trigger('change');
    });

    $(document).on('change', '#searchZeroAllowedPrice', function(event) {
        $('#fakeSearchZeroAllowedPrice').val($(this).is(':checked') ? 1 : 0);
        $('.search-form').trigger('change');
    });

    $(document).on('input', '.search-control-size', function(event) {
        event.stopPropagation();

        var min = parseFloat($('#searchMinSize').val());
        var max = parseFloat($('#searchMaxSize').val());

        if (min) {
            $('#fakeSearchMinSize').val(min);
        } else {
            min = parseFloat($('#searchMinSize').attr('placeholder'));
            $('#fakeSearchMinSize').val('');
        }

        if (max) {
            $('#fakeSearchMaxSize').val(max);
        } else {
            max = parseFloat($('#searchMaxSize').attr('placeholder'));
            $('#fakeSearchMaxSize').val('');
        }

        window.sliderSize.slider('setValue', [min, max]);
    });

    $(document).on('input', '.search-control-price', function(event) {
        event.stopPropagation();

        var min = parseInt($('#searchMinPrice').val());
        var max = parseInt($('#searchMaxPrice').val());

        if (min) {
            $('#fakeSearchMinPrice').val(min);
        } else {
            min = parseInt($('#searchMinPrice').attr('placeholder'));
            $('#fakeSearchMinPrice').val('');
        }

        if (max) {
            $('#fakeSearchMaxPrice').val(max);
        } else {
            max = parseInt($('#searchMaxPrice').attr('placeholder'));
            $('#fakeSearchMaxPrice').val('');
        }

        window.sliderPrice.slider('setValue', [min, max]);
    });

    $(document).on('change', '#SearchCategory', function(event) {
        event.stopPropagation();
        if ($(this).val() === 'all') {
            $(".wrapper-field-metal").hide();
            $(".wrapper-field-metal-standart").hide();
            $(".wrapper-field-size").hide();
        }

        $("#fakeSearchMinSize").val('');
        $("#fakeSearchMaxSize").val('');
        $("#fakeSearchZeroAllowedSize").val('');

        // hack: clear model_id value for not mobile category
        $(".search-form .search-field-phonemodel").val('');

        $('#searchForm').trigger('change');
    });

    $(document).on('change', '#SearchMetal', function(event) {
        event.stopPropagation();
        $(".search-form .search-field-metal").val($(this).val());
        $(".search-form .search-field-metal-standart").val('');
        if ($(".search-form .search-field-metal").val() === 'all') {
            $(".wrapper-field-metal-standart").hide();
        }
        $('#searchForm').trigger('change');
    });

    $(document).on('change', '#SearchMetalStandarts', function(event) {
        event.stopPropagation();
        $(".search-form .search-field-metal-standart").val($(this).val());
        $('#searchForm').trigger('change');
    });

    $(document).on('change', '#fakeSearchPhoneModel', function(event) {
        event.stopPropagation();
        $(".search-form .search-field-phonemodel").val($(this).val());
        $('#searchForm').trigger('change');
    });

    $(document).on('change', '#searchForm', function(event) {
        event.stopPropagation();

        var data = toObject($(this).serializeArray());

        data.zero_price = $('#searchZeroAllowedPrice').is(':checked') ? 1 : 0;
        data.zero_size = $('#searchZeroAllowedSize').is(':checked') ? 1 : 0;

        $.ajax('/api/get-search-data', {
            dataType: 'html',
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                $('.search-form').replaceWith(response);
                $(".search-collapse").addClass('in');
                searchFormSliderReInit();
            }
        });
    });

    $(document).on('click', '.city-detect-confirm .btn-confirm', function (event) {
        const city_id = 'detected';

        rememberCity(city_id, function () {
            $('.city-detect-confirm').remove();

            if (window.location.href === '/') {
                window.location.reload();
            }
        });
    });

    $(document).on('click', '.remember-city-link', function (event) {
        event.preventDefault();

        const city_id = $(this).data('id'),
                url_redirect = $(this).attr('href');

        rememberCity(city_id, function () {
            window.location.href = url_redirect;
        });
    });

    $(document).on('click', '.tab-country a', function (event) {
        event.preventDefault()
        $(this).tab('show')
    });

    $(".slider_good_mobile").on("init", function(event, slick) {
        $(".slick-counter").text(parseInt(slick.currentSlide + 1) + ' / ' + slick.slideCount);
    });

    $('.slider_good_mobile').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        mobileFirst: true,
        adaptiveHeight: true,
        responsive: [
            {
                breakpoint: 767,
                settings: 'unslick'
            }
        ]
    });

    $(".slider_good_mobile").on("afterChange", function(event, slick, currentSlide) {
        $(".slick-counter").text(parseInt(slick.currentSlide + 1) + ' / ' + slick.slideCount);
    });

    $(document).on('click', '.close_yellow_hint', function () {
        let $parent_hint = $(this).parent();
        let $date = new Date();
        let minutes = 30;
        let priority = $parent_hint.data('priority');

        $parent_hint.slideUp();

        $date.setTime($date.getTime() + (minutes * 60 * 1000));

        $.cookie('yellow_hint_hide_' + priority, 'Y', {expires: $date});
    });

});

function makeSelectHtml(elem, data, textall, valueall) {
    $(elem).empty();
    $(elem).html(' ');

    $(elem).append(
        $('<option></option>').text(textall).attr("value", valueall)
    );

    $.each(data, function(key, value) {
        var $option;

        if (value.id) {
            $option = $('<option></option>').attr("value", value.id);
        } else {
            $option = $('<option></option>');
        }

        if (value.uname) {
            $option.html(value.uname);
        }

        if (value.class) {
            $option.addClass(value.class);
        }

        if (value.selected) {
            $option.prop('selected', true);
            $option.attr('selected', true);
        }

        $(elem).append($option);
    });
}

/**
 * return "well" object;
 */
function toObject(a) {
    var tmp = {};

    for (x in a) {
        tmp[a[x].name] = a[x].value;
    }

    return tmp;
}

/**
 *
 * @param {string} country_code
 * @returns {undefined}
 */
function chooseCity(country_code) {
    $.ajax('/choose_city/' + country_code + '/', {
        dataType: 'html',
        method: 'GET',
        success: function (response) {
            $.fancybox({
                width: 900,
                height: 750,
                autoSize: false,
                content: response
            });
        }
    });
}

function rememberCity(city_id, success_callback) {
    $.ajax({
        type: 'POST',
        url: '/city/remember/',
        dataType: 'json',
        data: {city_id: city_id},
        success: function (result) {
            if (result.status) {
                if (success_callback) {
                    success_callback();
                }
            } else {
                alert(result.message);
            }
        },
        error: function (xhr, str) {
            console.log('Возникла ошибка: ' + xhr.responseCode + '; Текст ошибки: ' + str);
        }
    });
}

Share = {
    vkontakte: function(ptitle, pimg, text) {
        url  = 'http://vkontakte.ru/share.php?';
        url += 'url='          + encodeURIComponent(window.location.href);
        url += '&title='       + encodeURIComponent(ptitle);
        url += '&description=' + encodeURIComponent(text);
        url += '&image='       + encodeURIComponent(pimg);
        url += '&noparse=true';
        Share.popup(url);
    },
    odnoklassniki: function(text) {
        url  = 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1';
        url += '&st.comments=' + encodeURIComponent(text);
        url += '&st._surl='    + encodeURIComponent(window.location.href);
        Share.popup(url);
    },
    facebook: function(ptitle, pimg, text) {
        url  = 'http://www.facebook.com/sharer.php?s=100';
        url += '&p[title]='     + encodeURIComponent(ptitle);
        url += '&p[summary]='   + encodeURIComponent(text);
        url += '&p[url]='       + encodeURIComponent(window.location.href);
        url += '&p[images][0]=' + encodeURIComponent(pimg);
        Share.popup(url);
    },
    twitter: function(ptitle) {
        url  = 'http://twitter.com/share?';
        url += 'text='      + encodeURIComponent(ptitle);
        url += '&url='      + encodeURIComponent(window.location.href);
        url += '&counturl=' + encodeURIComponent(window.location.href);
        Share.popup(url);
    },
    mailru: function(ptitle, pimg, text) {
        url  = 'http://connect.mail.ru/share?';
        url += 'url='          + encodeURIComponent(window.location.href);
        url += '&title='       + encodeURIComponent(ptitle);
        url += '&description=' + encodeURIComponent(text);
        url += '&imageurl='    + encodeURIComponent(pimg);
        Share.popup(url);
    },
    google: function() {
        url  = 'https://plus.google.com/share?';
        url += 'url='          + encodeURIComponent(window.location.href);
        Share.popup(url)
    },

    popup: function(url) {
        window.open(url,'','toolbar=0,status=0,width=626,height=436');
    }
};

function searchFormSliderReInit() {
    window.sliderPrice = $("#slider-price").slider({
        scale: 'logarithmic'
    });

    window.sliderSize = $("#slider-size").slider();
}

function initGoodsImagesSlider() {
    $(document).on('mouseleave', '.goods-preview-carousel .thumbnail', function () {
        const main_container = $(this),
                photos = main_container.find('.js-tile-photo'),
                circles = main_container.find('.circle-block .circle'),
                main_img = main_container.find('.good-image-preview'),
                src = photos.eq(0).data('src');

        main_img.attr('src', src);
        circles.removeClass('active');
    });

    $(document).on('mouseover', '.goods-preview-carousel .thumbnail .js-tile-photo', function () {
        const main_container = $(this).parents('.thumbnail');
        const main_img = main_container.find('.good-image-preview');
        const circles = main_container.find('.circle-block .circle');
        const index = $(this).data('title');
        const new_src = $(this).data('src');

        main_img.attr('src', new_src);

        circles.removeClass('active');
        circles.eq(index).addClass('active');
    });
}

$(function () {
    initGoodsImagesSlider();
});

document.addEventListener("DOMContentLoaded", function () {
    var lazyloadImages;

    if ("IntersectionObserver" in window) {
        lazyloadImages = document.querySelectorAll(".lazy");
        var imageObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var image = entry.target;
                    image.src = image.dataset.src;
                    image.classList.remove("lazy");
                    imageObserver.unobserve(image);
                }
            });
        });

        lazyloadImages.forEach(function (image) {
            imageObserver.observe(image);
        });
    } else {
        var lazyloadThrottleTimeout;
        lazyloadImages = document.querySelectorAll(".lazy");

        function lazyload() {
            if (lazyloadThrottleTimeout) {
                clearTimeout(lazyloadThrottleTimeout);
            }

            lazyloadThrottleTimeout = setTimeout(function () {
                var scrollTop = window.pageYOffset;
                lazyloadImages.forEach(function (img) {
                    if (img.getBoundingClientRect().top < (window.innerHeight + scrollTop)) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                    }
                });
                if (lazyloadImages.length == 0) {
                    document.removeEventListener("load", lazyload);
                    document.removeEventListener("scroll", lazyload);
                    window.removeEventListener("resize", lazyload);
                    window.removeEventListener("orientationChange", lazyload);
                }
            }, 20);
        }

        document.addEventListener("load", lazyload);
        document.addEventListener("scroll", lazyload);
        window.addEventListener("resize", lazyload);
        window.addEventListener("orientationChange", lazyload);
    }
});
