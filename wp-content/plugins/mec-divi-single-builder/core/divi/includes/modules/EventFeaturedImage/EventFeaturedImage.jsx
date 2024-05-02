// External Dependencies
import React, { Component } from 'react';
// Internal Dependencies
import './style.css';


class EventFeaturedImage extends Component {

	static slug = 'MDSB_EventFeaturedImage';
	static custom_scopes = {};
	render() {
		if (!this.custom_scopes) {
			this.custom_scopes = window['MDSB_EventFeaturedImage'];
		}
		const renderHTML = (rawHTML: string) => React.createElement("div", { dangerouslySetInnerHTML: { __html: rawHTML } });
		return (
			<div className="mec-event-meta">
				<div className="mec-events-event-image">
					{renderHTML(this.custom_scopes.thumbnail)}
				</div>
			</div>
		);
	}
}

export default EventFeaturedImage;
