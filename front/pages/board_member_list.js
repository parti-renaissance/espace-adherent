/*
 * Board Member list
 */
export default (api) => {
    function reloadIfNeeded(element) {
        if (element.className.includes('need-to-refresh')) {
            window.location.reload();
        }
    }

    function addMemberToList(event) {
        event.preventDefault();
        const element = event.target;
        element.innerHTML = 'Sauvegarde en cours...';

        api.addBoardMemberToList(element.dataset.memberid).then(() => {
            element.innerHTML = 'Profil sauvegardé';
            reloadIfNeeded(element);
            element.className = element.className.replace('btn-add-member-list',
            'btn-remove-member-list newbtn--green');
        }).catch((err) => {
            element.innerHTML = 'Sauvegarder ce profil';
            /* eslint-disable no-alert */
            window.alert('Nous n\'arrivons pas à ajouter cette personne à votre liste.\n' +
                         'Actualisez votre page et réessayez !');
            /* eslint-enable no-alert */
        });
    }

    function removeMemberToList(event) {
        event.preventDefault();
        const element = event.target;
        element.innerHTML = 'Suppression en cours...';

        api.deleteBoardMemberOnList(element.dataset.memberid).then((response) => {
            element.className = element.className.replace('btn-remove-member-list' +
            'newbtn--green', 'btn-add-member-list');
            reloadIfNeeded(element);
            element.innerHTML = 'Sauvegarder ce profil';
        }).catch((err) => {
            element.innerHTML = 'Profil sauvegardé';
            /* eslint-disable no-alert */
            window.alert('Nous n\'arrivons pas à supprimer cette personne de votre liste.\n' +
                         'Actualisez votre page et réessayez !');
            /* eslint-enable no-alert */
        });
    }

    document.body.addEventListener('click', (event) => {
        if (event.target.className.includes('newbtn--green')) {
            removeMemberToList(event);
        }
    });

    document.body.addEventListener('click', (event) => {
        if (event.target.className.includes('btn-add-member-list')) {
            addMemberToList(event);
        }
    });
};
