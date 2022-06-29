import React from 'react';
import { render } from 'react-dom';
import RenaissanceAdhesionWidget from "../components/RenaissanceAdhesionWidget";

export default (wrapperSelector) => {
    render(
        <RenaissanceAdhesionWidget />,
        dom(wrapperSelector)
    );
};
