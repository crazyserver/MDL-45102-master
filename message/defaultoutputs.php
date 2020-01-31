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
 * Default message outputs configuration page
 *
 * @package   core_message
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @deprecated since Moodle 3.7 MDL-64495. Please use /admin/message.php instead.
 * @todo       MDL-64866 This will be deleted in Moodle 4.1.
 */
require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/message/lib.php');
require_once($CFG->libdir.'/adminlib.php');

// This is an admin page
admin_externalpage_setup('defaultmessageoutputs');

// Fetch processors
$processors = get_message_processors(true);
// Fetch message providers
$providers = get_message_providers();

if (($form = data_submitted()) && confirm_sesskey()) {
    $newpreferences = array();
    // Prepare default message outputs settings.
    foreach ($providers as $provider) {
        $componentproviderbase = $provider->component.'_'.$provider->name;
        $disableprovidersetting = $componentproviderbase.'_disable';
        if (!isset($form->$disableprovidersetting)) {
            $newpreferences[$disableprovidersetting] = 1;
        } else {
            $newpreferences[$disableprovidersetting] = 0;
        }

        $componentprovidersetting = $componentproviderbase.'_locked';
        foreach ($processors as $processor) {
            $value = 0;
            if (isset($form->{$componentprovidersetting}[$processor->name])) {
                $value = $form->{$componentprovidersetting}[$processor->name];
                if ($value == 'on') {
                    $value = 1;
                }
            }

            // Record the site preference.
            $newpreferences[$processor->name.'_provider_'.$componentprovidersetting] = $value;
        }

        $componentprovidersetting = $componentproviderbase.'_enabled';
        $newsettings = array();
        if (isset($form->$componentprovidersetting)) {
            // Store defained comma-separated processors as setting value.
            // Using array_filter eliminates elements set to 0 above.
            $newsettings = array_keys(array_filter($form->{$componentprovidersetting}));
        }

        // Let's join existing setting values for disabled processors.
        $property = 'message_provider_'.$componentprovidersetting;
        if (property_exists($preferences, $property)) {
            $existingsetting = $preferences->$property;
            foreach ($disabledprocessors as $disable) {
                if (strpos($existingsetting, $disable->name) > -1) {
                    $newsettings[] = $disable->name;
                }
            }
        }

        $value = join(',', $newsettings);
        if (empty($value)) {
            $value = null;
        }

        // Record the site preference.
        $newpreferences['message_provider_'.$componentprovidersetting] = $value;
    }

    // Update database
    $transaction = $DB->start_delegated_transaction();
    foreach ($newpreferences as $name => $value) {
        set_config($name, $value, 'message');
    }
    $transaction->allow_commit();

    // Redirect
    $url = new moodle_url('defaultoutputs.php');
    redirect($url);
}



// Page settings
$PAGE->set_context(context_system::instance());

// Grab the renderer
$renderer = $PAGE->get_renderer('core', 'message');

// Display the manage message outputs interface
$preferences = get_message_output_default_preferences();
$messageoutputs = $renderer->manage_defaultmessageoutputs($processors, $providers, $preferences);

// Display the page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('defaultmessageoutputs', 'message'));
echo $messageoutputs;
echo $OUTPUT->footer();
