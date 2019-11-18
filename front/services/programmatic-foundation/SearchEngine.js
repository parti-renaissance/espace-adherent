import Fuse from 'fuse.js';
import _ from 'lodash';

export default class SearchEngine {
    static search(approaches, filters) {
        let measures = _.flatMap(approaches, (approach) => {
            return _.flatMap(approach.sub_approaches, (subApproach) => {
                return _.flatMap(subApproach.measures, (measure) => {
                    measure.parentSectionIdentifierParts = [approach.position, subApproach.position];

                    return measure;
                });
            });
        });

        let projects = _.flatMap(measures, (measure) => _.flatMap(measure.projects));

        if (filters.isLeading || filters.tag) {
            measures = _.filter(measures, {
                ...(filters.isLeading ? {isLeading: true} : {}),
                ...(filters.tag ? {tags: [{label: filters.tag}]} : {})
            });
        }

        if (filters.city || filters.tag) {
            projects = _.filter(projects, {
                ...(filters.city ? {city: filters.city} : {}),
                ...(filters.tag ? {tags: [{label: filters.tag}]} : {})
            });
        }

        if (!filters.query) {
            return {
                measures: measures,
                projects: projects,
            };
        }

        const searchOptions = {
            threshold: 0.1,
            keys: [
                'title',
                'tags.label',
            ],
        };

        return {
            measures: (new Fuse(measures, searchOptions)).search(filters.query),
            projects: (new Fuse(projects, searchOptions)).search(filters.query),
        };
    }
}
