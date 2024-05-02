// External Dependencies
import React, { Component } from 'react';
// Internal Dependencies
import './style.css';


class EventTitle extends Component {

	static slug = 'MDSB_EventTitle';
	static custom_scopes = {};
	render() {
		if (!this.custom_scopes) {
			this.custom_scopes = window['MDSB_EventTitle'];
		}
		if (typeof(this.props.header_level) === "undefined") {
			this.props.header_level = 'h1';
		}
		const HeaderLevel = this.props.header_level;
		return (
			<div className="mec-event-meta">
				<div className="mec-single-event-title">
					<HeaderLevel className="mec-single-title">
						{this.custom_scopes.title}
					</HeaderLevel>
				</div>
			</div>
		);
	}
}

export default EventTitle;
