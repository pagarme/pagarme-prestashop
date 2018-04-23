<?php

class PagarmepsOrderModuleFrontController extends ModuleFrontController
{
    private function loader() {
        include '../../lib/pagarme/PagarMe.php';
    }

    public function __construct($response = array()) {
        spl_autoload_register(array($this, 'loader'));
        parent::__construct($response);
        $this->display_header = true;
        $this->display_header_javascript = true;
        $this->display_footer = true;
    }

    public function updateOrderStatus($order, $transaction) {
        $current_status = $transaction['status'];
        $prestashop_new_order_status = Pagarmeps::getStatusId($current_status);

        Pagarmeps::addLog('Postback: order ' . $order->id . ' current state: ' . $order->current_state . ' new status: ' . $prestashop_new_order_status, 1, 'info', 'Pagarme', null);
        if($order->current_state == $prestashop_new_order_status) {
            Pagarmeps::addLog('Order ' . $order->id . ': order already ' . $current_status, 1, 'info', 'Pagarme', $order->id);
            return false;
        }

        $order->current_state = $prestashop_new_order_status;

        if(!$order->save()) {
            Pagarmeps::addLog('Order ' . $order->id . ': failed to update order', 1, 'info', 'Pagarme', $order->id);
            return false;
        }

        $this->addOrderHistory($order, $prestashop_new_order_status);

        $formated_amount = $transaction['paid_amount']/100;
        if( $current_status === "paid" && !$order->addOrderPayment($formated_amount, null, $transaction['id']) ) {
          Pagarmeps::addLog('Order ' . $order->id . ': failed to add order payment', 1, 'info', 'Pagarme', $order->id);
          return false;
        }

        return true;
    }

    public function addOrderHistory($order, $prestashop_new_order_status) {
        $history = new OrderHistory();
        $history->id_order = (int)$order->id;
        $history->id_order_state = $prestashop_new_order_status;

        $history->addWithemail();
        $history->changeIdOrderState($prestashop_new_order_status, (int)$order->id);
    }
}
