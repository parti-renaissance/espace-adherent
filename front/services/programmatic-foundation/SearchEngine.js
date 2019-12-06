import _ from 'lodash';

export default class SearchEngine {
    static search(approaches, filters) {
        let measures = _.flatMap(approaches, (approach) => {
            return _.flatMap(approach.sub_approaches, (subApproach) => _.flatMap(subApproach.measures));
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
                measures: !filters.city ? measures : [],
                projects: projects,
            };
        }

        const filterCallback = (item) => {
            return item.title.toLowerCase().indexOf(filters.query.toLowerCase()) !== -1
                || _.uniq(_.flatMap(item.tags, tag => tag.label)).join().toLowerCase().indexOf(filters.query.toLowerCase()) !== -1
        };

        return {
            measures: _.filter(measures, filterCallback),
            projects: _.filter(projects, filterCallback),
        };
    }
}
