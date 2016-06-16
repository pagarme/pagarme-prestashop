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

<div id="pagarme_oneclick_ajax">

	<form id="pagarme_payment_form_ajax" action="{$link->getModuleLink('pagarmeps', 'confirmation', ['cart_id' => $cart_id, 'secure_key' => $secure_key], true)|escape:'htmlall':'UTF-8'}" method="POST">
		
		<input type="hidden" name="payment_way" value="oneclickbuy" />
		
		<p>
			{l s='The valor total of your order is:' mod='pagarmeps' }
			<strong class="amount {$currency->id|escape:'htmlall':'UTF-8'}">{convertPrice price=$total_order}</strong>
		</p>
		
		<div>
			{l s='You will finalize this order using the card:' mod='pagarmeps' }
			<ul class="card-list">
			{foreach from=$cards item='card'}
				<li class="{if !$card.valid}unvalid{/if}">
					<label>
						{if count($cards) > 1}
							<input type="radio" name="choosen_card" value="{$card.id|escape:'htmlall':'UTF-8'}" />
						{else}
							<input type="hidden" name="choosen_card" value="{$card.id|escape:'htmlall':'UTF-8'}" />
						{/if}
						<span class="brand">{l s='Card:' mod='pagarmeps' } {$card.brand|escape:'htmlall':'UTF-8'}</span>
						<span class="holder-name">{l s='Name:' mod='pagarmeps' } {$card.holder_name|escape:'htmlall':'UTF-8'}</span>
						<span class="last-digits">{l s='Last digits:' mod='pagarmeps' } {$card.last_digits|escape:'htmlall':'UTF-8'}</span>
					</label>
				</li>
			{/foreach}
			</ul>
		</div>
		
		{if count($installment) > 0}
			<label for="installment" >{l s='Installment:' mod='pagarmeps'}</label>
			<select id="installment" name="installment">
				{foreach from=$installment item='installment_opt'}
					<option value="{$installment_opt['installment']|escape:'htmlall':'UTF-8'}" >{$installment_opt['installment']|escape:'htmlall':'UTF-8'}x R$ {$installment_opt['installment_amount']/100|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			<select>
			<span class="installment-tax-infos" style="padding: 0;" >
				{if $installment_tax_free == 'all'}
					{l s='(Without tax)' mod='pagarmeps'}
				{else if $installment_tax_free != 'none'}
					{l s='(Until %s times without tax)' sprintf=$installment_tax_free mod='pagarmeps'}
				{/if}
			</span>
		{/if}
		
		<input class="oneclickbuy-validate" type="submit" value="{l s='Validate Payment' mod='pagarmeps' }" />
		
	</form>
	
</div>
