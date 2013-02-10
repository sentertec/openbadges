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
 * Page to handle actions associated with badges management.
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
$copy = optional_param('copy', 0, PARAM_BOOL);
$clear = optional_param('clear', 0, PARAM_BOOL);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$activate = optional_param('activate', 0, PARAM_BOOL);
$deactivate = optional_param('lock', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);

require_login();

$badge = new badge($badgeid);
$context = $badge->get_context();
$navurl = new moodle_url('/badges/index.php', array('type' => $badge->context));

if ($badge->context == BADGE_TYPE_COURSE) {
    require_login($badge->courseid);
    $navurl = new moodle_url('/badges/index.php', array('type' => $badge->context, 'id' => $badge->courseid));
}

$PAGE->set_context($context);
$PAGE->set_url('/badges/action.php', array('id' => $badge->id));
$PAGE->set_pagelayout('standard');
navigation_node::override_active_url($navurl);

$returnurl = new moodle_url('/badges/overview.php', array('id' => $badge->id));

if ($delete) {
    require_capability('moodle/badges:deletebadge', $context);

    $PAGE->url->param('delete', 1);
    if ($confirm && confirm_sesskey()) {
        $badge->delete();
        redirect(new moodle_url('/badges/index.php', array('type' => $badge->context, 'id' => $badge->courseid)));
    }

    $strheading = get_string('delbadge', 'badges');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($badge->name);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);

    $urlparams = array(
        'id' => $badge->id,
        'delete' => 1,
        'confirm' => 1,
        'sesskey' => sesskey()
    );
    $continue = new moodle_url('/badges/action.php', $urlparams);

    $message = get_string('delconfirm', 'badges', $badge->name);
    echo $OUTPUT->confirm($message, $continue, $returnurl);
    echo $OUTPUT->footer();
    die;
}

if ($clear) {
    require_capability('moodle/badges:configurecriteria', $context);

    $returnurl = new moodle_url('/badges/criteria.php', array('id' => $badge->id));
    $PAGE->url->param('clear', 1);

    if ($confirm && confirm_sesskey()) {
        $badge->clear_criteria();
        redirect($returnurl);
    }

    $strheading = get_string('clearbadge', 'badges');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($badge->name);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);

    $urlparams = array(
        'id' => $badge->id,
        'clear' => 1,
        'confirm' => 1,
        'sesskey' => sesskey()
    );
    $continue = new moodle_url('/badges/action.php', $urlparams);

    $message = get_string('clearconfirm', 'badges');
    echo $OUTPUT->confirm($message, $continue, $returnurl);
    echo $OUTPUT->footer();
    die;
}

if ($copy) {
    require_capability('moodle/badges:createbadge', $context);

    $cloneid = $badge->make_clone();
    redirect(new moodle_url('/badges/edit.php', array('id' => $cloneid, 'action' => 'details')));
}

if ($activate) {
    require_capability('moodle/badges:configurecriteria', $context);

    $PAGE->url->param('activate', 1);
    $status = ($badge->status == BADGE_STATUS_INACTIVE) ? BADGE_STATUS_ACTIVE : BADGE_STATUS_ACTIVE_LOCKED;
    if ($confirm == 1 && confirm_sesskey()) {
        $badge->set_status($status);
        $badge->review_all_criteria();
        redirect($returnurl);
    }

    $strheading = get_string('reviewbadge', 'badges');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($badge->name);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);

    $params = array('id' => $badge->id, 'activate' => 1, 'sesskey' => sesskey(), 'confirm' => 1);
    $url = new moodle_url('/badges/action.php', $params);

    if (!$badge->has_criteria()) {
        echo $OUTPUT->notification(get_string('error:cannotact', 'badges') . get_string('nocriteria', 'badges'));
        echo $OUTPUT->continue_button($returnurl);
    } else {
        $message = get_string('reviewconfirm', 'badges', $badge->name);
        echo $OUTPUT->confirm($message, $url, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

if ($deactivate) {
    require_capability('moodle/badges:configurecriteria', $context);

    $status = ($badge->status == BADGE_STATUS_ACTIVE) ? BADGE_STATUS_INACTIVE : BADGE_STATUS_INACTIVE_LOCKED;
    $badge->set_status($status);
    redirect($returnurl);
}
