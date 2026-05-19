// =============================================
//  GALLERY PAGE — JavaScript
//  File: js/gallery.js
//  Handles: category filter buttons
// =============================================

document.addEventListener('DOMContentLoaded', function () {
    initGalleryFilter();
});

function initGalleryFilter() {
    var filterBtns   = document.querySelectorAll('.filter-btn');
    var galleryItems = document.querySelectorAll('.g-item');
    var noResults    = document.getElementById('noResults');

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {

            // Remove active from all buttons
            filterBtns.forEach(function (b) { b.classList.remove('active'); });

            // Activate clicked button
            btn.classList.add('active');

            var filter = btn.getAttribute('data-filter');
            var visibleCount = 0;

            // Show or hide gallery items
            galleryItems.forEach(function (item) {
                var cat = item.getAttribute('data-category');
                if (filter === 'all' || cat === filter) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            // Show "no results" if none match
            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    });
}
