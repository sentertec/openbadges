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
 * Badge awards information
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir . '/badgeslib.php');

$badgeid    = required_param('id', PARAM_INT);
$sortby     = optional_param('sort', 'dateissued', PARAM_ALPHA);
$sorthow    = optional_param('dir', 'DESC', PARAM_ALPHA);
$page       = optional_param('page', 0, PARAM_INT);
$updatepref = optional_param('updatepref', false, PARAM_BOOL);

require_login();

if (!in_array($sortby, array('firstname', 'lastname', 'dateissued'))) {
    $sortby = 'dateissued';
}

if ($sorthow != 'ASC' and $sorthow != 'DESC') {
    $sorthow = 'DESC';
}

if ($page < 0) {
    $page = 0;
}

$badge = new badge($badgeid);
$context = $badge->get_context();
$navurl = new moodle_url('/badges/index.php', array('type' => $badge->context));

if ($badge->context == BADGE_TYPE_COURSE) {
    require_login($badge->courseid);
    $navurl = new moodle_url('/badges/index.php', array('type' => $badge->context, 'id' => $badge->courseid));
}

$PAGE->set_context($context);
$PAGE->set_url('/badges/recipients.php', array('id' => $badgeid, 'sort' => $sortby, 'dir' => $sorthow));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($badge->name);
$PAGE->set_title($badge->name);
$PAGE->navbar->add($badge->name);
navigation_node::override_active_url($navurl);

$output = $PAGE->get_renderer('core', 'badges');

echo $output->header();
echo $output->heading($badge->name . ': ' . get_string('awards', 'badges'));

$output->print_badge_tabs($badgeid, $context, 'awards');

$sql = "SELECT b.userid, b.dateissued, b.uniquehash, u.firstname, u.lastname
    FROM {badge_issued} b INNER JOIN {user} u
        ON b.userid = u.id
    WHERE b.badgeid = :badgeid
    ORDER BY $sortby $sorthow";

$totalcount = $DB->count_records('badge_issued', array('badgeid' => $badge->id));

if ($badge->has_awards()) {
    $users = $DB->get_records_sql($sql, array('badgeid' => $badge->id), $page * BADGE_PERPAGE, BADGE_PERPAGE);
    $recipients             = new badge_recipients($users);
    $recipients->sort       = $sortby;
    $recipients->dir        = $sorthow;
    $recipients->page       = $page;
    $recipients->perpage    = BADGE_PERPAGE;
    $recipients->totalcount = $totalcount;

    echo $output->render($recipients);
} else {
    echo $output->notification(get_string('noawards', 'badges'));
}

echo $output->footer();