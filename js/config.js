$(document).ready(function() {
    var $modal = $('#external-modules-configure-modal');

    // We need to force "Enable on all projects by default" on config form.
    $modal.on('shown.bs.modal', function(e) {
        // Replacing checkbox with a hidden field.
        $('.redcap-user-profile [field="enabled"]').replaceWith('<input type="hidden" name="enabled" value="1">');
        $(this).removeClass('redcap-user-profile');
    });

    // Hiding checkbox during modal init.
    // If we only execute the "shown" trigger above, the checkbox will be
    // weirdly visible for a few moments before it disapears.
    $modal.on('show.bs.modal', function(e) {
        if ($(this).data('module') === 'redcap_user_profile') {
            $(this).addClass('redcap-user-profile');
        }
    });
});
