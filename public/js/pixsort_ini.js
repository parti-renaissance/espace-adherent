$(function() {
    $('.sonata-ba-list').draggableTable();
});

$.fn.draggableTable = function() {
    $(this).each(function (index, item) {
        item = $(item);
        if (!item.data('DraggableTable')) {
            item.data('DraggableTable', new DraggableTable(item));
        }
    });
};

var DraggableTable = function(element) {
    var movers = element.find('.js-sortable-move');
    if (movers.length <= 1) return;

    var $document = $(document);
    var $body = $(document.body);

    var first = parseInt(movers.first().attr('data-current-position'));
    var last = parseInt(movers.last().attr('data-current-position'));
    var direction = first <= last ? 1 : -1;

    element.find('tbody').sortable({
        'handle': '.js-sortable-move',
        'start': function() {
            $body.addClass('is-dragging');
        },
        'stop': function() {
            setTimeout(function() {
                $body.removeClass('is-dragging');
            }, 100);
        },
        'axis': 'y',
        'cancel': 'input,textarea,select,option,button:not(.js-sortable-move)',
        'tolerance': 'pointer',
        'revert': 100,
        'cursor': 'move',
        'zIndex': 1,
        'helper': function(e, ui) {
            ui.css('width', '100%');
            ui.children().each(function() {
                var item = $(this);
                item.width(item.width());
            });
            return ui;
        },
        'update': function(event, ui) {
            element.find('.js-sortable-move').each(function(index, item) {
                $(item).attr('data-current-position', first + (index * direction));
            });

            var moved = $(ui.item).find('.js-sortable-move');
            var newPosition = moved.attr('data-current-position');

            $document.trigger('pixSortableBehaviorBundle.update', [event, ui]);

            $.ajax({
                'type': 'GET',
                'url': moved.attr('data-url').replace('NEW_POSITION', newPosition),
                'dataType': 'json',
                'error': function(data) {
                    $document.trigger('pixSortableBehaviorBundle.error', [data]);
                },
                'success': function(data) {
                    $document.trigger('pixSortableBehaviorBundle.success', [data]);
                },
                'complete': function() {
                    $document.trigger('pixSortableBehaviorBundle.complete');
                }
            });
        }
    }).disableSelection();
};
