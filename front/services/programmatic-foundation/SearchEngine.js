import Fuse from 'fuse.js';
import _ from 'lodash';

export default class SearchEngine {
    static search(approaches, filters) {
        const measures = _.flatMap(approaches, (approach) => {
            return _.flatMap(approach.sub_approaches, (subApproach) => {
                return _.flatMap(filters.isLeading ? _.filter(subApproach.measures, ['isLeading', true]) : subApproach.measures, (measure) => {
                    measure.parentSectionIdentifierParts = [
                        approach.position,
                        subApproach.position,
                    ];

                    return measure;
                });
            });
        });

        const projects = _.flatMap(measures, (measure) => {
            return _.flatMap(
                filters.city ? _.filter(measure.projects, ['city', filters.city]) : measure.projects,
                (project) => {
                    project.parentSectionIdentifierParts = measure.parentSectionIdentifierParts.concat(measure.position);

                    return project;
                }
            );
        });

        const searchOptions = {
            threshold: 0.1,
            keys: [
                'title',
                'tags.label',
            ],
        };

        if (filters.query) {
            return {
                measures: (new Fuse(measures, searchOptions)).search(filters.query),
                projects: (new Fuse(projects, searchOptions)).search(filters.query),
            };
        }

        return {
            measures: [],
            projects: projects,
        };
    }
}
