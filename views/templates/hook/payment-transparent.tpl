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
				<script type="text/javascript"
					src="https://assets.pagar.me/checkout/checkout.js"
					data-button-text="{$paymentText|escape:'htmlall':'UTF-8'}"
					data-encryption-key="{$encryption_key|escape:'htmlall':'UTF-8'}"
					data-amount="{$total_order * 100|escape:'htmlall':'UTF-8'}"
					data-payment-methods="{$paymentMethods|escape:'htmlall':'UTF-8'}"
					data-postback-url="{$link->getModuleLink('pagarmeps', 'postbacktransparent', [], true)|escape:'htmlall':'UTF-8'}"
					data-customer-document-number="{$customer_document_number|escape:'htmlall':'UTF-8'}"
					data-customer-name="{$customer_name|escape:'htmlall':'UTF-8'}"
					data-customer-email="{$customer_email|escape:'htmlall':'UTF-8'}"
					data-customer-address-street="{$address_street|escape:'htmlall':'UTF-8'}"
					data-customer-address-street-number="{$address_street_number|escape:'htmlall':'UTF-8'}"
					data-customer-address-complementary="{$address_complementary|escape:'htmlall':'UTF-8'}"
					data-customer-address-neighborhood="{$address_neighborhood|escape:'htmlall':'UTF-8'}"
					data-customer-address-city="{$address_city|escape:'htmlall':'UTF-8'}"
					data-customer-address-state="{$address_state|escape:'htmlall':'UTF-8'}"
					data-customer-address-zipcode="{$address_zipcode|escape:'htmlall':'UTF-8'}"
					data-customer-phone-ddd="{$phone_ddd|escape:'htmlall':'UTF-8'}"
					data-customer-phone-number="{$phone_number|escape:'htmlall':'UTF-8'}"
					data-max-installments="{$max_installments|escape:'htmlall':'UTF-8'}"
					data-interest-rate="{$interest_rate|escape:'htmlall':'UTF-8'}"
					data-free-installments="{$free_installments|escape:'htmlall':'UTF-8'}"
					>
				</script>
			</form>
	</div>
</div>
