// External Dependencies
import React, { Component } from 'react';
// Internal Dependencies
import './style.css';


class EventContent extends Component {

	static slug = 'MDSB_EventContent';
	static custom_scopes = {};
	render() {
		if (!this.custom_scopes) {
			this.custom_scopes = window['MDSB_EventContent'];
		}
		const renderHTML = (rawHTML: string) => React.createElement("div", { dangerouslySetInnerHTML: { __html: rawHTML } });
		return (
			<div className="mec-event-meta">
				<div className="mec-single-event-description mec-events-content">
					{renderHTML(this.custom_scopes.content)}
				</div>
			</div>
		);
	}
}

export default EventContent;
