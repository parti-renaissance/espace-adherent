import React, { Component } from 'react';
import Nav from './../components/Nav';
import Header from './../components/Header';

class Layout extends Component {
    render() {
        return (
            <div>
                <Header name="Mickaël-Ange" />
                <Nav />
                {this.props.children}
            </div>
        );
    }
}

export default Layout;
