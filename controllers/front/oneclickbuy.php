<?php
/**
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
*/

class PagarmepsOneclickbuyModuleFrontController extends ModuleFrontController
{

	public function __construct()
	{
		parent::__construct();
		$this->ajax = true;
	}
	
	public function displayAjax()
	{
		$id_customer = Context::getContext()->customer->id;
		$cards = PagarmepsCardClass::getClientCard($id_customer);
		
		$cart = Context::getContext()->cart;
		$total_order = $cart->getOrderTotal();
		$installment = Pagarmeps::getInstallmentOptions($total_order);
		$installment_tax_free = Configuration::get('PAGARME_INSTALLMENT_TAX_FREE');
		
		$this->context->smarty->assign(array(
			'cards' => $cards,
			'cart_id' => $cart->id,
			'total_order' => $total_order,
			'installment' => $installment,
			'installment_tax_free' => $installment_tax_free,
			'secure_key' => Context::getContext()->customer->secure_key,
		));
		
		//$this->layout = 'ajax-oneclick.tpl';
		//$this->display_header = false;
		//$this->display_header_javascript = false;
		//$this->display_footer = false;
		//return $this->display();
		
		$output = $this->context->smarty->fetch(_PS_MODULE_DIR_.'/pagarmeps/views/templates/front/ajax-oneclick.tpl');
		echo $output;
		//return $this->display(__FILE__, '../views/templates/front/ajax-oneclick.tpl');
		
		//return $this->setTemplate('ajax-oneclick.tpl');
	}
}
