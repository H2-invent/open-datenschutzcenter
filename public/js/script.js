jQuery(document).ready(function () {
    setTimeout(function () {
        var x = document.getElementById("snackbar");
        x.className = "show";
        setTimeout(function () {
            x.className = x.className.replace("show", "");
        }, 3000);
    }, 2000)
});

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