import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import TextArea from '.';

class ControlledTextArea extends React.Component {
    constructor(props) {
        super(props);
        this.state = { value: '' };
    }

    render() {
        return (
            <TextArea
                {...this.props}
                onChange={value => this.setState({ value })}
                value={this.state.value}
            />
        );
    }
}

storiesOf('TextArea', module)
    .addParameters({ jest: ['TextArea'] })
    .add('default', () => (
        <TextArea
            id="text-area"
            name="text-area"
            onChange={action('text area change')}
        />
    ))
    .add('with value', () => (
        <TextArea
            id="text-area"
            name="text-area"
            onChange={action('text area change')}
            value="Super text"
        />
    ))
    .add('with maxLength', () => (
        <TextArea
            id="text-area"
            name="text-area"
            maxLength={10}
            onChange={action('text area change')}
        />
    ))
    .add('with value and maxLength', () => (
        <TextArea
            id="text-area"
            name="text-area"
            maxLength={10}
            onChange={action('text area change')}
            value="Lol"
        />
    ))
    .add('disabled', () => (
        <TextArea
            disabled={true}
            id="text-area"
            name="text-area"
            maxLength={10}
            onChange={action('text area change')}
        />
    ))
    .add('controlled', () => (
        <ControlledTextArea id="text-area" name="text-area" maxLength={120} />
    ))
    .add('error', () => (
        <TextArea
            id="text-area"
            name="text-area"
            onChange={action('text area change')}
            error="Msg error"
        />
    ))
    .add('error and maxLength', () => (
        <TextArea
            id="text-area"
            maxLength={120}
            name="text-area"
            onChange={action('text area change')}
            error="Msg error"
        />
    ));
