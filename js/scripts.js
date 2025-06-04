// js/scripts.js

$(document).ready(function() {
    // ---------------------
    // 1) Client-side form validation
    // ---------------------
    $('form').on('submit', function(e) {
        var isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('input-error');
            } else {
                $(this).removeClass('input-error');
            }
        });
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });

    // Remove error class on input
    $('[required]').on('input change', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('input-error');
        }
    });


    // 2) AJAX: Like / Unlike
    // ---------------------
    $('.btn-like, .btn-unlike, #btnToggleLike').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var recipeId = $btn.data('id');
        var action = $btn.data('action');

        $btn.prop('disabled', true);

        $.ajax({
            url: 'like_ajax.php',
            method: 'POST',
            data: { recipe_id: recipeId, action: action },
            dataType: 'json'
        }).done(function(res) {
            if (res.success) {
                // Update like count display
                if ($('#likeCount').length) {
                    $('#likeCount').text(res.like_count);
                }
                if ($btn.closest('.recipe-card').length) {
                    $btn.closest('.recipe-card').find('.like-count').text(res.like_count);
                }
                // Toggle button text & data-action
                if (action === 'like') {
                    $btn.text('Unlike').removeClass('btn-like btn-success').addClass('btn-unlike btn-danger').data('action','unlike');
                } else {
                    $btn.text('Like').removeClass('btn-unlike btn-danger').addClass('btn-like btn-success').data('action','like');
                }
            } else {
                alert(res.message || 'An error occurred.');
            }
        }).fail(function() {
            alert('Request failed. Try again.');
        }).always(function() {
            $btn.prop('disabled', false);
        });
    });

    // ---------------------
    // 3) AJAX: Post Comment
    // ---------------------
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        var content = $('#commentContent').val().trim();
        var recipeId = $('input[name="recipe_id"]').val();
        var btn = $('#commentSubmitBtn');

        if (!content) {
            alert('Comment cannot be empty.');
            return;
        }

        btn.prop('disabled', true);

        $.ajax({
            url: 'comment_ajax.php',
            method: 'POST',
            data: { recipe_id: recipeId, content: content },
            dataType: 'json'
        }).done(function(res) {
            if (res.success) {
                // Append new comment HTML
                if ($('#noComments').length) {
                    $('#noComments').remove();
                }
                $('#commentsList').append(res.comment_html);
                // Clear textarea
                $('#commentContent').val('');
            } else {
                alert(res.message || 'Could not post comment.');
            }
        }).fail(function() {
            alert('Request failed. Try again.');
        }).always(function() {
            btn.prop('disabled', false);
        });
    });

});
