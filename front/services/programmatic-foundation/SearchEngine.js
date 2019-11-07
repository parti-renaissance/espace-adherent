import Fuse from 'fuse.js';

export default class SearchEngine {
    static search(query, city, initialApproaches) {
        const approaches = JSON.parse(JSON.stringify(initialApproaches));

        const measures = approaches.map(
            a => a.sub_approaches.map(
                s => s.measures.map((m) => {
                    m.parentSectionIdentifier = `${a.position}.${s.position}.`;
                    return m;
                })
            ).flat()
        ).flat();

        const projects = measures.map(
            m => m.projects.map((p) => {
                p.parentSectionIdentifier = `${m.parentSectionIdentifier}.${m.position}`;
                return p;
            })
        ).flat();

        const searchOptions = {
            threshold: 0.3,
            keys: [
                'title',
                'tags.label',
            ],
        };

        const results = {
            measures: (new Fuse(measures, searchOptions)).search(query),
            projects: (new Fuse(projects, searchOptions)).search(query),
        };

        //TODO filter by city

        return results;
    }
}
