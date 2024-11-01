let productProperties = {
    name: '',
    price: '',
    description: '',
};

jQuery.fn.extend({
    insertProductProperty: function (value) {
        this.each(function() {
            if (document.selection) {
                this.focus();

                const selection = document.selection.createRange();
                selection.text = value;

                this.focus();
            } else if (this.selectionStart || this.selectionStart == '0') {
                const startPosition = this.selectionStart;
                const endPosition = this.selectionEnd;
                const scrollTop = this.scrollTop;

                this.value = this.value.substring(0, startPosition) +
                    value + this.value.substring(endPosition,this.value.length);
                this.focus();
                this.selectionStart = startPosition + value.length;
                this.selectionEnd = startPosition + value.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += value;
                this.focus();
            }
        });
        return this;
    }
});

jQuery(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('productId');

    jQuery('select#groost-wc-product-id').on('change', function (e) {
        e.preventDefault();

        const productId = e.target.value;

        if (!productId) {
            throw new Error('Invalid product ID');
        }

        loadProductData(productId);
    });

    jQuery('textarea#groost_wc_product_primarytext').on('change', function (e) {
        jQuery('div.groost-wc_fbpost-primary-text').html(e.target.value.replace(/\n/g, '<br />'));
    });

    jQuery('input#groost-wc-product-headline').on('change', function (e) {
        jQuery('div.groost-wc_fbpost-link-headline-title').html(e.target.value);
    });

    if (productId) {
        jQuery('select#groost-wc-product-id').val(productId).trigger('change');
    }

    jQuery('div.groost_wc_post_type_container').on('click', function (e) {
        e.preventDefault();

        setImageSize(jQuery(this), jQuery(this).attr('data-size'));
    })
});

function loadProductData(productId) {
    const data = {
        'action': 'groost_wc_product_detail',
        productId,
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function (response) {
        const data = JSON.parse(response);

        if (data) {
            productProperties = {
                name: data.name || '',
                price: `${data.price}${data.currency.code}`,
                description: data.description || '',
            }

            let primaryText = '';

            if (data.name) {
                jQuery('input#groost-wc-product-headline').val(data.name).trigger('change');

                primaryText += data.name + '\n';
            }

            if (data.description) {
                primaryText += data.description + '\n\n';
            }

            if (data.price) {
                primaryText += `Price ${data.price}${data.currency.code}`;
            }

            jQuery('textarea#groost_wc_product_primarytext').val(primaryText).trigger('change');

            if (data.image && data.image.src.length > 0) {
                setImage(
                    data.image.src[0],
                    data.image.id,
                );
            }

            if (data.images && data.images.length > 0) {
                let gal = '';
                for (const image of data.images) {
                    gal += `<div class="groost-wc_fbpost-gallery-image" data-image-url="${image.src[0]}" data-image-id="${image.id}" style="background-image: url('${image.src[0]}');"></div>`;
                }

                jQuery('div.groost-wp-product_images').html(gal);

                jQuery('div.groost-wc_fbpost-gallery-image').on('click', function (e) {
                    e.preventDefault();

                    jQuery('div.groost-wc_fbpost-gallery-image.selected').removeClass('selected');
                    jQuery(this).addClass('selected');
                    setImage(
                        jQuery(this).attr('data-image-url'),
                        jQuery(this).attr('data-image-id'),
                    );
                });
            }
        }
    });
}

function setImage(imgSrc, imageId) {
    jQuery('input[name="groost-wc-image-id"]').val(imageId);
    jQuery('.groost-wc_fbpost-image').css('background-image', `url(${imgSrc})`);
}

function addPropertyToText(el, property) {
    jQuery(el).insertProductProperty(productProperties[property]);
}

function setImageSize(el, size) {
    const imageElement = jQuery('div.groost-wc_fbpost-image');
    const currentSize = imageElement.attr('data-size');

    imageElement
        .removeClass(currentSize)
        .addClass(size)
        .attr('data-size', size)

    jQuery('div.groost_wc_post_type_container.active').removeClass('active');

    el.addClass('active');

    jQuery('input[name="groost-wc-image-size"]').val(size);
}
