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
		if ($this->module->active == false) {
			//return $this->redirectNotFound();
		}

		if ((Tools::isSubmit('id') == false) || (Tools::isSubmit('current_status') == false)) {
			Pagarmeps::addLog('3-PostBackTrans', 1, 'info', 'Pagarme', null);
			die('No ID submited, or no Status');
		}

		$id = Tools::getValue('id');
		$currentStatus = Tools::getValue('current_status');
		$event = Tools::getValue('event');
		$old_status = Tools::getValue('old_status');
		$currentStatusId = Pagarmeps::getStatusId($currentStatus);

		$orderId = PagarmepsTransactionClass::getOrderIdByTransactionId($id);

		if (!$orderId) {
			$order = new Order($orderId);
			$order->current_state = $currentStatusId;

			$history = new OrderHistory();
			$history->id_order = (int)$order->id;

			$history->addWithemail();
			$history->changeIdOrderState($currentStatusId, (int)$order->id);

			try {
				$order->save();
			} catch (Exception $e) {
				return $this->redirectNotFound();
			}

		} else {
			return $this->postProcess();
		}

//		if($order_id != null) {
//			$order = new Order($order_id);
//			if($order_id != null) {
//				$order->current_state = $current_status_id;
//				$history = new OrderHistory();
//				$history->id_order = (int)$order->id;
//
//				if($order->save()){
//
//					$history->addWithemail();
//					$history->changeIdOrderState($current_status_id, (int)$order->id);
//
//				} else {
//					return $this->redirectNotFound();
//				}
//
//			} else {
//				return $this->redirectNotFound();
//			}
//		} else {
//			return $this->redirectNotFound();
//		}
	}

	public function redirectNotFound()
	{
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
		return false;
	}
}
