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
 * script for bulk user that converts first letter of each firstname and lastname to capital
 *
 * @author  Pau Ferrer Ocaña (pferre22 / crazyserver)
 * @copyright  2014 Generalitat de Catalunya, UPCnet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);

require_login();
admin_externalpage_setup('userbulk');
require_capability('moodle/user:editprofile', context_system::instance());

$return = new moodle_url('/'.$CFG->admin.'/user/user_bulk.php');

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

if ($confirm and confirm_sesskey()) {
    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $rs = $DB->get_recordset_select('user', "id $in", $params);
    foreach ($rs as $user) {
        $user->firstname = core_text::strtotitle($user->firstname);
        $user->lastname = core_text::strtotitle($user->lastname);
        $user->timemodified = time();

        if (!is_siteadmin($user) and $USER->id != $user->id) {
            $DB->update_record('user', $user);
            unset($SESSION->bulk_users[$user->id]);
        } else {
            $OUTPUT->notification(get_string('ucwordsnot', 'admin', fullname($user, true)));
        }
    }
    $rs->close();
    redirect($return, get_string('changessaved'));

} else {
    echo $OUTPUT->header();
    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $userlist = $DB->get_records_select_menu('user', "id $in", $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
    $usernames = implode(', ', $userlist);

    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    $formcontinue = new single_button(new moodle_url('user_bulk_ucwords.php', array('confirm' => 1)), get_string('yes'));
    $formcancel = new single_button(new moodle_url('user_bulk.php'), get_string('no'), 'get');
    echo $OUTPUT->confirm(get_string('ucwordscheckfull', 'admin', $usernames), $formcontinue, $formcancel);

    echo $OUTPUT->footer();
}
