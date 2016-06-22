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

{if (isset($status) == true) && ($status == 'ok')}
<h3>{l s='Your order on %s is complete.' sprintf=$shop_name mod='pagarmeps'}</h3>
<p>
	<br />- {l s='Amount' mod='pagarmeps'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
	<br />- {l s='Reference' mod='pagarmeps'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='An email has been sent with this information.' mod='pagarmeps'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='pagarmeps'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='pagarmeps'}</a>
</p>

{if $boleto_url}
<div id="boletoConfirm">
	<span onclick="printBoleto()" id="boletoPrint"></span>
	{l s='You have chooseen to pay by Boleto' mod='pagarmeps'}
	<br />
	{l s='Here is the barcode of the Boleto:' mod='pagarmeps'}
	<br />
	<strong>{$boleto_barcode|escape:'htmlall':'UTF-8'}</strong>

	<script>
		var boleto_url = '{$boleto_url|escape:'htmlall':'UTF-8'}';
		function printBoleto() {
			var myWindow=window.open('','','width=800,height=800');
			myWindow.document.write("<iframe src=\"{$boleto_url|escape:'htmlall':'UTF-8'}\" style=\"width: 100%; height: 900px; border: none;\" id=\"boletoIframe\" name=\"boletoIframe\" ></iframe>");

			myWindow.document.close();
			myWindow.focus();
			$(myWindow.document).ready(function(){
				setTimeout(function(){ myWindow.print(); }, 3000);

			});
		}
	</script>

	<iframe src="{$boleto_url|escape:'htmlall':'UTF-8'}" id="boletoIframe" name="boletoIframe" ></iframe>
</div>
{/if}

{else}
<h3>{l s='Your order on %s has not been accepted.' sprintf=$shop_name mod='pagarmeps'}</h3>
<p>
	<br />- {l s='Reference' mod='pagarmeps'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='Please, try to order again.' mod='pagarmeps'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='pagarmeps'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='pagarmeps'}</a>
</p>
{/if}
<hr />