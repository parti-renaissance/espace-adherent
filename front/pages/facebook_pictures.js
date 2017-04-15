import React from 'react';
import { render } from 'react-dom';
import FacebookPictureChooser from '../components/FacebookPictureChooser';

/*
 * Facebook picture chooser
 */
export default (urls, api) => {
    render(<FacebookPictureChooser urls={urls} api={api} />, dom('#facebook-chooser'));
};
