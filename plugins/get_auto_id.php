<?php
/**
 * @file
 * Provides getAutoId() value to outsite the project's scope.
 */

define('NOAUTH', true);

require_once dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))) . '/redcap_connect.php';
require_once APP_PATH_DOCROOT . 'ProjectGeneral/form_renderer_functions.php';

$result = isset($_GET['pid']) ? array('success' => true, 'result' => getAutoId()) : array('success' => false);
echo json_encode($result);
