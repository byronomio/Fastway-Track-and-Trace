jQuery(document).ready(function ($) {
    $('#fastway-submit').on('click', function (e) {
        e.preventDefault();

        var waybill = $('#fastway-waybill').val();

        // Show the spinner
        $('#fastway-spinner').css('display', 'block');
        $('#fastway-tracking-text').css('display', 'block');
        $('#fastway-result').html('');

        if (waybill) {
            $.ajax({
                type: 'POST',
                url: fastway_ajax_object.ajax_url,
                dataType: 'json',
                data: {
                    action: 'fastway_ajax',
                    waybill: waybill,
                    api_key: fastway_ajax_object.fastway_api_key,
                },
                success: function (response) {
                    if (response.result && response.result.Scans) {
                        var scans = response.result.Scans;
                        var resultHtml = '<br><table border="1" cellpadding="5" cellspacing="0" style="width: 100%; margin-top: 15px;">';
                        resultHtml += '<thead><tr><th>Status</th><th>Date</th><th>Location</th></tr></thead><tbody>';
                        
                        for (var i = 0; i < scans.length; i++) {
                            resultHtml += '<tr>';
                            resultHtml += '<td>' + scans[i].StatusDescription + '</td>';
                            resultHtml += '<td>' + scans[i].Date + '</td>';
                            resultHtml += '<td>' + scans[i].Name + '</td>';
                            resultHtml += '</tr>';
                        }
                
                        resultHtml += '</tbody></table>';
                        $('#fastway-result').html(resultHtml);
                    } else {
                        $('#fastway-result').html('<br><p>No results found.</p>');
                    }

                    // Hide the spinner
                    $('#fastway-spinner').css('display', 'none');
                    $('#fastway-tracking-text').css('display', 'none');
                },
                error: function () {
                    $('#fastway-result').html('<p>Error fetching data.</p>');
                    // Hide the spinner
                    $('#fastway-spinner').css('display', 'none');
                    $('#fastway-tracking-text').css('display', 'none');
                },
            });
        } else {
            // Hide the spinner
            $('#fastway-spinner').css('display', 'none');
            $('#fastway-tracking-text').css('display', 'none');
            $('#fastway-result').html('<p>Please enter a waybill number.</p>');
        }
    });
});