/* eslint-disable no-restricted-syntax */
import React, { PropTypes } from 'react';

export default class FacebookPictureChooser extends React.Component {
    constructor(props) {
        super(props);

        this.api = props.api;

        const pictures = [];
        props.urls.forEach((url) => {
            pictures.push({
                url,
                loading: true,
                data: null,
            });
        });

        this.state = {
            pictures,
            totalCount: pictures.length,
            totalReady: 0,
        };
    }

    componentDidMount() {
        this.props.urls.forEach((url) => {
            this.api.getFacebookPicture(url, (data) => {
                const pictures = this.state.pictures;
                for (const i in pictures) {
                    if (pictures[i].url === url) {
                        pictures[i].loading = false;
                        pictures[i].data = data.responseText;
                    }
                }

                const totalReady = this.state.totalReady + 1;

                this.setState({
                    pictures,
                    totalReady,
                });
            });
        });
    }

    render() {
        return (
            <div>
                <header className="l__wrapper l__wrapper--slim b__nudge--top-40 b__nudge--bottom-larger">
                    <h1 className="text--large">
                        {
                            this.state.totalReady === this.state.totalCount
                                ? 'Votre photo est prête !'
                                : 'Votre photo est en préparation ...'
                        }
                    </h1>
                </header>

                <section className="l__wrapper">
                    <div className="facebook__chooser">
                        {this.state.pictures.map((picture, key) => {
                            if (picture.loading) {
                                return (
                                    <div className="facebook__chooser__choice" key={picture.url}>
                                        <div className="facebook__chooser__image facebook__chooser__image--loading" />

                                        <div className="facebook__chooser__choice__button">
                                            <a className="btn btn--disabled" disabled={true}>
                                                Télécharger
                                            </a>
                                        </div>
                                    </div>
                                );
                            }

                            return (
                                <div className="facebook__chooser__choice" key={picture.url}>
                                    <div className="facebook__chooser__image">
                                        <img alt={`je_vote_macron_${key + 1}.jpg`}
                                             src={`data:image/jpeg;base64,${picture.data}`} />
                                    </div>

                                    <div className="facebook__chooser__choice__button">
                                        <a download={`je_vote_macron_${key + 1}.jpg`}
                                           href={`data:image/jpeg;base64,${picture.data}`}
                                           className="btn btn--blue">
                                            Télécharger
                                        </a>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </section>
            </div>
        );
    }
}

FacebookPictureChooser.propTypes = {
    api: PropTypes.object.isRequired,
    urls: PropTypes.array.isRequired,
};
