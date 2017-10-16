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
            element.className = element.className.replace('btn-add-member-list', 'btn-remove-member-list');
        }).catch((err) => {
            element.innerHTML = 'Sauvegarder son profil';
            window.alert('Impossible d\'ajouter ce membre à votre liste.');
        });
    }

    function removeMemberToList(event) {
        event.preventDefault();
        const element = event.target;
        element.innerHTML = 'Suppression en cours...';

        api.deleteBoardMemberOnList(element.dataset.memberid).then((response) => {
            element.className = element.className.replace('btn-remove-member-list', 'btn-add-member-list');
            reloadIfNeeded(element);
            element.innerHTML = 'Sauvegarder son profil';
        }).catch((err) => {
            element.innerHTML = 'Profil sauvegardé';
            window.alert('Impossible de supprimer ce membre de votre liste.');
        });
    }

    document.body.addEventListener('click', (event) => {
        if (event.target.className.includes('btn-remove-member-list')) {
            removeMemberToList(event);
        }
    });

    document.body.addEventListener('click', (event) => {
        if (event.target.className.includes('btn-add-member-list')) {
            addMemberToList(event);
        }
    });
};
