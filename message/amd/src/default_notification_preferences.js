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
 * Controls the default settings for the list of notification types on the
 * notifications admin page
 *
 * @module     core_message/default_notification_preferences
 * @class      default_notification_preferences
 * @package    message
 * @copyright  2020 Pau Ferrer Ocaña <pau@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
        ],
        function(
          $,
        ) {

    var SELECTORS = {
        PROVIDER: '.defaultmessageoutputs .provider_enabled',
        PROVIDER_DISABLED: '.defaultmessageoutputs .provider_enabled:not(:disabled)',
        LOCK_SETTING: '.locked_message_setting',
        ENABLED_SETTING: '.enabled_message_setting',
        ALL_SETTINGS: '.locked_message_setting, .enabled_message_setting'
    };

    /**
     * Constructor for the Preference.
     */
    var NotificationPreference = function() {
        this.registerEventListeners();

        $(SELECTORS.PROVIDER_DISABLED).each(function(i, e) {
            this.toggleDisableProviderSettings(e);
        }.bind(this));
    };

    /**
      * Update the disable the enabled toggle on the notification setting.
      *
      * @method toggleDisableProviderSettings
      * @param {object} element jQuery object that receives the event.
      * @return {Promise}
      */
    NotificationPreference.prototype.toggleLockSetting = function(e) {
        var isenabled = $(e).prop('checked') || false;
        $(e).closest('td').find(SELECTORS.ENABLED_SETTING).closest('div').toggleClass('dimmed_text', isenabled);
    };

    /**
      * Update the disable all notifications of the provider.
      * @method toggleDisableProviderSettings
      * @param {object} element jQuery object that receives the event.
      * @return {Promise}
      */
    NotificationPreference.prototype.toggleDisableProviderSettings = function(e) {
        var isenabled = $(e).prop('checked') || false;
        var parentNode = $(e).closest('tr');

        parentNode.find(SELECTORS.ALL_SETTINGS).prop('disabled', !isenabled);
    };

    /**
      * Set up all of the event listeners for the NotificationPreference.
      *
      * @method registerEventListeners
      */
    NotificationPreference.prototype.registerEventListeners = function() {
        $(SELECTORS.LOCK_SETTING).on('change', function(e) {
            this.toggleLockSetting(e.target);
        }.bind(this));

        $(SELECTORS.PROVIDER).on('change', function(e) {
            this.toggleDisableProviderSettings(e.target);
        }.bind(this));
    };

    return NotificationPreference;
});
