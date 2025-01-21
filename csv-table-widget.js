(function($) {
    $(document).ready(function() {
        $('#csv-filter').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#csv-table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
})(jQuery);
