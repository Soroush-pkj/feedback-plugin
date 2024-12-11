jQuery(document).ready(function ($) {
    // Star rating interaction
    $('.star').on('mouseover', function () {
        const value = $(this).data('value');
        $(this).addClass('hover').siblings().removeClass('hover');
        $(this).nextAll().removeClass('hover');
    });

    $('.star').on('click', function () {
        const value = $(this).data('value');
        $('#rating').val(value);
        $(this).addClass('selected').siblings().removeClass('selected');
        $(this).nextAll().removeClass('selected');
    });

    $('#feedback-form').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.post(mfp_ajax.ajax_url, formData + '&action=submit_feedback', function (response) {
            if (response.success) {
                $('#feedback-response').html('<p>' + response.data.message + '</p>');
                $('#feedback-form')[0].reset();
                $('.star').removeClass('selected');
            } else {
                $('#feedback-response').html('<p>' + response.data.message + '</p>');
            }
        });
    });
});
