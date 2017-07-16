jQuery(document).ready(function ($) {
    // save the send_to_editor handler function
    window.send_to_editor_default = window.send_to_editor;

    $('#set-map-image').click(function () {

        // replace the default send_to_editor handler function with our own
        window.send_to_editor = window.attach_image;
        var postId = 0;
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');

        return false;
    });

    $('#remove-map-image').click(function () {

        $('#upload_image_id').val('');
        $('img').attr('src', '');
        $(this).hide();

        return false;
    });

    // handler function which is invoked after the user selects an image from the gallery popup.
    // this function displays the image and sets the id so it can be persisted to the post meta
    window.attach_image = function (html) {

        // turn the returned image html into a hidden image element so we can easily pull the relevant attributes we need
        $('body').append('<div id="temp_image">' + html + '</div>');

        var img = $('#temp_image').find('img');

        var imgurl = img.attr('src');
        var imgclass = img.attr('class');
        var imgid = parseInt(imgclass.replace(/\D/g, ''), 10);

        $('#upload_image_id').val(imgid);
        $('#remove-map-image').show();

        $('img#map_image').attr('src', imgurl);
        try {
            tb_remove();
        } catch (e) {
        }
        img.remove();

        // restore the send_to_editor handler function
        window.send_to_editor = window.send_to_editor_default;

    }
});
