/* 
 *
 *
 */

jQuery(function($) {

        // REPEAT INPUTS
        jQuery('#am_recurrent').click(function() {
            jQuery('#am_recurrent_fields')[this.checked ? "show" : "hide"]();
        });

        // DATETIME PICKERS
        var startDateTextBox = $('#am_startdate');
        var endDateTextBox = $('#am_enddate');

        startDateTextBox.datetimepicker({
                dateFormat: "dd.mm.yy",
                timeFormat: 'HH:mm',
                stepMinute: 15,
                onClose: function(dateText, inst) {
                        if (endDateTextBox.val() != '') {
                                var testStartDate = startDateTextBox.datetimepicker('getDate');
                                var testEndDate = endDateTextBox.datetimepicker('getDate');
                                if (testStartDate > testEndDate)
                                        endDateTextBox.datetimepicker('setDate', testStartDate);
                        }
                        else {
                                endDateTextBox.val(dateText);
                        }
                },
                onSelect: function (selectedDateTime){
                        endDateTextBox.datetimepicker('option', 'minDate', startDateTextBox.datetimepicker('getDate') );
                }
        });
        endDateTextBox.datetimepicker({ 
                dateFormat: "dd.mm.yy",
                timeFormat: 'HH:mm',
                stepMinute: 15,
                onClose: function(dateText, inst) {
                        if (startDateTextBox.val() != '') {
                                var testStartDate = startDateTextBox.datetimepicker('getDate');
                                var testEndDate = endDateTextBox.datetimepicker('getDate');
                                if (testStartDate > testEndDate)
                                        startDateTextBox.datetimepicker('setDate', testEndDate);
                        }
                        else {
                                startDateTextBox.val(dateText);
                        }
                },
                onSelect: function (selectedDateTime){
                        startDateTextBox.datetimepicker('option', 'maxDate', endDateTextBox.datetimepicker('getDate') );
                }
        });
        
});




