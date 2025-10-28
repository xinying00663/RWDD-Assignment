/**
 * Handles client-side filtering for the energy tips feed.
 */
document.addEventListener('DOMContentLoaded', function () {
    const filterSelect = document.getElementById('energyFilter');
    const grid = document.getElementById('energyGrid');

    // Exit if the required filter or grid elements are not on the page.
    if (!filterSelect || !grid) {
        return;
    }

    filterSelect.addEventListener('change', function () {
        const selectedCategory = this.value;
        const cards = grid.querySelectorAll('.media-card');

        cards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            
            // Show the card if its category matches the selection, or if "Everything" is selected.
            const shouldShow = (selectedCategory === 'all' || cardCategory === selectedCategory);
            card.style.display = shouldShow ? '' : 'none';
        });
    });
});