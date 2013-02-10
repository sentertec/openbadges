<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Displays user badges for badges management in own profile
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir . '/badgeslib.php');

$page        = optional_param('page', 0, PARAM_INT);
$perpage     = optional_param('perpage', 30, PARAM_INT);
$search      = optional_param('search', '', PARAM_CLEAN);
$clearsearch = optional_param('clearsearch', '', PARAM_TEXT);
$action      = optional_param('action', '', PARAM_TEXT);
$options     = optional_param_array('badges', array(), PARAM_TEXT);

require_login();
if (isguestuser()) {
    die();
}

if ($page < 0) {
    $page = 0;
}

if ($clearsearch) {
    $search = '';
}

if ($action && !empty($options)) {
    list($sql, $params) = $DB->get_in_or_equal($options);
    if ($action == 'hide') {
        $DB->set_field_select('badge_issued', 'visible', 0, "uniquehash $sql", $params);
    } else if ($action == 'show') {
        $DB->set_field_select('badge_issued', 'visible', 1, "uniquehash $sql", $params);
    } else if ($action == 'download') {
        ob_start();
        download_badges($USER->id, $options);
        ob_flush();
    }
}

$context = context_user::instance($USER->id);
require_capability('moodle/badges:manageownbadges', $context);

$url = new moodle_url('/badges/mybadges.php');

$PAGE->set_url($url);
$PAGE->set_context($context);

$title = get_string('mybadges', 'badges');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('mydashboard');

$output = $PAGE->get_renderer('core', 'badges');
$badges = get_user_badges($USER->id);

echo $OUTPUT->header();
$totalcount = count($badges);
$records = get_user_badges($USER->id, null, $page, $perpage, $search);

if ($totalcount) {
    $userbadges             = new badge_user_collection($records, $USER->id);
    $userbadges->sort       = 'dateissued';
    $userbadges->dir        = 'DESC';
    $userbadges->page       = $page;
    $userbadges->perpage    = $perpage;
    $userbadges->totalcount = $totalcount;
    $userbadges->search     = $search;

    echo $output->render($userbadges);
} else {
    echo $output->notification(get_string('nobadges', 'badges'));
}

echo $OUTPUT->footer();