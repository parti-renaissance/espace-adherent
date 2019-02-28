import React from 'react';
import ReactSVG from 'react-svg';

const DefaultImage = () => <div className="turnkey__default" />;

const Title = ({ title, subtitle }) => (
    <div className="turnkey__header">
        <h2>{title}</h2>
        <h4>{subtitle}</h4>
        <hr />
    </div>
);

const TurnkeyProjectDetail = props => {
    const {
        video_id,
        title,
        subtitle,
        description,
        solution,
        border,
        is_favorite,
        renderCTA,
        video_border,
        swap
    } = props;
    return (
        <div
            className={`turnkey__project__pinned ${
                border ? `turnkey__project__${border}` : ''
            }`}>
            {swap && <Title title={title} subtitle={subtitle} />}
            <div className={`turnkey__video--${video_border}`}>
                {video_id ? (
                    <iframe
                        title="Turnkey_video"
                        width="560"
                        height="315"
                        src={`https://www.youtube.com/embed/${video_id}?rel=0&amp;controls=0&amp;showinfo=0`}
                        frameBorder="0"
                        allow="autoplay; encrypted-media"
                        allowFullScreen
                    />
                ) : (
                    <DefaultImage />
                )}
            </div>
            {!swap && <Title title={title} subtitle={subtitle} />}

            <p style={{ width: `${props.textSize}` }}>{description}</p>

            <div className="solution" style={{ width: `${props.textSize}` }} dangerouslySetInnerHTML={{__html: solution}} />

            {renderCTA && renderCTA()}

            {is_favorite && (
                <ReactSVG
                    className="coeur-icon"
                    src="/assets-citizen-project/svg/coeur.svg"
                />
            )}
        </div>
    );
};

export default TurnkeyProjectDetail;
