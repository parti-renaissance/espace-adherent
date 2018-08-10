import React from 'react';

const TurnkeyProjectDetail = (props) => {
    const { video_id, title, subtitle, description, cta_content, cta_border, border } = props;
    return (
        <div className={`turnkey__project__pinned turnkey__project__${border}`}>
            <div className="turnkey__video">
                <iframe
                    title="Turnkey_video"
                    width="560"
                    height="315"
                    src={`https://www.youtube.com/embed/${video_id}?rel=0&amp;controls=0&amp;showinfo=0`}
                    frameborder="0"
                    allow="autoplay; encrypted-media"
                    allowfullscreen
                />
            </div>
            <h2>{title}</h2>
            <h4>{subtitle}</h4>
            <hr />
            <p>{description}</p>
            <button className={`turnkey__cta turnkey__cta__${cta_border}`}>{cta_content}</button>
        </div>
    );
};

export default TurnkeyProjectDetail;
