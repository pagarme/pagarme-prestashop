<?php
require_once('pagarmeOrder.php');

class PagarmepsPostbacktransparentModuleFrontController extends PagarmepsOrderModuleFrontController
{
    public function postProcess()
    {
        if ($this->module->active == false) {
            Pagarmeps::addLog('Pagar.me module is not active', 1, 'info', 'Pagarme', null);
            return header('HTTP/1.1 500 Pagarme is not active');
        }

        $api_key = Configuration::get('PAGARME_API_KEY');
        Pagarme::setApiKey($api_key);

        $request_body = file_get_contents('php://input');

        if(!Pagarme::validateRequestSignature($request_body, $_SERVER['HTTP_X_HUB_SIGNATURE'])) {
            Pagarmeps::addLog('Postback: dados de postback inválidos', 1, 'info', 'Pagarme', null);
            return header('HTTP/1.1 403 Dados de postback inválidos');
        }

        $id = Tools::getValue('id');
        $current_status = Tools::getValue('current_status');
        $transaction = Tools::getValue('transaction');

        if($current_status == 'authorized') {
            return header('HTTP/1.1 200 Order already ' . $current_status);
        }

        $order_id = PagarmepsTransactionClass::getOrderIdByTransactionId($id);
        if( isset($transaction['metadata']['order_id']) ) {
            $order_id = $transaction['metadata']['order_id'];
        }

        $order = new Order($order_id);

        if(is_null($order_id) || is_null($order)) {
            Pagarmeps::addLog('Postback: Order not found', 1, 'info', 'Pagarme', $order_id);
            return header('HTTP/1.1 400 Order not found');
        }

        if(!$this->updateOrderStatus($order, $transaction)) {
            Pagarmeps::addLog('Postback: Cannot update order status', 1, 'info', 'Pagarme', $order->id);
            return header('HTTP/1.1 200 Order update failed');
        }

        Pagarmeps::addLog('Postback: Order ' . $order->id . ' successfully updated to ' . $current_status, 1, 'info', 'Pagarme', $order->id);

        return header('HTTP/1.1 200 Order successfully updated');
    }
}
