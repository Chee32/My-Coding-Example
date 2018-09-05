<?php
function email_details($payer, $payment, $input_data) {
    $today = Date('F j, Y');
    ob_start();
?>

<h2 style='color:#6a9c78;display:block;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:16px 0 8px;text-align:left'>
<b><?php echo $today;?></b>
</h2>

<table class="td" cellspacing="0" cellpadding="6" style="width:100%;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;color:#737373;border:none;">
	<tbody>
        <tr>
		    <td align="left" colspan="3">
			    Here are your payment results
            </td>
		</tr>
		<tr>
			<td align="left">
					Invoice: <?php echo $payment['invoice']; ?>
			</td>
			<td align="right">
					Amount: $<?php echo substr_replace($payment['amount'], '.', -2, 0); ?>
			</td>
			<td align="left">
					Result: <?php echo $payment['result']; ?>
			</td>
        </tr>
    </tbody>
</table>

<?php
$message = ob_get_clean();

return $message;

}

