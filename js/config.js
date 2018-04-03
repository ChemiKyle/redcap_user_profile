$(document).ready(function() {
    $modal = $('#external-modules-configure-modal');
    $modal.on('show.bs.modal', function() {
        // Making sure we are overriding this modules's modal only.
        if ($modal.data('module') !== userProfile.modulePrefix) {
            return;
        }

        if (typeof ExternalModules.Settings.prototype.configureSettingsOld === 'undefined') {
            ExternalModules.Settings.prototype.configureSettingsOld = ExternalModules.Settings.prototype.configureSettings;
        }

        ExternalModules.Settings.prototype.configureSettings = function() {
            ExternalModules.Settings.prototype.configureSettingsOld();

            // Making sure we are overriding this modules's modal only.
            if ($modal.data('module') === userProfile.modulePrefix) {
                $modal.find('tr[field="enabled"] input').attr('disabled', 'disabled').attr('title', 'This module needs to be enabled for all projects.');
            }
        };
    });
});
