$(document).ready(function() {
    ExternalModules.Settings.prototype.configureSettingsOld = ExternalModules.Settings.prototype.configureSettings;
    ExternalModules.Settings.prototype.configureSettings = function() {
        ExternalModules.Settings.prototype.configureSettingsOld();

        var $modal = $('#external-modules-configure-modal');
        if ($modal.data('module') !== userProfile.modulePrefix) {
            return;
        }

        $modal.find('[field="enabled"]').replaceWith('<input type="hidden" name="enabled" value="1">');
    }
});
