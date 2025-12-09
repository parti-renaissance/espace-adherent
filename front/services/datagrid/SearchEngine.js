export default class SearchEngine {
    constructor(slugifier) {
        this.slugifier = slugifier;
    }

    search(columns, items, orderBy, term) {
        const keywords = this.slugifier.extractKeywords(term);

        if (0 === keywords.length) {
            return [];
        }

        const results = [];

        for (const i in items) {
            const item = items[i];
            item.score = 0;

            for (const j in columns) {
                const columnKey = columns[j].key;
                const resultColumnKeywords = this.slugifier.extractKeywords(`${item[columnKey]}`);

                // Priority to the first keyword
                if (startsWith(resultColumnKeywords[0], keywords[0])) {
                    item.score += 5;
                }

                // Then priority to any keyword
                for (const k in keywords) {
                    if (0 != k) {
                        for (const l in resultColumnKeywords) {
                            if (startsWith(resultColumnKeywords[l], keywords[k])) {
                                item.score += 2;
                            }
                        }
                    }
                }
            }

            // Keeping only matching results
            if (0 < item.score) {
                results.push(item);
            }
        }

        // Sort by score and then by the orderBy parameter
        results.sort((item1, item2) => {
            if (item1.score > item2.score) {
                return -1;
            }
            if (item1.score < item2.score) {
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
