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
class PagarmepsConfirmationModuleFrontController extends ModuleFrontController
{
    private function loader($className) {
    }

    public function __construct($response = array()) {
        spl_autoload_register(array($this, 'loader'));
        parent::__construct($response);
    }
    /**
     * @param $address
     * @return int|mixed
     */
    private function getAddressNumber($address)
    {
        $addressNumber = filter_var($address->address1, FILTER_SANITIZE_NUMBER_INT);
        if ($addressNumber) {
            return $addressNumber;
        }
        return 10;
    }

    public function postProcess()
    {
        $integrationMode = Configuration::get('PAGARME_INTEGRATION_MODE');
        $oneClickBuy = (bool)Configuration::get('PAGARME_ONE_CLICK_BUY');


        if ( (Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false) ) {
            return false;
        }

        if ( (Tools::getValue('payment_way') == 'card') && (Tools::isSubmit('card_hash') == false) ) {
            return false;
        }

        if ( (Tools::getValue('payment_way') == 'oneclickbuy') && (Tools::isSubmit('choosen_card') == false) ) {
            return false;
        }

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        $payment_way = Tools::getValue('payment_way');
        $card_hash = Tools::getValue('card_hash');
        $token = Tools::getValue('token');
        $choosen_card = Tools::getValue('choosen_card');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);

        $currency_id = (int)Context::getContext()->currency->id;

        if ($secure_key !== $customer->secure_key) {
            Pagarmeps::addLog('Invalid secure key', 1, 'info', 'Pagarme', null);

            $this->errors[] = $this->module->l('An error occured. Please contact the merchant to have more informations');
            return $this->setTemplate('error.tpl');
        }

        $api_key = Configuration::get('PAGARME_API_KEY');
        Pagarme::setApiKey($api_key);

        if($integrationMode == 'gateway') {
            $transaction_data = array();

            $transaction_data['amount'] = $cart->getOrderTotal()*100;

            if($payment_way == 'card' || $payment_way == 'oneclickbuy') {
                $payment_method_name = 'Cartão';

                $transaction_data['payment_method'] = 'credit_card';
                $transaction_data['installments'] = Tools::getValue('installment') ? Tools::getValue('installment') : 1;

                if(Tools::isSubmit('installment') != false && (bool)Configuration::get('PAGARME_INSTALLMENT') === true){
                    $calculateInstallments = $this->calculateInstallmentsForOrder($transaction_data['amount']);
                    $transaction_data['amount'] = $this->amountToCapture($calculateInstallments);
                }

                if(isset($card_hash)) {
                    $transaction_data['card_hash'] = $card_hash;
                }

                if(isset($choosen_card)) {
                    $transaction_data['card_id'] = $choosen_card;
                }
            }

            if($payment_way == 'boleto') {
                $payment_method_name = 'Boleto';

                $transaction_data['payment_method'] = 'boleto';

                $this->createDiscountAmount();
                $transaction_data['amount'] = $cart->getOrderTotal()*100;
            }

            $transaction_data['async'] = false;
            $transaction_data['postback_url'] = _PS_BASE_URL_ .__PS_BASE_URI__.'module/pagarmeps/postback';

            $transaction_data['customer'] = $this->getCustomerData($cart);
            $transaction_data['metadata'] = array(
                'cart_id' => $cart_id
            );

            $transaction = new PagarMe_Transaction($transaction_data);

            try {
                $transaction->charge();

                Pagarmeps::addLog('Transaction successfully created', 1, 'info', 'Pagarme', null);
                Pagarmeps::addLog('Transaction ID:' . $transaction->id . ' | status: ' . $transaction->status, 1, 'info', 'Pagarme', null);
            } catch (PagarMe_Exception $e) {
                Pagarmeps::addLog('Failed to create transaction', 1, 'info', 'Pagarme', null);
                Pagarmeps::addLog('Fail reason: '. $e->getMessage(), 1, 'info', 'Pagarme', null);

                $this->errors[] = $e->getMessage();
            }

            if ($transaction->getStatus() === 'refused') {
                Pagarmeps::addLog('Transaction refused', 1, 'info', 'Pagarme', null);
                Pagarmeps::addLog('Transaction ID:' . $transaction->id . ' | status: ' . $transaction->status, 1, 'info', 'Pagarme', null);
                $this->errors[] = 'Ocorreu um erro ao realizar a transação. Que tal verificar os dados e tentar novamente?';
            }

        } else if( $integrationMode == 'checkout_transparente' && !empty($token) ) {
            $cart = new Cart((int)$cart_id);

            $amount = $cart->getOrderTotal()*100;

            $payment_method_name = 'Checkout transparente';

            $transaction = PagarMe_Transaction::findById($token);

            if ($transaction->getPaymentMethod() == 'boleto') {
                $this->createDiscountAmount();
                $capture_amount = $this->context->cart->getOrderTotal() * 100;
            } else {
                $calculateInstallments = $this->calculateInstallmentsForOrder($amount);
                $capture_amount = $this->amountToCapture($calculateInstallments, $transaction);
            }

            $capture_data = array(
                'amount' => $capture_amount,
                'metadata' => array (
                    'cart_id' => $cart_id
                )
            );

            try {
                $transaction->capture($capture_data);
                Pagarmeps::addLog('Transaction successfully captured', 1, 'info', 'Pagarme', null);
                Pagarmeps::addLog('Transaction ID:' . $transaction->id . ' | status: ' . $transaction->status, 1, 'info', 'Pagarme', null);
            } catch (PagarMe_Exception $e) {
                Pagarmeps::addLog('Failed to capture transaction', 1, 'info', 'Pagarme', null);
                Pagarmeps::addLog('Fail reason: '. $e->getMessage(), 1, 'info', 'Pagarme', null);
                $this->errors[] = $e->getMessage();
            }

            if ($transaction->getPaymentMethod() == 'credit_card') {
                $card = $transaction->getCard();
                $cardInfo = '<strong> Bandeira : </strong>' . $card->getBrand() . '<strong> Parcelas : </strong>' . $transaction->getInstallments();
                $payment_method_name = 'Cartão';
            } else {
                $cardInfo = null;
                $payment_method_name = 'Boleto';
            }

        }

        if( count($this->errors) > 0 ) {
            return $this->setTemplate('error.tpl');
        }

        $prestashop_order_status = Pagarmeps::getStatusId($transaction->status);

        $this->module->validateOrder(
            $cart->id,
            $prestashop_order_status,
            $cart->getOrderTotal(),
            $payment_method_name,
            null,
            array(),
            $currency_id,
            false,
            $secure_key
        );

        $order_id = Order::getOrderByCartId((int) $cart->id);

        $order = new Order($order_id);

        $this->updateOrderPayments($order, $transaction);

        $prestashop_paid_status = Pagarmeps::getStatusId('paid');
        $prestashop_order_status = Pagarmeps::getStatusId($transaction->status);

        //Generate Invoice if paid
        if( !$order->hasInvoice() && ($prestashop_paid_status == $prestashop_order_status) ){
            Pagarmeps::addLog('Generated invoice for order ' . $order->id, 1, 'info', 'Pagarme', $order_id);
            $order->setInvoice(true);
        }

        $order->payment = $payment_method_name;

        if( !$order->save() ) {
            Pagarmeps::addLog('Cannot save order', 1, 'info', 'Pagarme', $order_id);
        }

        $pgmTrans = new PagarmepsTransactionClass();

        $pgmTrans->id_order = (int)$order_id;
        $pgmTrans->id_object_pagarme = pSQL($transaction->id);
        $pgmTrans->current_status = $transaction->current_status;

        if( !$pgmTrans->save() ) {
            Pagarmeps::addLog('Cannot save the transaction on database ', 1, 'info', 'Pagarme', $order_id);
        }

        if($choosen_card) {
            $this->savePagarMeCard($choosen_card, $cart);
        }

        return Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);

    } 

    private function updateOrderPayments($order, $transaction) {
        $order_payments = $order->getOrderPayments();

        foreach ($order_payments as $order_payment) {
            $order_payment->transaction_id = $transaction->getId();

            if ($transaction->getPaymentMethod() == "credit_card") {

                $card = $transaction->getCard();
                $order_payment->card_number = $card->getFirstDigits() . '****' . $card->getLastDigits();
                $order_payment->card_brand 	= $card->getBrand();
                $order_payment->card_holder = $card->getHolderName();
                $order_payment->installments = $transaction->getInstallments();
            }

            $order_payment->save();
        }
    }

    private function savePagarMeCard($choosen_card, $cart) {
        $pgmCard = new PagarmepsCardClass();

        $pgmCard->id_object_card_pagarme = pSQL($choosen_card);
        $pgmCard->id_client = (int)$cart->id_customer;

        if( !$pgmCard->save() ) {
            Pagarmeps::addLog('Card saved '. $choosen_card, 1, 'info', 'Pagarme', $order_id);
        }

    }

    private function getCustomerData($cart) {
        $customer = new Customer((int)$cart->id_customer);
        $address = new Address((int)$cart->id_address_invoice);

        return array(
            'name'            => $customer->firstname.' '.$customer->lastname,
            'document_number' => Pagarmeps::getCustomerCPFouCNPJ($address, (int)$cart->id_customer),
            'email'           => $customer->email,
            'address'         => array(
                'street'        => $address->address1,
                'neighborhood'  => $address->address2,
                'zipcode'       => $address->postcode,
                'street_number' => $this->getAddressNumber($address),
                'complementary' => $address->other
            ),
            'phone' => $this->getPhoneData($address)
        );
    }

    private function getPhoneData($address) {

        $phone = empty($address->phone) ? $address->phone_mobile : $address->phone;
        $phone = preg_replace('/\D/', '', $phone);

        if(!empty($phone) && Tools::strlen($phone) > 2) {
            $ddd = Tools::substr($phone, 0, 2);
            $phone = Tools::substr($phone, 2, Tools::strlen($phone));
        }

        return array(
            'ddd'    => $ddd,
            'number' => $phone
        );
    }

    private function createDiscountAmount()
    {
        if (!Configuration::get('PAGARME_DISCOUNT_BOLETO')) {
            return $this;
        }

        foreach ($this->context->cart->getCartRules() as $cart_rule) {
            if ($cart_rule['description'] == 'discount_boleto') {
                return $this;
            }
        }

        $languages = Language::getLanguages();
        foreach($languages as $key => $language) {
            $cart_rule_names[$language['id_lang']] = "Desconto Boleto";
        }
        $cart_rule = new CartRule();

        $cart_rule->name = $cart_rule_names;
        $cart_rule->id_customer = $this->context->cart->id_customer;
        $cart_rule->date_from = date('Y-m-d H:i:s');
        $cart_rule->date_to = date('Y-m-d H:i:s', strtotime("+2 days",strtotime(date('Y-m-d'))));
        $cart_rule->description = 'discount_boleto';
        $cart_rule->quantity = 1;
        $cart_rule->quantity_per_user = 1;
        $cart_rule->priority = 1;
        $cart_rule->partial_use = 1;
        $cart_rule->code = md5('discount_boleto' .$this->context->cart->id_customer . date('Y-m-d H:i:s'));
        $cart_rule->minimum_amount = 0;
        $cart_rule->minimum_amount_tax = 0;
        $cart_rule->minimum_amount_currency = 1;
        $cart_rule->minimum_amount_shipping = 0;
        $cart_rule->country_restriction = 0;
        $cart_rule->carrier_restriction = 0;
        $cart_rule->group_restriction = 0;
        $cart_rule->cart_rule_restriction = 0;
        $cart_rule->product_restriction = 0;
        $cart_rule->shop_restriction = 0;
        $cart_rule->free_shipping = 0;
        #$cart_rule->reduction_percent = Configuration::get('PAGARME_DISCOUNT_BOLETO');
        $cart_rule->reduction_amount = $this->calculateBoletoDiscountAmount();
        $cart_rule->reduction_tax = 1;
        $cart_rule->reduction_currency = 1;
        $cart_rule->reduction_product = 0;
        $cart_rule->gift_product = 0;
        $cart_rule->gift_product_attribute = 0;
        $cart_rule->highlight = 0;
        $cart_rule->active = 1;

        $cart_rule->add();
        $this->context->cart->addCartRule($cart_rule->id);
        $this->context->cart->save();
    }

    protected function calculateBoletoDiscountAmount()
    {
        $taxCalculationMethod = Group::getPriceDisplayMethod((int)Group::getCurrent()->id);
        $useTax = !($taxCalculationMethod == PS_TAX_EXC);

        $cart = $this->context->cart;
        $shippingAmount = $cart->getOrderTotal($useTax, Cart::ONLY_SHIPPING, null, $cart->id_carrier, false);

        $totalAmount = $cart->getOrderTotal();
        $totalAmountFreeShipping = $totalAmount - $shippingAmount;

        $discountAmount = (Configuration::get('PAGARME_DISCOUNT_BOLETO') / 100) * $totalAmountFreeShipping;
        return number_format($discountAmount, '2', '.', '');
    }

    private function calculateInstallmentsForOrder($amount){

        $interest_rate = Configuration::get('PAGARME_INSTALLMENT_TAX');

        $max_installments = Configuration::get('PAGARME_INSTALLMENT_MAX_NUMBER');

        $free_installments = Configuration::get('PAGARME_INSTALLMENT_TAX_FREE');

        return PagarMe_Transaction::calculateInstallmentsAmount($amount, $interest_rate, $max_installments, $free_installments);

    }

    private function amountToCapture($calculateInstallments, $transaction = null){

        $installments = $calculateInstallments['installments'];
        $chosenInstallments = Tools::getValue('installment');

        if (!is_null($transaction)) {
            $chosenInstallments = $transaction->installments;
        }

        return $installments[$chosenInstallments]['amount'];
    }
}
