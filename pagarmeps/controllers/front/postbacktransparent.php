<?php
require_once('pagarmeOrder.php');

class PagarmepsPostbacktransparentModuleFrontController extends PagarmepsOrderModuleFrontController
{
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
            return header('HTTP/1.1 403 Invalid POSTback data');
        }

        $id = Tools::getValue('id');
        $current_status = Tools::getValue('current_status');
        $prestashop_new_order_status= Pagarmeps::getStatusId($current_status);
        $transaction = Tools::getValue('transaction');

        Pagarmeps::addLog('Postback: transaction id='.$id.' | status:'.$current_status, 1, 'info', 'Pagarme', null);

        if($current_status == 'authorized') {
            return header('HTTP/1.1 200 Order already authorized');
        }


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

        Pagarmeps::addLog('Postback: Order ' . $order->id . ' successfully updated to' . $current_status);

        return header('HTTP/1.1 200 Order successfully updated');
    }
}
