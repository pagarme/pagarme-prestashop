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

{capture name=path}{/capture}

    {capture name=path}
        <a href="{$link->getPageLink('my-account')|escape:'htmlall':'UTF-8'}" title="{l s='My account' mod='pagarmeps'}" >{l s='My account' mod='pagarmeps'}</a>
        <span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
		{l s='Your saved credit cards' mod='pagarmeps'}
    {/capture}

<div id="pagarme_gateway" class="row">
	<h2>{l s='Your saved credit cards' mod='pagarmeps'}:</h2>
	
	<p>
		{l s='You can keep this card to buy eseally on this site, or delete them.' mod='pagarmeps' }<br />
	</p>
	<div>
		<ul class="card-list">
		{foreach from=$cards item='card'}
			<li class="{if !$card.valid}unvalid{/if}">
				<form action="{$link->getModuleLink('pagarmeps', 'mycards', ['secure_key' => $secure_key, 'card_id' => $card.id ], true)|escape:'htmlall':'UTF-8'}" method="POST">
					<strong> {l s='Card:' mod='pagarmeps' } </strong><br />
					<span class="brand">{l s='Brand:' mod='pagarmeps' } {$card.brand|escape:'htmlall':'UTF-8'}</span><br />
					<span class="holder-name">{l s='Name:' mod='pagarmeps' } {$card.holder_name|escape:'htmlall':'UTF-8'}</span><br />
					<span class="last-digits">{l s='Last digits:' mod='pagarmeps' } {$card.last_digits|escape:'htmlall':'UTF-8'}</span><br />
					<input type="submit" value="{l s='Delete this card' mod='pagarmeps' }" />
				</form>
			</li>
		{/foreach}
		</ul>
	</div>
</div>
