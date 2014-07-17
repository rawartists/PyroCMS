$(document).ready(function() {
    /**
     * Datepicker
     */
    $('[data-toggle^="datepicker"]').each(function () {
        $(this).datepicker({
            autoclose: true,
            todayHighlight: true
        });

        $(this).datepicker('update', $(this).data('date'));
    });
});
