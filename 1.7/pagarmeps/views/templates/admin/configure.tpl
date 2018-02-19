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

<div class="panel">
	<div class="row pagarme-header">
		<img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo-full.png" class="col-xs-6 col-md-4 text-center" style="height: auto;" id="payment-logo" />
		<div class="col-xs-6 col-md-4 text-center">
			<h4>{l s='Integre. Venda. Receba.' mod='pagarmeps'}</h4>
			<h4>{l s='Para decolar seu negócio, a melhor infraestrutura em pagamentos online.' mod='pagarmeps'}</h4>
		</div>
		<div class="col-xs-12 col-md-4 text-center">
			<a href="https://dashboard.pagar.me/#/signup?sid=1002" class="btn btn-primary" id="create-account-btn" target="blank" >{l s='Cadastro Pagar.me' mod='pagarmeps'}</a><br />
			{l s='Already registered ?' mod='pagarmeps'}<a href="https://dashboard.pagar.me" target="blank"> {l s='Log into your dashbord' mod='pagarmeps'}</a>
		</div>
	</div>

	<hr />
	
	<div class="pagarme-content">
		<div class="row">
			<div class="col-md-6">
				<p>
				O <b>Pagar.me</b> é uma nova solução para pagamentos online, que tem ajudado nossos clientes a aumentar em média  62%  seu faturamento.
				<br /><br />
				O <b>Checkout Transparente Pagar.me</b> e a maior aprovação de pedidos garantem a <b>melhor experiência de compra online</b> para seus clientes, <a href="http://blog.pagar.me/adsive-fatura-pagamento-online" target="blank" >aumentando suas vendas</a>.
				</p>

			</div>
			
			<div class="col-md-6">
				<p>
				 O Pagar.me também oferece uma gestão completa das transações com a conciliação dinâmica e antifraude já integrado.
				 <br /><br />
				 Uma combinação perfeita para os empreendedores digitais venderem mais!
				</p>
			</div>
			<h4 style="    text-align: center;    float: left;    width: 100%;    padding: 20px;">
			<a target="blank" href="http://preview.hs-sites.com/_hcms/preview/content/3263862058?portalId=462824&_preview=true&preview_key=t17rMUX3&__hstc=20629287.c6766c7949209fde2cf9a9cbfdca1c68.1424958996573.1439404179417.1439473276712.437&__hssc=20629287.15.1439473276712&__hsfp=3975761328" >
				Clique aqui para saber mais!
				</a>
			</h4>
		</div>

		<hr />
		
		<div class="row">
			<div class="col-md-12">
				<h4>{l s='Accept payments in Brazil using all major credit cards' mod='pagarmeps'}</h4>
				
				<div class="row">
					<img src="{$module_dir|escape:'html':'UTF-8'}views/img/cards.png" class="col-md-6" id="payment-logo" />
					<div class="col-md-6">
						<h6 class="text-branded">{l s='For transactions in Brazilian Real (BRL) only' mod='pagarmeps'}</h6>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
