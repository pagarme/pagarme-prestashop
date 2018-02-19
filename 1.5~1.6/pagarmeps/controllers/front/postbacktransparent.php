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
class PagarmepsPostbacktransparentModuleFrontController extends ModuleFrontController
{
	/**
	 * This class should be use by your Instant Payment
	 * Notification system to validate the order remotely
	 */

	private function loader($className) {
		//echo 'Trying to load ', $className, ' via ', __METHOD__, "()\n";
		if(strrpos($className, 'PagarMe_') !== false) {
			$className = Tools::substr($className, 8);
			//echo 'Trying to load V2 ', $className, ' via ', __METHOD__, "()\n";
			include dirname(__FILE__).'/../../lib/pagarme/'.$className . '.php';
		}else if(strrpos($className, 'Pagarmeps') !== false) {
			include dirname(__FILE__).'/../../classes/'.$className . '.php';
		} else {
			include dirname(__FILE__).'/../../lib/pagarme/'.$className . '.php';
		}
	}

	public function __construct($response = array()) {
		spl_autoload_register(array($this, 'loader'));
		parent::__construct($response);
		$this->display_header = false;
		$this->display_header_javascript = false;
		$this->display_footer = false;
	}

	public function postProcess()
	{
		Pagarmeps::addLog('1-PostBackTrans', 1, 'info', 'Pagarme', null);
		/**
		 * If the module is not active anymore, no need to process anything.
		 */
		if ($this->module->active == false) {
			Pagarmeps::addLog('2-PostBackTrans', 1, 'info', 'Pagarme', null);
			die('This module is not active');
		}


		if ((Tools::isSubmit('id') == false) || (Tools::isSubmit('current_status') == false)) {
			Pagarmeps::addLog('3-PostBackTrans', 1, 'info', 'Pagarme', null);
			die('No ID submited, or no Status');
		}

		$id = Tools::getValue('id');
		$current_status = Tools::getValue('current_status');
		$event = Tools::getValue('event');
		$old_status = Tools::getValue('old_status');
		$current_status_id = Pagarmeps::getStatusId($current_status);
		Pagarmeps::addLog('4-PostBackTrans id='.$id.' | old_status='.$old_status.' | current_status='.$current_status.' | event='.$event, 1, 'info', 'Pagarme', null);

		$order_id = PagarmepsTransactionClass::getOrderIdByTransactionId($id);
		Pagarmeps::addLog('5-PostBackTrans order_id='.$order_id, 1, 'info', 'Pagarme', null);

		if($order_id != null) {
			$order = new Order($order_id);
			if($order_id != null) {
				$order->current_state = $current_status_id;
				$history = new OrderHistory();
				$history->id_order = (int)$order->id;
				$history->changeIdOrderState($current_status_id, (int)$order->id);
				if($history->addWithemail()){
					if($order->save()){
						Pagarmeps::addLog('10-PostBackTrans: Everything is OK', 1, 'info', 'Pagarme', null);
						die('OK');
					} else {
						Pagarmeps::addLog('9-PostBackTrans: Error while saving Order', 1, 'info', 'Pagarme', null);
					}
				} else {
					Pagarmeps::addLog('8-PostBackTrans: Error while updating the order history', 1, 'info', 'Pagarme', null);
					die('Error while updating the order history');
				}
			} else {
				Pagarmeps::addLog('7-PostBackTrans: No order Object found for the saved ID', 1, 'info', 'Pagarme', null);
				die('No order Object found for the saved ID');
			}
		} else { //for a transparent CheckOut, this cas can happen when the callback URL is call befor the serveur processes the form POST
			Pagarmeps::addLog('6-PostBackTrans: No saved order found for the submited ID (ID='.$id.' - current_status='.$current_status.')', 1, 'info', 'Pagarme', null);
			//So we save the current state and the transaction ID for
			if($current_status != 'authorized'){
				$pgmTrans = new PagarmepsTransactionClass();
				$pgmTrans->id_order = 0;
				$pgmTrans->id_object_pagarme = pSQL($id);
				$pgmTrans->current_status = pSQL($current_status);
				if(!$pgmTrans->save()){
					Pagarmeps::addLog('7-PostBackTrans: Failed Save (id_object_pagarme='.$id.' - current_status='.$current_status.')', 1, 'info', 'Pagarme', null);
				}
			}
		}


	}
}