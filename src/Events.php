<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App;

final class Events
{
    /**
     * @Event("App\Event\NewConferenceEvent")
     */
    const NEW_CONFERENCE_ADDED = 'app.new_conference_added';

    /**
     * @Event("App\Event\NewConferencesEvent")
     */
    const NEW_CONFERENCES_ADDED = 'app.new_conferences_added';

    /**
     * @Event("App\Event\CfpEndingSoonEvent")
     */
    const CFP_ENDING_SOON = 'app.cfp_ending_soon';

    /**
     * @Event("App\Event\SubmitStatusChangedEvent")
     */
    const SUBMIT_STATUS_CHANGED = 'app.submit_status_changed';

    /**
     * @Event("App\Event\NewTalkSubmittedEvent")
     */
    const NEW_TALK_SUBMITTED = 'app.new_talk_submitted';

    private function __construct()
    {
    }
}
