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

{capture name=path}{l s='Payment by Boleto' mod='pagarmeps'}{/capture}

<div id="pagarme_gateway" class="row">
	<h3>{l s='Payment by Boleto' mod='pagarmeps'}:</h3>
	
	<p>
		{l s='The valor total of your order is:' mod='pagarmeps' }
		<span class="amount {$currency->id|escape:'htmlall':'UTF-8'}">{convertPrice price=$total_order}</span>
		{if isset($boleto_discount_percentage) && $boleto_discount_percentage > 0}
			{l s='(%s%% Boleto discount)' sprintf=$boleto_discount_percentage mod='pagarmeps' }
		{/if}
	</p>
	
	<form id="pagarme_payment_form" action="{$link->getModuleLink('pagarmeps', 'confirmation', ['cart_id' => $cart_id, 'secure_key' => $secure_key], true)|escape:'htmlall':'UTF-8'}" method="POST">
		
		<div class="top-choice">
			<img src="{$modules_dir|escape:'htmlall':'UTF-8'}pagarmeps/views/img/boleto.png" alt="{l s='Boleto' mod='pagarmeps' }" />
			<input type="hidden" name="payment_way" value="boleto" />
		</div>
		
		<div id="field_errors"></div>
		
		<div class="boleto">
			<p>
				{l s='On the next step, you will see the boleto' mod='pagarmeps' } <br />
				{l s='You can pay it in a bank agency or by your bank website' mod='pagarmeps' }
			</p>
		</div>
	</form>
	
	<p class="cart_navigation clearfix">
		<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}" title="{l s='Other payment form' mod='pagarmeps' }" class="button-exclusive btn btn-default">
			<i class="icon-chevron-left"></i>
			{l s='Other payment form' mod='pagarmeps' }
		</a>
		{if $integration_mode == 'gateway'}
			<a onclick="$(this).attr('disabled','disabled');javascript:document.forms.namedItem('pagarme_payment_form').submit();" id="pagarme_submit_button" class="button btn btn-default standard-checkout button-medium">
				<span>{l s='Confirm' mod='pagarmeps'}<i class="icon-chevron-right right"></i></span>
			</a>
		{/if}
	</p>
</div>
