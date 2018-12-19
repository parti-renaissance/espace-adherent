import React from 'react';
import MediaQuery from 'react-responsive';

const breakpoints = {
    desktop: '(min-width: 992px)',
    tablet: '(min-width: 768px) and (max-width: 991px)',
    notMobile: '(min-width: 768px)',
    mobile: '(max-width: 767px)',
    default: '(max-width: 768px)',
};

export default function Breakpoint(props) {
    const breakpoint = breakpoints[props.name] || breakpoints.default;
    return (
        <MediaQuery {...props} query={breakpoint}>
            {props.children}
        </MediaQuery>
    );
}

export const Desktop = props => (
    <Breakpoint {...props} name="desktop">
        {props.children}
    </Breakpoint>
);

export const Tablet = props => (
    <Breakpoint {...props} name="tablet">
        {props.children}
    </Breakpoint>
);

export const NotMobile = props => (
    <Breakpoint {...props} name="notMobile">
        {props.children}
    </Breakpoint>
);

export const Mobile = props => (
    <Breakpoint {...props} name="mobile">
        {props.children}
    </Breakpoint>
);
