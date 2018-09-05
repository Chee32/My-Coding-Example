<?php
include('header.php');
include('details.php');
include('footer.php');

function create_email($payer, $payment, $input_data) {

$header = email_head($input_data);
$details = email_details($payer, $payment, $input_data);
$footer = email_footer();

ob_start();
echo $header;
echo $details;
echo $footer;
$message = ob_get_clean();

return $message;

}
?>