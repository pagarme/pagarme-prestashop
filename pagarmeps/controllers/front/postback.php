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

class PagarmepsPostbackModuleFrontController extends ModuleFrontController
{
    private function loader() {
        include '../../lib/pagarme/PagarMe.php';
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
            Pagarmeps::addLog('Postback', 1, 'info', 'Pagarme', null);
            return header('HTTP/1.1 500 Pagarme is not active');
        }

        $api_key = Configuration::get('PAGARME_API_KEY');
        Pagarme::setApiKey($api_key);

        $request_body = file_get_contents('php://input');

        if(!Pagarme::validateRequestSignature($request_body, $_SERVER['HTTP_X_HUB_SIGNATURE'])){
            Pagarmeps::addLog('Postback: dados de postback inválidos', 1, 'info', 'Pagarme', null);
            return header('HTTP/1.1 403 Dados de postback inválidos');
        }

        $id = Tools::getValue('id');
        $current_status = Tools::getValue('current_status');
        $prestashop_new_order_status = Pagarmeps::getStatusId($current_status);
        $transaction = Tools::getValue('transaction');

        Pagarmeps::addLog('Postback: transaction id='.$id.' | status:'.$current_status, 1, 'info', 'Pagarme', null);

        $order_id = PagarmepsTransactionClass::getOrderIdByTransactionId($id);
        if( isset($transaction['metadata']['cart_id']) ) {
            $order_id = Order::getOrderByCartId($transaction['metadata']['cart_id']);
        }

        Pagarmeps::addLog('Postback: order id='.$order_id, 1, 'info', 'Pagarme', null);

        $order = new Order($order_id);

        if(is_null($order_id) || is_null($order)) {
            Pagarmeps::addLog('Postback: Order not found', 1, 'info', 'Pagarme', null);
            return header('HTTP/1.1 400 Order not found');
        }

        if(!$this->updateOrderStatus($order, $prestashop_new_order_status)) {
            Pagarmeps::addLog('Postback: Order '. $order->id .' already ' . $current_status, 1, 'info', 'Pagarme', null);
            return header('HTTP/1.1 200 Order already ' . $current_status);
        }

        if(!$order->hasInvoice() && $current_status == 'paid') {
            $order->setInvoice();
        }

        $this->addOrderHistory($order, $prestashop_new_order_status);

        Pagarmeps::addLog('Postback: Order ' . $order->id . ' successfully updated to' . $current_status);

        return header('HTTP/1.1 200 Order successfully updated');
    }

    private function updateOrderStatus($order, $prestashop_new_order_status) {

        if($order->current_state == $prestashop_new_order_status) {
            return false;
        }

        $order->current_state = $prestashop_new_order_status;

        if(!$order->save()){
            Pagarmeps::addLog('Postback: failed to update order', 1, 'info', 'Pagarme', null);

            return false;
        }

        Pagarmeps::addLog('Postback: order ' . $order->id . ' successfully updated to ' . $current_status, 1, 'info', 'Pagarme', null);

        return true;
    }

    private function addOrderHistory($order, $prestashop_new_order_status) {
        $history = new OrderHistory();
        $history->id_order = (int)$order->id;
        $history->id_order_state = $prestashop_new_order_status;

        $history->addWithemail();
        $history->changeIdOrderState($prestashop_new_order_status, (int)$order->id);
    }
}
