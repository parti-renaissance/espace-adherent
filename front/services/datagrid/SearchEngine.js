export default class SearchEngine
{
    constructor(slugifier) {
        this.slugifier = slugifier;
    }

    search(columns, items, orderBy, term) {
        let keywords = this.slugifier.extractKeywords(term);

        if (keywords.length === 0) {
            return [];
        }

        let results = [];

        for (let i in items) {
            let item = items[i];
            item.score = 0;

            for (let j in columns) {
                const columnKey = columns[j].key;
                const resultColumnKeywords = this.slugifier.extractKeywords(item[columnKey]+'');

                // Priority to the first keyword
                if (startsWith(resultColumnKeywords[0], keywords[0])) {
                    item.score += 5;
                }

                // Then priority to any keyword
                for (let k in keywords) {
                    for (let l in resultColumnKeywords) {
                        if (l != 0) {
                            if (startsWith(resultColumnKeywords[l], keywords[k])) {
                                item.score += 2;
                            }
                        }
                    }
                }
            }

            // Keeping only matching results
            if (item.score > 0) {
                results.push(item);
            }
        }

        // Sort by score and then by the orderBy parameter
        results.sort((item1, item2) => {
            if (item1.score > item2.score) {
                return -1;
            } else if (item1.score < item2.score) {
                return 1;
            }

            if (item1[orderBy] > item2[orderBy]) {
                return -1;
            }

            return 0;
        });

        return results;
    }
}
