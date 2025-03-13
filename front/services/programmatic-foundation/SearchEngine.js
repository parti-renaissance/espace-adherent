import _ from 'lodash';

export default class SearchEngine {
    static search(approaches, filters) {
        let measures = _.flatMap(approaches, (approach) => _.flatMap(approach.subApproaches, (subApproach) => _.flatMap(subApproach.measures)));

        let projects = _.flatMap(measures, (measure) => _.flatMap(measure.projects));

        if (filters.isLeading) {
            measures = _.filter(measures, {
                ...(filters.isLeading ? { isLeading: true } : {}),
            });
        }

        if (filters.city) {
            projects = _.filter(projects, {
                ...(filters.city ? { city: filters.city } : {}),
            });
        }

        if (!filters.query) {
            return {
                measures: !filters.city ? measures : [],
                projects,
            };
        }

        const filterCallback = (item) => -1 !== this.normalize(item.title).indexOf(this.normalize(filters.query))
                || -1 !== this.normalize(_.uniq(_.flatMap(item.tags, (tag) => tag.label)).join()).indexOf(this.normalize(filters.query));

        return {
            measures: _.filter(measures, filterCallback),
            projects: _.filter(projects, filterCallback),
        };
    }

    static normalize(string) {
        return string.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
}
