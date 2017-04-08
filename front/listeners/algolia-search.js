import algoliasearch from 'algoliasearch';

/*
 * Algolia search
 */
export default () => {
    /*
    var client = algoliasearch('{{ algolia_app_id|e('js') }}', '{{ algolia_api_key|e('js') }}');
    var articles = client.initIndex('Article_dev');
    var pages = client.initIndex('Page_dev');
    var proposals = client.initIndex('Proposal_dev');
    autocomplete('#aa-search-input', {}, [
        {
            source: autocomplete.sources.hits(articles, {hitsPerPage: 3}),
            displayKey: 'title',
            templates: {
                header: '<div class="aa-suggestions-category">Articles:</div>',
                suggestion: function (suggestion) {
                    return '<a href="/article/' + suggestion.slug + '">' +
                        suggestion._highlightResult.title.value + '</a>';
                }
            }
        },
        {
            source: autocomplete.sources.hits(pages, {hitsPerPage: 3}),
            displayKey: 'title',
            templates: {
                header: '<div class="aa-suggestions-category">Pages:</div>',
                suggestion: function (suggestion) {
                    return '<a href="#">' +
                        suggestion._highlightResult.title.value + '</a>';
                }
            }
        },
        {
            source: autocomplete.sources.hits(proposals, {hitsPerPage: 3}),
            displayKey: 'title',
            templates: {
                header: '<div class="aa-suggestions-category">Propositions:</div>',
                suggestion: function (suggestion) {
                    return '<a href="/emmanuel-macron/le-programme/' + suggestion.slug + '">' +
                        suggestion._highlightResult.title.value + '</a>';
                }
            }
        }
    ]);
    */
};
