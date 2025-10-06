(function () {
    function buildDestination(link) {
        var rawHref = link.getAttribute(''href'') || ''swapConfirm.html'';
        var questionIndex = rawHref.indexOf(''?'');
        return questionIndex === -1 ? rawHref : rawHref.slice(0, questionIndex);
    }

    function redirectWithCardData(link) {
        var card = link.closest(''.swap-card'');
        if (!card) {
            window.location.href = link.getAttribute(''href'') || ''swapConfirm.html'';
            return;
        }

        var params = new URLSearchParams();
        var titleElement = card.querySelector(''h3'');
        var descriptionElement = card.querySelector(''p'');
        var imageEl = card.querySelector(''img'');
        var tag = card.querySelector(''.card-tag'');

        var title = card.dataset.itemTitle || (titleElement ? titleElement.textContent.trim() : ''Swap item'');
        var description = card.dataset.itemDescription || (descriptionElement ? descriptionElement.textContent.trim() : '');
        var category = tag ? tag.textContent.trim() : (card.dataset.category || '''');
        var image = card.dataset.itemImage || (imageEl ? imageEl.getAttribute(''src'') : '''');

        params.set(''title'', title);
        if (description) {
            params.set(''description'', description);
        }
        if (category) {
            params.set(''category'', category);
        }
        if (image) {
            params.set(''image'', image);
        }

        var destination = buildDestination(link);
        var query = params.toString();
        window.location.href = query ? destination + ''?'' + query : destination;
    }

    document.addEventListener(''DOMContentLoaded'', function () {
        var swapLinks = document.querySelectorAll(''[data-swap-trigger]'');
        if (!swapLinks.length) {
            return;
        }

        swapLinks.forEach(function (link) {
            link.addEventListener(''click'', function (event) {
                event.preventDefault();
                redirectWithCardData(link);
            });

            var card = link.closest(''.swap-card'');
            if (card && card.dataset.itemTitle) {
                link.setAttribute(''aria-label'', ''Swap for '' + card.dataset.itemTitle);
            }
        });
    });
})();
