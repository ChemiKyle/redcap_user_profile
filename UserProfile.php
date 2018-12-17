<?php
/**
 * @file
 * Provides User Profile class.
 */

namespace UserProfile;

use ExternalModules\ExternalModules;
use Project;
use Records;
use REDCap;

/**
 * User Profile class.
 */
class UserProfile {
    protected $projectId;
    protected $username;
    protected $usernameField;
    protected $profileId;
    protected $profileData;

    /**
     * Gets a list of existing profiles.
     *
     * @return array
     *   An array of UserProfile objects keyed by username.
     */
    public static function getProfiles() {
        $pid = ExternalModules::getSystemSetting('redcap_user_profile', 'project_id');
        $field = ExternalModules::getSystemSetting('redcap_user_profile', 'username_field');
        $data = REDCap::getData($pid, 'array', null, $field);

        $profiles = array();
        foreach ($data as $username) {
            // Since the result is given a record-event-value nesting
            // structure, we need to get rid of the first two array levels in
            // order to get the username.
            $username = reset($username);
            $username = reset($username);

            $profiles[$username] = new UserProfile($username);
        }

        return $profiles;
    }

    /**
     * Creates a new user profile.
     *
     * @param mixed $data
     *   The user profile data array or the username.
     *
     * @return bool
     *   TRUE if success, FALSE otherwise.
     */
    public static function createProfile($data) {
        $module = ExternalModules::getModuleInstance('redcap_user_profile');
        $username_field = $module->getSystemSetting('username_field');

        if (is_string($data)) {
            $data = array($username_field => $data);
        }
        elseif (!isset($data[$username_field])) {
            REDCap::logEvent('User profile creation failed', 'No username provided.');
            return false;
        }

        $username = db_escape($data[$username_field]);
        $project_id = $module->getSystemSetting('project_id');

        if (REDCap::getData($project_id, 'array', null, $username_field, null, null, false, false, false, '[' . $username_field . '] = "' . $username . '"')) {
            REDCap::logEvent('User profile creation failed', 'User profile "' . $username . '" already exists.');
            return false;
        }

        // Checking whether input fields are valid.
        $project = new Project($project_id);
        foreach (array_keys($data) as $field_name) {
            if (!isset($project->metadata[$field_name])) {
                unset($data[$field_name]);
            }
        }

        $data = array(
            $module->getAutoId() => array(
                $project->firstEventId => $data + array(
                    $project->firstForm . '_complete' => 2,
                ),
            ),
        );

        $result = Records::saveData($project_id, 'array', $data);

        if (!is_array($result) || !empty($result['errors']) || empty($result['ids'])) {
            $msg = !is_array($result) || empty($result['errors']) ? 'Data could not be saved.' : json_encode($result['errors']);
            REDCap::logEvent('User profile creation failed', $msg);
            return false;
        }

        REDCap::logEvent('User profile created', 'Username: "' . $username . '"');
        return true;
    }

    /**
     * Constructor.
     */
    public function __construct($username, $set_profile_data = true) {
        $module_name = 'redcap_user_profile';

        $this->username = $username;
        $this->usernameField = ExternalModules::getSystemSetting($module_name, 'username_field');
        $this->projectId = ExternalModules::getSystemSetting($module_name, 'project_id');
        $this->setProfileId();

        if ($set_profile_data) {
            $this->setProfileData();
        }
    }

    /**
     * Gets profile username.
     *
     * @return string
     *   The profile username.
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Gets profile ID.
     *
     * @return int
     *   The profile record ID.
     */
    public function getProfileId() {
        return $this->profileId;
    }

    /**
     * Gets profile data.
     *
     * @return array
     *   A keyed array containing the profile information.
     */
    public function getProfileData() {
        return $this->profileData;
    }

    /**
     * Gets project ID.
     *
     * @return int
     *   The profile project ID.
     */
    public function getProjectId() {
        return $this->projectId;
    }

    /**
     * Gets username field name.
     *
     * @return string
     *   The username field name.
     */
    public function getUsernameField() {
        return $this->usernameField;
    }

    /**
     * Sets profile ID.
     */
    protected function setProfileId() {
        $sql = '
            SELECT record FROM redcap_data
            WHERE
                field_name = "' . db_real_escape_string($this->usernameField) . '" AND
                project_id = "' . intval($this->projectId) . '" AND
                value = "' . db_real_escape_string($this->username) . '"
            LIMIT 1';

        $q = db_query($sql);
        if (!db_num_rows($q)) {
            return;
        }

        $result = db_fetch_assoc($q);
        $this->profileId = $result['record'];
    }

    /**
     * Sets profile data.
     */
    protected function setProfileData() {
        if (empty($this->profileId)) {
            return;
        }

        $data = REDCap::getData($this->projectId, 'array', $this->profileId);
        $project = new Project($this->projectId);

        // Taking the first and only event (the concept of events does not apply
        // to user profiles.
        $data = reset($data);

        // Getting repeat instruments.
        $repeat_instances = array();
        if (isset($data['repeat_instances']) && isset($data['repeat_instances'][$project->firstEventId])) {
            $repeat_instances = $data['repeat_instances'][$project->firstEventId];
        }

        $data = $data[$project->firstEventId];

        $user_profile = array();
        foreach ($project->forms as $form_key => $form) {
            if (!in_array($form_key, $project->eventsForms[$project->firstEventId])) {
                continue;
            }

            $user_profile[$form_key] = array();

            if (isset($repeat_instances[$form_key])) {
                // Handling repeat instruments.
                foreach ($repeat_instances[$form_key] as $instance_data) {
                    $row = array();
                    foreach (array_keys($form['fields']) as $field) {
                        $row[$field] = $instance_data[$field];
                    }

                    // Removing "complete?" field from repeat instrument data.
                    unset($row[$form_key . '_complete']);

                    $user_profile[$form_key][] = $row;
                }
            }
            else {
                foreach (array_keys($form['fields']) as $field) {
                    $user_profile[$form_key][$field] = $data[$field];
                }
            }

            // Removing "complete?" field from profile data.
            unset($user_profile[$form_key][$form_key . '_complete']);
        }

        // Removing record ID from profile data.
        unset($user_profile[$project->firstForm][$project->table_pk]);

        if (count($user_profile) == 1) {
            // If there is only one instrument, remove the first tree level.
            $user_profile = reset($user_profile);
        }

        $this->profileData = $user_profile;
    }
}
