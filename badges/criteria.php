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
 * Editing badge details, criteria, messages
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir . '/badgeslib.php');

$badgeid = required_param('id', PARAM_INT);
$update = optional_param('update', 0, PARAM_INT);

require_login();

$badge = new badge($badgeid);
$context = $badge->get_context();
$navurl = new moodle_url('/badges/index.php', array('type' => $badge->context));

if ($badge->context == BADGE_TYPE_COURSE) {
    require_login($badge->courseid);
    $navurl = new moodle_url('/badges/index.php', array('type' => $badge->context, 'id' => $badge->courseid));
}

$currenturl = qualified_me();

$PAGE->set_context($context);
$PAGE->set_url($currenturl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($badge->name);
$PAGE->set_title($badge->name);

// Set up navigation and breadcrumbs.
navigation_node::override_active_url($navurl);
$PAGE->navbar->add($badge->name);

$output = $PAGE->get_renderer('core', 'badges');
$msg = optional_param('msg', '', PARAM_TEXT);
$emsg = optional_param('emsg', '', PARAM_TEXT);

if ((($update == 1) || ($update == 2)) && confirm_sesskey()) {
    require_capability('moodle/badges:configurecriteria', $context);
    $obj = new stdClass();
    $obj->id = $badge->criteria[BADGE_CRITERIA_TYPE_OVERALL]->id;
    $obj->method = $update;
    if ($DB->update_record('badge_criteria', $obj)) {
        $msg = get_string('changessaved');
    } else {
        $emsg = get_string('error:save', 'badges');
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading($badge->name . ': ' . get_string('bcriteria', 'badges'));

if ($emsg !== '') {
    echo $OUTPUT->notification($emsg);
} else if ($msg !== '') {
    echo $OUTPUT->notification($msg, 'notifysuccess');
}

$output->print_badge_tabs($badgeid, $context, 'criteria');

if (!$badge->is_locked() && !$badge->is_active()) {
    echo $output->print_criteria_actions($badge);
}

if ($badge->has_criteria()) {
    ksort($badge->criteria);

    foreach ($badge->criteria as $crit) {
        $crit->config_form_criteria($badge);
    }
}

echo $OUTPUT->footer();