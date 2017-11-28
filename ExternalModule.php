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
use UserRights;

/**
 * ExternalModule class for User Profile module.
 */
class ExternalModule extends AbstractExternalModule {
    protected $projectId;
    protected $usernameField;

    /**
     * @inheritdoc
     */
    function __construct() {
        parent::__construct();

        $this->projectId = ExternalModules::getSystemSetting('redcap_user_profile', 'project_id');
        $this->usernameField = ExternalModules::getSystemSetting('redcap_user_profile', 'username_field');
    }

    /**
     * @inheritdoc
     */
    function hook_every_page_top($project_id) {
        // Initializing User Profile JS settings variable.
        echo '<script>var userProfile = {};</script>';

        if (
            strpos(PAGE, 'ExternalModules/manager/control_center.php') !== false ||
            strpos(PAGE, 'external_modules/manager/control_center.php') !== false
        ) {
            $this->includeJs('js/config.js');
            $this->includeCss('css/config.css');

            return;
        }

        if (PAGE == 'DataEntry/index.php') {
            if (!empty($_GET['user_profile_username']) && $project_id == $this->projectId) {
                global $Proj;

                // Setting default value for username when users get redirected
                // from "Create user profile" button.
                $Proj->metadata[$this->usernameField]['misc'] .= ' @DEFAULT="' . $_GET['user_profile_username'] . '"';
            }

            return;
        }

        if (PAGE != 'ControlCenter/view_users.php') {
            return;
        }

        $project = new Project($this->projectId);
        $settings = array(
            'nextProfileId' => $this->getAutoId(),
            'existingProfiles' => array(),
            'url' => APP_PATH_WEBROOT . 'DataEntry/index.php?pid=' . $this->projectId . '&event_id=' . $project->firstEventId . '&page=' . $project->firstForm,
        );

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
            $settings[$key] = '
                <button type="button" style="padding:1px 5px 2px 5px;" id="user-profile-btn">
                    <img src="' . APP_PATH_IMAGES . $btn_info['icon'] . '.png" style="vertical-align:middle;">
                    <span style="vertical-align:middle;">' . $btn_info['label'] . '</span>
                </button>';
        }

        $this->setJsSetting('addEditButtons', $settings);
        $this->includeJs('js/add_edit_buttons.js');
    }

    /**
     * Gets profile ID for a new entry.
     *
     * @return int
     *   The new profile ID.
     */
    function getAutoId() {
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/form_renderer_functions.php';

        if (defined('PROJECT_ID') && PROJECT_ID == $this->projectId) {
            return getAutoId();
        }

        // Since this function is called from the Control Center, this
        // constant won't hurt.
        define('PROJECT_ID', $this->projectId);

        // Fake project scope in order to call getAutoId().
        $GLOBALS['Proj'] = new Project($this->projectId);
        $GLOBALS['user_rights'] = UserRights::getPrivileges($this->projectId, USERID);

        $auto_id = getAutoId();

        // Cleaning project scope.
        unset($GLOBALS['Proj'], $GLOBALS['user_rights']);

        return $auto_id;
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
     * Includes a local CSS file.
     *
     * @param string $path
     *   The relative path to the css file.
     */
    protected function includeCss($path) {
        echo '<link rel="stylesheet" href="' . $this->getUrl($path) . '">';
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
        echo '<script>userProfile.' . $key . ' = ' . json_encode($value) . ';</script>';
    }
}
