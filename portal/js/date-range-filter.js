// Shared date range filter helper for report pages.
// Computes start/end dates for #dateRange dropdown options and toggles
// the .custom-date inputs, mirroring analytics-management.js behavior.

function getDateRangeBounds(range) {
    const today = new Date();
    let start = new Date();
    let end = new Date();

    switch (range) {
        case 'today':
            start.setHours(0, 0, 0, 0);
            end.setHours(23, 59, 59, 999);
            break;
        case 'yesterday':
            start.setDate(today.getDate() - 1);
            start.setHours(0, 0, 0, 0);
            end.setDate(today.getDate() - 1);
            end.setHours(23, 59, 59, 999);
            break;
        case 'this_week':
            start.setDate(today.getDate() - today.getDay());
            start.setHours(0, 0, 0, 0);
            end.setHours(23, 59, 59, 999);
            break;
        case 'last_week':
            start.setDate(today.getDate() - today.getDay() - 7);
            start.setHours(0, 0, 0, 0);
            end.setDate(today.getDate() - today.getDay() - 1);
            end.setHours(23, 59, 59, 999);
            break;
        case 'this_month':
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            end.setHours(23, 59, 59, 999);
            break;
        case 'last_month':
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
            end.setHours(23, 59, 59, 999);
            break;
        case 'this_quarter': {
            const quarter = Math.floor(today.getMonth() / 3);
            start = new Date(today.getFullYear(), quarter * 3, 1);
            end = new Date(today.getFullYear(), quarter * 3 + 3, 0);
            end.setHours(23, 59, 59, 999);
            break;
        }
        case 'last_quarter': {
            const quarter = Math.floor(today.getMonth() / 3) - 1;
            start = new Date(today.getFullYear(), quarter * 3, 1);
            end = new Date(today.getFullYear(), quarter * 3 + 3, 0);
            end.setHours(23, 59, 59, 999);
            break;
        }
        case 'this_year':
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date(today.getFullYear(), 11, 31);
            end.setHours(23, 59, 59, 999);
            break;
        case 'last_year':
            start = new Date(today.getFullYear() - 1, 0, 1);
            end = new Date(today.getFullYear() - 1, 11, 31);
            end.setHours(23, 59, 59, 999);
            break;
    }

    return {
        start: formatDateLocal(start),
        end: formatDateLocal(end)
    };
}

function formatDateLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Wires up a #dateRange select within the given form: shows/hides the
// .custom-date inputs and fills start_date/end_date before submit when
// a preset (non-custom) range is chosen.
function initDateRangeFilter(formSelector) {
    const $form = $(formSelector);
    const $dateRange = $form.find('#dateRange');
    const $customDate = $form.find('.custom-date');

    if ($dateRange.length === 0) {
        return;
    }

    function toggleCustomDate() {
        if ($dateRange.val() === 'custom') {
            $customDate.show();
        } else {
            $customDate.hide();
        }
    }

    $dateRange.on('change', toggleCustomDate);
    toggleCustomDate();

    $form.on('submit', function() {
        if ($dateRange.val() !== 'custom') {
            const range = getDateRangeBounds($dateRange.val());
            $form.find('#startDate').val(range.start);
            $form.find('#endDate').val(range.end);
        }
    });
}
