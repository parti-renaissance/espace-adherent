export function sortEntitiesByDate(entities = [], direction = 'DESC') {
    const sortOffset = 'DESC' === direction ? -1 : 1;
    return entities.sort((a, b) => {
        if (a.created_at < b.created_at) {
            return 1 * sortOffset;
        }
        if (a.created_at > b.created_at) {
            return -1 * sortOffset;
        }
        return 0;
    });
}
