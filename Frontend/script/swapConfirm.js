(function () {
    document.addEventListener(''DOMContentLoaded'', function () {
        var params = new URLSearchParams(window.location.search);
        var itemTitle = params.get(''title'') || ''Selected swap item'';
        var itemDescription = params.get(''description'') || ''Choose a listing from the swap feed to see the details here.'';
        var itemCategory = params.get(''category'') || ''Swap item'';
        var itemImage = params.get(''image'') || ''Pictures/landingPage/swap-item-pic.jpg'';

        var titleElement = document.getElementById(''selectedItemTitle'');
        var nameElement = document.getElementById(''selectedItemName'');
        var descriptionElement = document.getElementById(''selectedItemDescription'');
        var tagElement = document.getElementById(''selectedItemTag'');
        var inlineNameElement = document.getElementById(''selectedItemNameInline'');
        var imageElement = document.getElementById(''selectedItemImage'');

        if (titleElement) {
            titleElement.textContent = itemTitle;
        }
        if (nameElement) {
            nameElement.textContent = itemTitle;
        }
        if (descriptionElement) {
            descriptionElement.textContent = itemDescription;
        }
        if (tagElement) {
            tagElement.textContent = itemCategory;
        }
        if (inlineNameElement) {
            inlineNameElement.textContent = itemTitle;
        }
        if (imageElement) {
            imageElement.src = itemImage;
            imageElement.alt = 'Photo of ' + itemTitle;
        }

        document.title = 'Confirm Swap - ' + itemTitle;

        var offerForm = document.getElementById(''swapOfferForm'');
        if (!offerForm) {
            return;
        }

        var successBanner = document.getElementById(''swapConfirmSuccess'');
        var submitButton = offerForm.querySelector(''button[type="submit"]'');

        offerForm.addEventListener(''submit'', function (event) {
            event.preventDefault();
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = ''Request sent'';
            }
            if (successBanner) {
                successBanner.classList.add(''swap-confirm__success--visible'');
                successBanner.focus?.();
            }
        });
    });
})();
