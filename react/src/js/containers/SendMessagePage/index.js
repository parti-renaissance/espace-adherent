import React, { Component } from "react";
import { withRouter } from "react-router";

class SendMessagePage extends Component {
    render() {
        return (
            <div className="wrapper">
                <h2>SEND A MESSAGE</h2>
            </div>
        );
    }
}

export default withRouter(SendMessagePage);
