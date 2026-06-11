// Shared client-side pagination helper.
// Usage:
//   const pager = createPagination({
//       getItems: () => $('#myTable tbody tr'),
//       getFilteredItems: () => ...,        // optional, defaults to getItems()
//       paginationContainer: '#myPagination',
//       rowsPerPage: 10
//   });
//   pager.refresh();     // re-apply pagination (call after data/filters change)
//   pager.resetPage();   // jump back to page 1 (call before refresh on filter/search change)
function createPagination(options) {
    const {
        getItems,
        getFilteredItems,
        paginationContainer,
        rowsPerPage = 10
    } = options;

    let currentPage = 1;

    function refresh() {
        const $all = getItems();
        const $filtered = getFilteredItems ? getFilteredItems() : $all;

        $all.hide();

        const totalPages = Math.max(1, Math.ceil($filtered.length / rowsPerPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * rowsPerPage;
        $filtered.slice(start, start + rowsPerPage).show();

        renderPagination(totalPages, $filtered.length);
    }

    function renderPagination(totalPages, totalItems) {
        const $pagination = $(paginationContainer);
        $pagination.empty();

        if (totalItems === 0 || totalPages <= 1) {
            return;
        }

        $pagination.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
        `);

        for (let i = 1; i <= totalPages; i++) {
            $pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        $pagination.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `);
    }

    $(document).on('click', `${paginationContainer} .page-link`, function(e) {
        e.preventDefault();
        const $item = $(this).closest('.page-item');
        if ($item.hasClass('disabled') || $item.hasClass('active')) {
            return;
        }
        const page = parseInt($(this).data('page'));
        if (isNaN(page) || page < 1) return;
        currentPage = page;
        refresh();
    });

    return {
        refresh,
        resetPage: function() { currentPage = 1; },
        getCurrentPage: function() { return currentPage; }
    };
}
