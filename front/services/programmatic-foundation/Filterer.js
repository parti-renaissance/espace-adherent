export default class Filterer {
    static filterApproachesByIsLeading(isLeading, unfilteredApproaches) {
        if (false === isLeading) {
            return unfilteredApproaches;
        }

        const approaches = JSON.parse(JSON.stringify(unfilteredApproaches));

        return approaches.map((a) => {
            a.sub_approaches = a.sub_approaches.map((s) => {
                s.measures = s.measures.filter(m => m.isLeading);
                return s;
            });

            return a;
        });
    }

    static filterSearchResultsByIsLeading(isLeading, unfilteredResults) {
        if (false === isLeading) {
            return unfilteredResults;
        }

        const results = JSON.parse(JSON.stringify(unfilteredResults));
        results.measures = results.measures.filter(m => m.isLeading);

        return results;
    }
}
