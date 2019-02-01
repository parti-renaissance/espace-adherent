import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Input from '.';

class ControlledInput extends React.Component {
    constructor(props) {
        super(props);
        this.state = { value: '' };
    }

    render() {
        return <Input {...this.props} onChange={value => this.setState({ value })} value={this.state.value} />;
    }
}

storiesOf('Input', module)
    .addParameters({ jest: ['Input'] })
    .add('default', () => (
        <Input id="input" name="input" onChange={action('input change')} value="" placeholder="Entrez votre texte" />
    ))
    .add('controlled', () => <ControlledInput id="input" name="input" placeholder="Entrez votre texte" />)
    .add('with value', () => (
        <Input
            id="input"
            name="input"
            onChange={action('input change')}
            value="Super texte"
            placeholder="Entrez votre texte"
        />
    ))
    .add('with error', () => (
        <Input
            error="Message d'erreur"
            id="input"
            name="input"
            onChange={action('input change')}
            placeholder="Entrez votre texte"
        />
    ));
