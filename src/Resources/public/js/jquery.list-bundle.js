(function($)
{
    LIST_BUNDLE = {
        init: function()
        {
            this.initPagination();
        },
        initPagination: function()
        {
            $('.huh-list .ajax-pagination').each(function()
            {
                var $list = $(this).closest('.huh-list'),
                    $items = $list.find('.items'),
                    $wrapper = $list.find('.wrapper'),
                    id = '#' + $wrapper.attr('id');

                $wrapper.jscroll({
                    loadingHtml: '<div class="loading"><span class="text">Lade...</span></div>',
                    nextSelector: '.ajax-pagination a.next',
                    autoTrigger: $wrapper.data('add-infinite-scroll') == 1,
                    contentSelector: id,
                    callback: function()
                    {
                        var $jscrollAdded = $(this),
                            $newItems = $jscrollAdded.find('.item');

                        $newItems.hide();

                        $jscrollAdded.imagesLoaded(function()
                        {
                            $items.append($newItems.fadeIn(300));

                            // remove item counters...
                            $items.find('.item').removeClass(function(index, cssClass)
                            {
                                var matches = cssClass.match(/item_\d+/g);

                                if (matches.length > 0)
                                {
                                    return matches[0];
                                }
                            });

                            //... and readd them again
                            $items.find('.item').each(function(index)
                            {
                                var $item = $(this),
                                    itemIndex = index + 1;

                                $(this).addClass('item_' + itemIndex).removeClass('odd even first last');

                                // odd/even
                                if (itemIndex % 2 == 0)
                                {
                                    $item.addClass('even');
                                }
                                else
                                {
                                    $item.addClass('odd');
                                }

                                // add first and last
                                if (itemIndex == 1)
                                {
                                    $item.addClass('first');
                                }

                                if (itemIndex == $items.find('.item').length)
                                {
                                    $item.addClass('last');
                                }
                            });

                            $jscrollAdded.find('.ajax-pagination').appendTo($jscrollAdded.closest('.jscroll-inner'));
                            $jscrollAdded.remove();
                        });
                    }
                });
            });
        }
    };

    $(document).ready(function()
    {
        LIST_BUNDLE.init();
    });
})(jQuery);