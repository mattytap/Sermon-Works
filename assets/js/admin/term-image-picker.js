jQuery(document).ready(function ($) {
    $(document).on('click', '.sm-term-image-add', function (e) {
        e.preventDefault();
        var $field = $(this).closest('.sm-term-image-field');

        var frame = wp.media({
            title: smTermImagePicker.modalTitle,
            button: { text: smTermImagePicker.modalButton },
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var thumbUrl = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
            $field.find('input[name="sm_term_image_id"]').val(attachment.id);
            $field.find('.sm-term-image-preview img').attr('src', thumbUrl);
            $field.find('.sm-term-image-preview').show();
            $field.find('.sm-term-image-remove').show();
        });

        frame.open();
    });

    $(document).on('click', '.sm-term-image-remove', function (e) {
        e.preventDefault();
        var $field = $(this).closest('.sm-term-image-field');
        $field.find('input[name="sm_term_image_id"]').val('');
        $field.find('.sm-term-image-preview').hide();
        $field.find('.sm-term-image-preview img').attr('src', '');
        $field.find('.sm-term-image-remove').hide();
    });
});
