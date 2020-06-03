jQuery(document).ready(function () {
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
});

jQuery(document).ready(function () {
    $('#data-table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'csv', 'excel'

        ]
    });
});

jQuery(".clickable-row").click(function () {
    window.location = $(this).data("href");
});