// External Dependencies
import React, { Component } from 'react';
// Internal Dependencies
import './style.css';


class EventNextPrevious extends Component {

	static slug = 'MDSB_EventNextPrevious';
	static custom_scopes = {};
	render() {
		if (!this.custom_scopes) {
			this.custom_scopes = window['MDSB_EventNextPrevious'];
		}
		const renderHTML = (rawHTML: string) => React.createElement("div", { dangerouslySetInnerHTML: { __html: rawHTML } });
		return (
			<div className="mec-event-meta">
				{renderHTML(this.custom_scopes.content)}
			</div>
		);
	}
}

export default EventNextPrevious;
