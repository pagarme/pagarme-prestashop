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
        $new_order_status = Pagarmeps::getStatusId($current_status);

        if($order->current_state == $new_order_status) {
            Pagarmeps::addLog('Order already ' . $current_status);
            return false;
        }

        $order->current_state = $new_order_status;

        $this->addOrderHistory($order, $new_order_status);

        $formated_amount = $transaction['paid_amount']/100;
        if( $current_status === "paid" && !$order->addOrderPayment($formated_amount, null, $transaction['id']) ) {
          Pagarmeps::addLog('Failed to add order payment');
          return false;
        }

        //Generate Invoice if paid
        if( !$order->hasInvoice() && $current_status == 'paid' ){
            $order->setInvoice(true);

            Pagarmeps::addLog('Successfully Generated invoice');
        }

        if(!$order->save()) {
            Pagarmeps::addLog('Failed to save order');
            return false;
        }

        return true;
    }

    public function addOrderHistory($order, $new_order_status) {
        $history = new OrderHistory();
        $history->id_order = (int)$order->id;
        $history->id_order_state = $new_order_status;

        $history->addWithemail();
        $history->changeIdOrderState($new_order_status, (int)$order->id);
    }
}
