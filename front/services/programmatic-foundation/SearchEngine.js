import { flatMap, filter, uniq} from 'lodash';

export default class SearchEngine {
    static search(approaches, filters) {
        let measures = flatMap(approaches, approach => flatMap(approach.sub_approaches, subApproach => flatMap(subApproach.measures)));

        let projects = flatMap(measures, measure => flatMap(measure.projects));

        if (filters.isLeading) {
            measures = filter(measures, {
                ...(filters.isLeading ? { isLeading: true } : {}),
            });
        }

        if (filters.city) {
            projects = filter(projects, {
                ...(filters.city ? { city: filters.city } : {}),
            });
        }

        if (!filters.query) {
            return {
                measures: !filters.city ? measures : [],
                projects,
            };
        }

        const filterCallback = item => -1 !== this.normalize(item.title).indexOf(this.normalize(filters.query))
                || -1 !== this.normalize(uniq(flatMap(item.tags, tag => tag.label)).join()).indexOf(this.normalize(filters.query));

        return {
            measures: filter(measures, filterCallback),
            projects: filter(projects, filterCallback),
        };
    }

    static normalize(string) {
        return string.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
}
