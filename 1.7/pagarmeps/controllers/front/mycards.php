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

class PagarmepsMycardsModuleFrontController extends ModuleFrontController
{
	/**
	 * Do whatever you have to before redirecting the customer on the website of your payment processor.
	 */
	public function postProcess()
	{
		/**
		 * Oops, an error occured.
		 */
		if (Tools::getValue('action') == 'error')
			return $this->displayError('An error occurred while trying to redirect the customer');
		else
		{
			$id_customer = Context::getContext()->customer->id;
			
			if (Tools::isSubmit('card_id') === true) {
				$card_id = Tools::getValue('action');
				$cards = PagarmepsCardClass::getClientCard($id_customer);
				
				for($i=0; $i<=count($cards); $i++) {
					if($cards[0]['id'] == $card_id) {
						PagarmepsCardClass::deleteClientCard($card_id);
					}
				}
			}
			
			$cards = PagarmepsCardClass::getClientCard($id_customer);
			$this->context->smarty->assign(array(
				'cards' => $cards,
				'secure_key' => Context::getContext()->customer->secure_key,
			));
			
			return $this->setTemplate('account-mycards.tpl');
		}
	}

	protected function displayError($message, $description = false)
	{
		/**
		 * Create the breadcrumb for your ModuleFrontController.
		 */
		$this->context->smarty->assign('path', '
			<a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Payment').'</a>
			<span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));

		/**
		 * Set error message and description for the template.
		 */
		array_push($this->errors, $this->module->l($message), $description);

		return $this->setTemplate('error.tpl');
	}
	
		/**
	* Set default medias for this controller
	*/
	public function setMedia(){
		parent::setMedia();
		//$this->context->controller->addJS(array('https://assets.pagar.me/js/pagarme.min.js',
		//$this->module->getPath().'/views/js/jquery.mask.min.js',
		//$this->module->getPath().'/views/js/gateway.js',
		//));
	}

}
