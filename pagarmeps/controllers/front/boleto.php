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

class PagarmepsBoletoModuleFrontController extends ModuleFrontController
{

    private function createDiscountPercentageRule($reduction_percent)
    {
        $cart_rule_name = "Desconto Boleto (" . $reduction_percent . "%)";
        foreach ($this->context->cart->getCartRules() as $cart_rule) {
            if ($cart_rule->description == $cart_rule_name) {
                return null;
            }
        }

        $languages = Language::getLanguages();
        foreach($languages as $key => $language) {
            $cart_rule_localized_names[$language['id_lang']] = $cart_rule_name;
        }

        $cart_rule = new CartRule();
        $cart_rule->name = $cart_rule_localized_names;
        $cart_rule->id_customer = $this->context->cart->id_customer;

        $now = new \DateTime();
        $cart_rule->date_from = $now->format('Y-m-d H:i:s');;
        $cart_rule->date_to = $now->modify('+2 days')->format('Y-m-d H:i:s');;
        $cart_rule->description = 'discount_boleto';
        $cart_rule->quantity = 1;
        $cart_rule->quantity_per_user = 1;
        $cart_rule->priority = 1;
        $cart_rule->partial_use = 1;
        $cart_rule->code = sprintf('%s_%s',
            'pagarmeps',
            md5('discount_boleto' .$this->context->cart->id_customer . date('Y-m-d H:i:s'))
        );
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
        $cart_rule->reduction_percent = $reduction_percent;
        $cart_rule->reduction_tax = 1;
        $cart_rule->reduction_currency = 1;
        $cart_rule->reduction_product = 0;
        $cart_rule->gift_product = 0;
        $cart_rule->gift_product_attribute = 0;
        $cart_rule->highlight = 0;
        $cart_rule->active = 1;

        return $cart_rule;
    }

    /**
    * Do whatever you have to before redirecting the customer on the website of your payment processor.
    */
    public function postProcess()
    {
        /**
        * Oops, an error occured.
        */
        if (Tools::getValue('action') == 'error'){
            return $this->displayError('An error occurred while trying to redirect the customer');
        }

        $cart = Context::getContext()->cart;

        $boleto_discount_percentage = Configuration::get('PAGARME_DISCOUNT_BOLETO');
        $cart_rule = $this->createDiscountPercentageRule($boleto_discount_percentage);

        if($cart_rule) {
            $cart_rule->add();
            $cart->addCartRule($cart_rule->id);
            $cart->save();
        }

        $total_order = $cart->getOrderTotal();
        $pay_way = Configuration::get('PAGARME_PAY_WAY');
        $integration_mode = Configuration::get('PAGARME_INTEGRATION_MODE');
        $encryption_key = Configuration::get('PAGARME_ENCRYPTION_KEY');

        if(empty($encryption_key)){
            return $this->displayError('An error occurred, missing configuration for the Pagar.me Module');
        }
        if($integration_mode != 'gateway' && !($pay_way == 'boleto' || $pay_way == 'both')){
            return $this->displayError('This payment mode is not activated, please contact the administrator of this site');
        }

        $this->context->smarty->assign(array(
            'cart_id' => $cart->id,
            'total_order' => $total_order,
            'encryption_key' => $encryption_key,
            'pay_way' => $pay_way,
            'integration_mode' => $integration_mode,
            'secure_key' => Context::getContext()->customer->secure_key,
            'boleto_discount_percentage' => $boleto_discount_percentage,
        ));

        return $this->setTemplate('redirect-boleto.tpl');
    }

    protected function displayError($message, $description = false)
    {
        /**
        * Create the breadcrumb for your ModuleFrontController.
        */
        $link_element = sprintf(
            '<a href="%s">%s</a><span class="navigation-pipe">&gt;</span>%s',
            $this->context->link->getPageLink('order', null, null, 'step=3',
            $this->module->l('Payment'),
            $this->module->l('Error'))
        );

        $this->context->smarty->assign('path', $link_element);

        /**
        * Set error message and description for the template.
        */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('error.tpl');
    }

    /**
    * Set default medias for this controller
    */
    public function setMedia() {
        parent::setMedia();
    }

}
