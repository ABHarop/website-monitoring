<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Page Load Detection</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Function to check if a specific element on the home page is visible
    function checkHomePageLoad() {
        $.ajax({
            url: 'https://pahappa.com/',
            type: 'GET',
            success: function(response) {
                // Create a temporary DOM element to parse the response
                var tempDiv = $('<div></div>').html(response);

                // Check for the presence of a specific element or content
                if (tempDiv.find('div.specific-element').length > 0) { // Modify selector as needed
                    alert('Home page has fully loaded.');
                } else {
                    setTimeout(checkHomePageLoad, 1000); // Check every second
                }
            },
            error: function(xhr, status, error) {
                alert('Error checking home page load status: ' + error);
            }
        });
    }

    checkHomePageLoad();
});
</script>



</head>
<body>
<p>Page Load Detection</p>
</body>
</html>
