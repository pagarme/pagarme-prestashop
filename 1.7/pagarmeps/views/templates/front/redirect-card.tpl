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

{capture name=path}{l s='Payment by Credit Card' mod='pagarmeps'}{/capture}

<div id="pagarme_gateway" class="row">
	<h3>{l s='Payment by Credit Card' mod='pagarmeps'}:</h3>
	
	<p>
		{l s='The valor total of your order is:' mod='pagarmeps' }
		<span class="amount {$currency->id|escape:'htmlall':'UTF-8'}">{convertPrice price=$total_order}</span>
	</p>
	<form id="pagarme_payment_form" action="{$link->getModuleLink('pagarmeps', 'confirmation', ['cart_id' => $cart_id, 'secure_key' => $secure_key], true)|escape:'htmlall':'UTF-8'}" method="POST">
		
		<div class="top-choice">
			<img src="{$modules_dir|escape:'htmlall':'UTF-8'}pagarmeps/views/img/cards.png" alt="{l s='Credit Card' mod='pagarmeps' }" />
			<input type="hidden" name="payment_way" value="card" />
		</div>
		
		<div id="field_errors"></div>
		
		<div class="card">
			<div class="form-item">
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<label  for="card_number" >{l s='Credit card number:' mod='pagarmeps'}</label> 
				</div>
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<input size="32" type="text" id="card_number" name="card_number"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="form-item">
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<label for="card_holder_name" >{l s='Name (Like on the credit card)' mod='pagarmeps'}</label>
				</div>
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<input size="32" type="text" id="card_holder_name" name="card_holder_name"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="form-item">
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<label for="card_expiration_month" >{l s='Month / Year of experation' mod='pagarmeps'}</label>
				</div>
				{if $show_combo == true}
					{include file="./combo-expiration.tpl"}
				{else}
					{include file="./input-expiration.tpl"}
				{/if}
				<div class="clear"></div>
			</div>
			<div class="form-item">
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<label for="card_cvv" >{l s='Safety code:' mod='pagarmeps'}</label>
				</div>
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<input size="4" type="text" id="card_cvv" name="card_cvv"/>
				</div>
				<div class="clear"></div>
			</div>
			{if count($installment) > 0}
			<div class="form-item">
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<label for="installment" >{l s='Installment:' mod='pagarmeps'}</label>
				</div>
				<div class="column col-xs-12 col-sm-6 col-md-4">
					<select id="installment" name="installment">
						{foreach from=$installment item='installment_opt'}
							<option value="{$installment_opt['installment']|escape:'htmlall':'UTF-8'}" >{$installment_opt['installment']|escape:'htmlall':'UTF-8'}x R$ {$installment_opt['installment_amount']/100|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					<select>
					<span class="installment-tax-infos">
						{if $installment_tax_free == 'all'}
							{l s='(Without tax)' mod='pagarmeps'}
						{else if $installment_tax_free != 'none'}
							{l s='(Until %s times without tax)' sprintf=$installment_tax_free mod='pagarmeps'}
						{/if}
					</span>
				</div>
				<div class="clear"></div>
			</div>
			{/if}
		</div>
	</form>
	<!--
	<p class="cart_navigation clearfix">
		<a href="{$link->getPageLink('order', true, NULL, 'step=3')|escape:'htmlall':'UTF-8'}" title="{l s='Other payment form' mod='pagarmeps' }" class="button-exclusive btn btn-default">
			<i class="icon-chevron-left"></i>
			{l s='Other payment form' mod='pagarmeps' }
		</a>
		{if $integration_mode == 'gateway'}
			<a href="#pagarme_payment_form" onclick="validateForm();" class="button btn btn-default standard-checkout button-medium">
				<span>{l s='Confirm' mod='pagarmeps'}<i class="icon-chevron-right right"></i></span>
			</a>
		{/if}
	</p>
-->
</div>

<script>
	var encryption_key = "{$encryption_key|escape:'htmlall':'UTF-8'}";
	var pay_way = "{$pay_way|escape:'htmlall':'UTF-8'}";
</script>
