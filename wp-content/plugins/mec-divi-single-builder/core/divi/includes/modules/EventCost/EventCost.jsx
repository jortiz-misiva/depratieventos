// External Dependencies
import React, { Component } from 'react';
// Internal Dependencies
import './style.css';


class EventCost extends Component {

	static slug = 'MDSB_EventCost';
	static custom_scopes = {};
	render() {
		if (!this.custom_scopes) {
			this.custom_scopes = window['MDSB_EventCost'];
		}
		return (
			<div className="mec-event-meta">
				<div className="mec-event-cost">
					<i className="mec-sl-wallet"></i>
					<h3 className="mec-cost">{this.custom_scopes.Cost}</h3>
					<dd className="mec-events-event-cost">{this.custom_scopes.EventCost}</dd>
				</div>
			</div>
		);
	}
}

export default EventCost;
