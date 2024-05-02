// External Dependencies
import React, { Component } from 'react';
// Internal Dependencies
import './style.css';


class EventTime extends Component {

	static slug = 'MDSB_EventTime';
	static custom_scopes = {};
	render() {
		if (!this.custom_scopes) {
			this.custom_scopes = window['MDSB_EventTime'];
		}
		return (
			<div className="mec-event-meta">
				<div className="mec-single-event-time">
                    <i className="mec-sl-clock"></i>
                    <h3 className="mec-time">{this.custom_scopes.translates.Time}</h3>
                    <i className="mec-time-comment">{this.custom_scopes.TimeComment}</i>
					<dd><abbr className="mec-events-abbr">{this.custom_scopes.EventsAbbr}</abbr></dd>
                </div>
			</div>
		);
	}
}

export default EventTime;
