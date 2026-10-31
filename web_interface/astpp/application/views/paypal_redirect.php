<script type="text/javascript">
window.addEventListener('DOMContentLoaded', function(){
         document.getElementById("payment_form").submit();
     });
</script>
<?php
// All values come from the pending payment built on the server (libraries/astpp/paypal.php,
// form_data()); item_number is its token, the IPN to notify_url is checked against it.
// No session id (PHPSESSID) goes to PayPal any more.
$h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
?>
<div class ="loader_page_bg">
<div class="container">
	<div class="content" ><br/><br/><br/>
		<h2 class="text-center"><i class="fa fa-cog fa-spin" style="font-size:72px;color:#1ed7d1"></i></h2><br/>
		<h3 class="text-center" style ="color:#fff">Please wait, we are redirecting you to the payment gateway...</h3>
		<form action="<?php echo $h($paypal_url); ?>" method="post" id="payment_form">
			<input type="hidden" readonly name="amount" id="amount" value="<?php echo $h($total_amt); ?>">
			<input type="hidden" readonly name="cmd" value="_xclick">
			<input type="hidden" readonly name="business" value="<?php echo $h($paypal_email_id); ?>">
			<input type="hidden" readonly name="item_name" value="<?php echo $h($item_name); ?>">
			<input type="hidden" readonly name="item_number" value="<?php echo $h($order_id); ?>">
			<input type="hidden" readonly name="LC" value="US">
			<input type="hidden" readonly name="quantity" value="1">
			<input type="hidden" readonly name="rm" value="1">
			<input type="hidden" readonly name="no_shipping" value="1">
			<input type="hidden" readonly name="currency_code" value="<?php echo $h($currency_code); ?>">
			<input type="hidden" readonly name="notify_url" value="<?php echo $h($notify_url); ?>">
			<input type="hidden" readonly name="return" value="<?php echo $h($return); ?>">
			<input type="hidden" readonly name="cancel_return" value="<?php echo $h($cancel_return); ?>">
			<input type="hidden" readonly name="custom" id="custom" value="<?php echo $h($account_id); ?>">
		</form>
	</div>
</div>
</div>
