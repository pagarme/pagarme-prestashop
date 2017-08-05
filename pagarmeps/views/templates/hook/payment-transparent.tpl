{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Pagar.me
*  @copyright 2015 Pagar.me
*  @version   1.0.0
*  @link      https://pagar.me/
*  @license
*}

<div class="row">
	<div class="col-xs-12 col-md-12">
		{assign var='paymentMethods' value='credit_card,boleto' }
		{assign var='paymentText' value='Pagar com Cartão ou Boleto' }
		{if $pay_way == 'boleto'}
			{assign var='paymentMethods' value='boleto' }
			{assign var='paymentText' value='Pagar com Boleto' }
		{elseif $pay_way == 'credit_card'}
			{assign var='paymentMethods' value='credit_card' }
			{assign var='paymentText' value='Pagar com Cartão' }
		{/if}

		<form class="payment_module {$pay_way|escape:'htmlall':'UTF-8'}" id="pagarme_payment_transparent_auto" method="POST" action="{$link->getModuleLink('pagarmeps', 'confirmation', ['cart_id' => $cart_id, 'secure_key' => $secure_key], true)|escape:'htmlall':'UTF-8'}">
			<span class="waiting-view" >{l s='Pay with ...' mod='pagarmeps' }</span>
			<input class="pagarme-checkout-btn" type="button" value="{$paymentText|escape:'htmlall':'UTF-8'}">
			<input type="hidden" name="token">
			<input type="hidden" name="payment_method">
		</form>

		<script>
			var buttonText = "{$paymentText|escape:'htmlall':'UTF-8'}"
			var encryptionKey = "{$encryption_key|escape:'htmlall':'UTF-8'}"
			var amount = "{$total_order * 100|escape:'htmlall':'UTF-8'}"
			var paymentMethods = "{$paymentMethods|escape:'htmlall':'UTF-8'}"
			var postbackUrl = "{$link->getModuleLink('pagarmeps', 'postbacktransparent', [],true)|escape:'htmlall':'UTF-8'}"
			var customerData = "{$confirm_customer_data|escape:'htmlall':'UTF-8'}"
			var customerDocumentNumber = "{$customer_document_number|escape:'htmlall':'UTF-8'}"
			var customerName = "{$customer_name|escape:'htmlall':'UTF-8'}"
			var customerEmail = "{$customer_email|escape:'htmlall':'UTF-8'}"
			var customerAddressStreet = "{$address_street|escape:'htmlall':'UTF-8'}"
			var customerAddressStreetNumber = "{$address_street_number|escape:'htmlall':'UTF-8'}"
			var customerAddressComplementary = "{$address_complementary|escape:'htmlall':'UTF-8'}"
			var customerAddressNeighborhood = "{$address_neighborhood|escape:'htmlall':'UTF-8'}"
			var customerAddressCity = "{$address_city|escape:'htmlall':'UTF-8'}"
			var customerAddressState = "{$address_state|escape:'htmlall':'UTF-8'}"
			var customerAddressZipcode = "{$address_zipcode|escape:'htmlall':'UTF-8'}"
			var customerPhoneDdd = "{$phone_ddd|escape:'htmlall':'UTF-8'}"
			var customerPhoneNumber = "{$phone_number|escape:'htmlall':'UTF-8'}"
			var maxInstallments = "{$max_installments|escape:'htmlall':'UTF-8'}"
			var interestRate = "{$interest_rate|escape:'htmlall':'UTF-8'}"
			var freeInstallments = "{$free_installments|escape:'htmlall':'UTF-8'}"
			var boletoDiscountAmount = "{$boleto_discount_amount|escape:'htmlall':'UTF-8'}"
			{literal}
				$(document).ready(function() {
					var button = $('.pagarme-checkout-btn');

					button.click(function(event) {
						event.preventDefault()
						var checkout = new PagarMeCheckout.Checkout({"encryption_key": encryptionKey, success: function(data) {
							$("input[name='token']").val(data.token)
							$("input[name='payment_method']").val(data.payment_method)
							$("form#pagarme_payment_transparent_auto").submit()
						}});

						var params = {
							"buttonText": buttonText,
							"amount": amount,
							"paymentMethods": paymentMethods,
							"postbackUrl": postbackUrl,
							"customerData": customerData,
							"customerDocumentNumber": customerDocumentNumber,
							"customerName": customerName,
							"customerEmail": customerEmail,
							"customerAddressStreet": customerAddressStreet,
							"customerAddressStreetNumber": customerAddressStreetNumber,
							"customerAddressComplementary": customerAddressComplementary,
							"customerAddressNeighborhood": customerAddressNeighborhood,
							"customerAddressCity": customerAddressCity,
							"customerAddressState": customerAddressState,
							"customerAddressZipcode": customerAddressZipcode,
							"customerPhoneDdd": customerPhoneDdd,
							"customerPhoneNumber": customerPhoneNumber,
							"maxInstallments": maxInstallments,
							"interestRate": interestRate,
							"freeInstallments": freeInstallments,
							"boletoDiscountAmount": boletoDiscountAmount
						};
						checkout.open(params);
					});
				});
			{/literal}
		</script>           
	</div>
</div>
