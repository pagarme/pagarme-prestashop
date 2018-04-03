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

    public function updateOrderStatus($order, $prestashop_new_order_status) {

        if($order->current_state == $prestashop_new_order_status) {
            return false;
        }

        $order->current_state = $prestashop_new_order_status;

        if(!$order->save()){
            Pagarmeps::addLog('Postback: failed to update order', 1, 'info', 'Pagarme', null);

            return false;
        }

        $this->addOrderHistory($order, $prestashop_new_order_status);

        Pagarmeps::addLog('Postback: order ' . $order->id . ' successfully updated to ' . $current_status, 1, 'info', 'Pagarme', null);

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
