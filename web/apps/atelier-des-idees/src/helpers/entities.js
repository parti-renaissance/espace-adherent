export function sortEntitiesByDate(entities = []) {
    return entities.sort((a, b) => {
        if (a.created_at < b.created_at) {
            return 1;
        }
        if (a.created_at > b.created_at) {
            return -1;
        }
        return 0;
    });
}
