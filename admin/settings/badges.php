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
* This file defines settingpages and externalpages under the "badges" section
*
* @package    core
* @subpackage badges
* @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
* @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
*/

global $SITE;
require_once($CFG->libdir . '/badgeslib.php');

if ($hassiteconfig) {
    $ADMIN->add('badges',
            new admin_externalpage('managebadges',
                    new lang_string('managebadges', 'badges'),
                    new moodle_url($CFG->wwwroot . '/badges/index.php', array('type' => BADGE_TYPE_SITE)),
                    array('moodle/badges:viewawarded')
            )
    );

    $ADMIN->add('badges',
            new admin_externalpage('newbadge',
                    new lang_string('newbadge', 'badges'),
                    new moodle_url($CFG->wwwroot . '/badges/newbadge.php', array('type' => BADGE_TYPE_SITE)),
                    array('moodle/badges:createbadge')
            )
    );

    $globalsettings = new admin_settingpage('badgesettings', new lang_string('badgesettings', 'badges'),
            array('moodle/badges:manageglobalsettings'));

    $globalsettings->add(new admin_setting_configtext('badges_defaultissuername',
            new lang_string('defaultissuername', 'badges'),
            new lang_string('defaultissuername_desc', 'badges'),
            $SITE->fullname ? $SITE->fullname : $SITE->shortname, PARAM_TEXT));

    $globalsettings->add(new admin_setting_configtext('badges_defaultissuerurl',
            new lang_string('defaultissuerurl', 'badges'),
            new lang_string('defaultissuerurl_desc', 'badges'),
            $CFG->wwwroot, PARAM_TEXT));

    $globalsettings->add(new admin_setting_configtext('badges_defaultissuercontact',
            new lang_string('defaultissuercontact', 'badges'),
            new lang_string('defaultissuercontact_desc', 'badges'),
            get_config('moodle','supportemail'), PARAM_TEXT));

    $globalsettings->add(new admin_setting_configtext('badges_defaultbadgesalt',
            new lang_string('defaultbadgesalt', 'badges'),
            new lang_string('defaultbadgesalt_desc', 'badges'),
            'badges101', PARAM_ALPHANUM));

    $globalsettings->add(new admin_setting_configcheckbox('badges_allowexternalbackpack',
            new lang_string('allowexternalbackpack', 'badges'),
            new lang_string('allowexternalbackpack_desc', 'badges'), 1));

    $globalsettings->add(new admin_setting_configcheckbox('badges_allowcoursebadges',
            new lang_string('allowcoursebadges', 'badges'),
            new lang_string('allowcoursebadges_desc', 'badges'), 1));

    $ADMIN->add('badges', $globalsettings);
}
