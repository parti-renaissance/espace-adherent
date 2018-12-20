import { configure } from '@storybook/react';
import { addDecorator } from '@storybook/react';
import { withTests } from '@storybook/addon-jest';
import StoryRouter from 'storybook-react-router';
import results from '../.jest-test-results.json';
import '../src/App.css';

// load stories dynamically
const req = require.context('../src', true, /\.stories\.js$/);
function loadStories() {
    req.keys().forEach(filename => req(filename));
}

// load tests results dynamically
addDecorator(
    withTests({
        results,
    })
);

addDecorator(StoryRouter());

configure(loadStories, module);
