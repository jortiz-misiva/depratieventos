// External Dependencies
import React, { Component } from 'react';
// Internal Dependencies
import './style.css';


class EventCountDown extends Component {

	static slug = 'MDSB_EventCountDown';
	static custom_scopes = {};
	render() {
		if (!this.custom_scopes) {
			this.custom_scopes = window['MDSB_EventCountDown'];
		}
		const renderHTML = (rawHTML: string) => React.createElement("div", { dangerouslySetInnerHTML: { __html: rawHTML } });
		return (
			<div className="mec-event-meta">
				<div className="mec-events-meta-group mec-events-meta-group-countdown">
					{renderHTML(this.custom_scopes.CountDown)}
				</div>
			</div>
		);
	}
}

export default EventCountDown;
