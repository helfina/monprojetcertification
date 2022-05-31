$(document).ready( function () {
    $('#table').DataTable({
        order: [[2, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.16/i18n/French.json',
            buttons: {
                copyTitle: 'Ajouté au presse-papiers',
                copySuccess: {
                    _: '%d lignes copiées',
                    1: '1 ligne copiée'
                }
            }
        },
        dom: 'lBtip',
        responsive: true,
        buttons: ['excel', 'pdf', 'copy'],
        select: true,
        columnDefs: [
            {
                target: 0,
                visible: false,
                searchable: false,
            },
            {
                targets: 2,
                render: DataTable.render.datetime(),
            },
            {
                targets: 3,
                render: DataTable.render.datetime(),
            },
        ],
    });
} );