<?php
/**
 * @file
 * Provides ExternalModule class for User Profile module.
 */

namespace UserProfile\ExternalModule;

require_once 'UserProfile.php';

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;
use UserProfile\UserProfile;
use Project;

/**
 * ExternalModule class for User Profile module.
 */
class ExternalModule extends AbstractExternalModule {

    /**
     * @inheritdoc
     */
    function hook_every_page_top($project_id) {
        if (!empty($_GET['user_profile_username']) && PAGE == 'DataEntry/index.php' && $project_id == ExternalModules::getSystemSetting('redcap_user_profile', 'project_id')) {
            global $Proj;
            $field = ExternalModules::getSystemSetting('redcap_user_profile', 'username_field');
            $Proj->metadata[$field]['misc'] .= ' @DEFAULT="' . $_GET['user_profile_username'] . '"';

            return;
        }

        if (PAGE != 'ControlCenter/view_users.php') {
            return;
        }

        $project_id = ExternalModules::getSystemSetting('redcap_user_profile', 'project_id');
        $project = new Project($project_id);

        $settings = array(
            'nextProfileId' => 1,
            'existingProfiles' => array(),
            'url' => APP_PATH_WEBROOT . 'DataEntry/index.php?pid=' . $project_id . '&event_id=' . $project->firstEventId . '&page=' . $project->firstForm,
        );

        $q = db_query('SELECT record FROM redcap_data WHERE project_id = ' . $project_id . ' ORDER BY record DESC LIMIT 1');

        if (db_num_rows($q)) {
            $result = db_fetch_assoc($q);
            // TODO: improve this - it should behave like getAutoId().
            $settings['nextProfileId'] += $result['record'];
        }

        foreach (UserProfile::getProfiles() as $username => $user_profile) {
            $settings['existingProfiles'][$username] = $user_profile->getProfileId();
        }

        $buttons = array(
            'addButton' => array(
                'icon' => 'user_add3',
                'label' => 'Create user profile',
            ),
            'editButton' => array(
                'icon' => 'user_edit',
                'label' => 'Edit user profile',
            ),
        );

        foreach ($buttons as $key => $btn_info) {
            $settings[$key] = '<button type="button" style="padding:1px 5px 2px 5px;" id="user-profile-btn">';
            $settings[$key] .= '<img src="' . APP_PATH_IMAGES . $btn_info['icon'] . '.png" style="vertical-align:middle;">';
            $settings[$key] .= '<span style="vertical-align:middle;">' . $btn_info['label'] . '</span></button>';
        }

        $this->setJsSetting('addEditButtons', $settings);
        $this->includeJs('js/add_edit_buttons.js');
    }

    /**
     * Includes a local JS file.
     *
     * @param string $path
     *   The relative path to the js file.
     */
    protected function includeJs($path) {
        echo '<script src="' . $this->getUrl($path) . '"></script>';
    }

    /**
     * Sets a JS setting.
     *
     * @param string $key
     *   The setting key to be appended to the module settings object.
     * @param mixed $value
     *   The setting value.
     */
    protected function setJsSetting($key, $value) {
        echo '<script>userProfile = {' . $key . ': ' . json_encode($value) . '};</script>';
    }
}
