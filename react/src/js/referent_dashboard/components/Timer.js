import React, { Component } from 'react';

class Timer extends Component {
    constructor() {
        super();
        const d = new Date();
        this.state = {
            time: d.toLocaleTimeString(),
        };
        this.countingSecond = this.countingSecond.bind(this);
    }

    countingSecond() {
        const d = new Date();
        this.setState({
            time: d.toLocaleTimeString(),
        });
    }

    componentDidMount() {
        setInterval(this.countingSecond, 1000);
    }

    componentDidUpdate() {
        clearInterval(this.timer);
    }

    render() {
        return <p>{this.state.time}</p>;
    }
}
export default Timer;
