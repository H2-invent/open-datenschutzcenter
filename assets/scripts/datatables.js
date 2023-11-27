import DataTable from "datatables.net-dt";

Array.prototype.forEach.call(document.getElementsByClassName('dataTable'), function (element) {
    new DataTable('#' + element.getAttribute('id'), {
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                exportOptions: {
                    columns: ':not(.hide-in-export)'
                }
            },
            {
                extend: 'excel',
                exportOptions: {
                    columns: ':not(.hide-in-export)'
                }
            }
        ]
    });
});

document.querySelectorAll('[data-href]').forEach(function (element) {
    const link = element.getAttribute('data-href');
    element.addEventListener('click', function() {
        window.location.href = link;
    })
});