/* eslint-disable no-restricted-syntax */
import React, { PropTypes } from 'react';
import ReqwestApiClient from '../services/api/ReqwestApiClient';

export default class FacebookPictureChooser extends React.Component {
    constructor(props) {
        super(props);

        this.api = props.api;

        const pictures = [];
        props.urls.forEach((url) => {
            pictures.push({
                dataUrl: url.data,
                uploadUrl: url.upload,
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
            this.api.getFacebookPicture(url.data, (data) => {
                const pictures = this.state.pictures;
                for (const i in pictures) {
                    if (pictures[i].dataUrl === url.data) {
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
                                    <div className="facebook__chooser__choice" key={picture.dataUrl}>
                                        <div className="facebook__chooser__image facebook__chooser__image--loading" />

                                        <div className="facebook__chooser__choice__button">
                                            <a className="btn btn--small btn--disabled" disabled={true}>
                                                Télécharger
                                            </a>
                                            <br />
                                            <a className="btn btn--small btn--disabled b__nudge--top" disabled={true}>
                                                Envoyer sur Facebook
                                            </a>
                                            <br />
                                            <div className="text--smallest text--muted b__nudge--top-5">
                                                Nous aurons besoin de votre autorisation<br />
                                                pour envoyer la photo.
                                            </div>
                                            <div className="text--smallest text--muted b__nudge--top-5">
                                                Une fois envoyée, vous pourrez la<br />
                                                définir comme photo de profil.
                                            </div>
                                        </div>
                                    </div>
                                );
                            }

                            return (
                                <div className="facebook__chooser__choice" key={picture.dataUrl}>
                                    <div className="facebook__chooser__image">
                                        <img alt={`je_vote_macron_${key + 1}.jpg`}
                                             src={`data:image/jpeg;base64,${picture.data}`} />
                                    </div>

                                    <div className="facebook__chooser__choice__button">
                                        <a download={`je_vote_macron_${key + 1}.jpg`}
                                           href={`data:image/jpeg;base64,${picture.data}`}
                                           className="btn btn--small btn--blue">
                                            Télécharger
                                        </a>
                                        <br />
                                        <a href={picture.uploadUrl}
                                           className="btn btn--small btn--facebook b__nudge--top">
                                            Envoyer sur Facebook
                                        </a>
                                        <br />
                                        <div className="text--smallest text--muted b__nudge--top-5">
                                            Nous aurons besoin de votre autorisation<br />
                                            pour envoyer la photo.
                                        </div>
                                        <div className="text--smallest text--muted b__nudge--top-5">
                                            Une fois envoyée, vous pourrez la<br />
                                            définir comme photo de profil.
                                        </div>
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
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    urls: PropTypes.array.isRequired,
};
