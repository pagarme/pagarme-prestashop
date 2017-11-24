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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Pagarmeps extends PaymentModule
{
    protected $config_form = false;

    private function loader($className)
    {
        if (strrpos($className, 'PagarMe_') !== false) {
            $className = Tools::substr($className, 8);
            include dirname(__FILE__).'/lib/pagarme/'.$className . '.php';
        } elseif (strrpos($className, 'Pagarmeps') !== false) {
            include dirname(__FILE__).'/classes/'.$className . '.php';
        } elseif ($className == 'Pagarme' || $className == 'RestClient') {
            include dirname(__FILE__).'/lib/pagarme/'.$className . '.php';
        }
    }

    public function __construct()
    {
        $this->name = 'pagarmeps';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.0';
        $this->author = 'Pagar.me';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        spl_autoload_register(array($this, 'loader'));
        parent::__construct();

        $this->displayName = $this->l('Pagar.Me');
        $this->description = $this->l('O Pagar.me aprova 92 a cada 100 tentativas de pagamento para aumentar sua receita com conversão de gateway e facilidade de PSP.');

        $this->confirmUninstall = $this->l('Are you really sure you want to uninstall this module ?');

        $this->limited_countries = array('BR');

        $this->limited_currencies = array('BRL');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module is not available in your country');
            return false;
        }

        include(dirname(__FILE__).'/sql/install.php');

        Configuration::updateValue('PAGARME_LIVE_MODE', false);
        Configuration::updateValue('PAGARME_ACTIVATE_LOG', false);
        Configuration::updateValue('PAGARME_CONFIRM_CUSTOMER_DATA_IN_CHECKOUT_PAGARME', true);
        Configuration::updateValue('PAGARME_EXPIRATION_COMBO', false);

        //Shop name for Soft Descriptor
        $shop_name = Configuration::get('PS_SHOP_NAME');
        Configuration::updateValue('PAGARME_SOFT_DESCRIPTOR', Tools::substr($shop_name, 0, 13));

        //Order State
        if (!$this->createStates()) {
            $this->_errors[] = $this->l('can\'t create states');
            return false;
        }

        return parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('displayHeader') &&
        $this->registerHook('backOfficeHeader') &&
        $this->registerHook('payment') &&
        $this->registerHook('paymentReturn') &&
        $this->registerHook('displayOrderDetail') &&
        $this->registerHook('actionPaymentCCAdd') &&
        $this->registerHook('actionPaymentConfirmation') &&
        $this->registerHook('displayHeader') &&
        $this->registerHook('displayPayment') &&
        $this->registerHook('displayPaymentReturn') &&
        $this->registerHook('displayPaymentTop') &&
        $this->registerHook('productActions') &&
        $this->registerHook('cart') &&
        $this->registerHook('displayShoppingCart') &&
        $this->registerHook('shoppingCartExtra') &&
        $this->registerHook('customerAccount');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        Configuration::deleteByName('PAGARME_LIVE_MODE');

        $order_state = new OrderState(Configuration::get('PAGARME_DEFAULT_PROCESSING'));
        $order_state->delete();
        $order_state = new OrderState(Configuration::get('PAGARME_DEFAULT_WAITING_PAYMENT'));
        $order_state->delete();
        $order_state = new OrderState(Configuration::get('PAGARME_DEFAULT_AUTHORIZED'));
        $order_state->delete();
        $order_state = new OrderState(Configuration::get('PAGARME_DEFAULT_PAID'));
        $order_state->delete();
        $order_state = new OrderState(Configuration::get('PAGARME_DEFAULT_PENDING_REFUND'));
        $order_state->delete();
        $order_state = new OrderState(Configuration::get('PAGARME_DEFAULT_REFUNDED'));
        $order_state->delete();
        $order_state = new OrderState(Configuration::get('PAGARME_DEFAULT_REFUSED'));
        $order_state->delete();

        Configuration::deleteByName('PAGARME_DEFAULT_PROCESSING');
        Configuration::deleteByName('PAGARME_DEFAULT_WAITING_PAYMENT');
        Configuration::deleteByName('PAGARME_DEFAULT_AUTHORIZED');
        Configuration::deleteByName('PAGARME_DEFAULT_PAID');
        Configuration::deleteByName('PAGARME_DEFAULT_PENDING_REFUND');
        Configuration::deleteByName('PAGARME_DEFAULT_REFUNDED');
        Configuration::deleteByName('PAGARME_DEFAULT_REFUSED');
        Configuration::deleteByName('PAGARME_DEFAULT_STATUS');
        Configuration::deleteByName('PAGARME_INTEGRATION_MODE');
        Configuration::deleteByName('PAGARME_API_KEY');
        Configuration::deleteByName('PAGARME_ENCRYPTION_KEY');
        Configuration::deleteByName('PAGARME_PAY_WAY');
        Configuration::deleteByName('PAGARME_ONE_CLICK_BUY');
        Configuration::deleteByName('PAGARME_CONFIRM_CUSTOMER_DATA_IN_CHECKOUT_PAGARME');
        Configuration::deleteByName('PAGARME_EXPIRATION_COMBO');

        return parent::uninstall();
    }

    public function createStates()
    {
        //$languages = Language::getLanguages();
        /*
        processing, authorized, paid, refunded, waiting_payment, pending_refund, refused

        */

        //PROCESSING
        $order_state = new OrderState();
        $order_state->invoice = false;
        $order_state->send_email = false;
        $order_state->module_name = $this->name;
        $order_state->color = '#EE6C00';
        $order_state->unremovable = false;
        $order_state->hidden = false;
        $order_state->logable = true;
        $order_state->delivery = false;
        $order_state->shipped = false;
        $order_state->paid = false;
        $order_state->deleted = false;
        $order_state->name = array();
        $order_state->template = false;

        foreach (Language::getLanguages(false) as $language) {
            $order_state->name[(int)$language['id_lang']] = 'Em processo';
            //$order_state->template[(int)$language['id_lang']] = 'order_conf';
        }

        if (!$order_state->add()) {
            return false;
        }

        $file = _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif';
        Tools::copy((dirname(__file__).'/views/img/pagarme_x16.gif'), $file);
        Configuration::updateValue('PAGARME_DEFAULT_STATUS', $order_state->id);
        Configuration::updateValue('PAGARME_DEFAULT_PROCESSING', $order_state->id);


        //WAITING_PAYMENT
        $order_state = new OrderState();
        $order_state->invoice = false;
        $order_state->send_email = false;
        $order_state->module_name = $this->name;
        $order_state->color = '#EE6C00';
        $order_state->unremovable = false;
        $order_state->hidden = false;
        $order_state->logable = true;
        $order_state->delivery = false;
        $order_state->shipped = false;
        $order_state->paid = false;
        $order_state->deleted = false;
        $order_state->name = array();
        $order_state->template = false;

        foreach (Language::getLanguages(false) as $language) {
            $order_state->name[(int)$language['id_lang']] = 'Aguardando Pagamento';
        }

        if (!$order_state->add()) {
            return false;
        }

        $file = _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif';
        Tools::copy((dirname(__file__).'/views/img/pagarme_x16.gif'), $file);
        Configuration::updateValue('PAGARME_DEFAULT_WAITING_PAYMENT', $order_state->id);


        //AUTHORIZED
        $order_state = new OrderState();
        $order_state->invoice = false;
        $order_state->send_email = true;
        $order_state->module_name = $this->name;
        $order_state->color = '#FFB83C';
        $order_state->unremovable = false;
        $order_state->hidden = false;
        $order_state->logable = true;
        $order_state->delivery = false;
        $order_state->shipped = false;
        $order_state->paid = true;
        $order_state->deleted = false;
        $order_state->name = array();
        $order_state->template = false;

        foreach (Language::getLanguages(false) as $language) {
            $order_state->name[(int)$language['id_lang']] = 'Autorizado';
        }

        if (!$order_state->add()) {
            return false;
        }

        $file = _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif';
        Tools::copy((dirname(__file__).'/views/img/pagarme_x16.gif'), $file);
        Configuration::updateValue('PAGARME_DEFAULT_AUTHORIZED', $order_state->id);



        //PAID
        $order_state = new OrderState();
        $order_state->invoice = false;
        $order_state->send_email = true;
        $order_state->module_name = $this->name;
        $order_state->color = '#35C41F';
        $order_state->unremovable = false;
        $order_state->hidden = false;
        $order_state->logable = true;
        $order_state->delivery = false;
        $order_state->shipped = false;
        $order_state->paid = true;
        $order_state->deleted = false;
        $order_state->name = array();
        $order_state->template = array();

        foreach (Language::getLanguages(false) as $language) {
            $order_state->name[(int)$language['id_lang']] = 'Pago';
            $order_state->template[(int)$language['id_lang']] = 'order_conf';
        }

        if (!$order_state->add()) {
            return false;
        }

        $file = _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif';
        Tools::copy((dirname(__file__).'/views/img/pagarme_x16.gif'), $file);
        Configuration::updateValue('PAGARME_DEFAULT_PAID', $order_state->id);


        //PENDING_REFUND
        $order_state = new OrderState();
        $order_state->invoice = false;
        $order_state->send_email = false;
        $order_state->module_name = $this->name;
        $order_state->color = '#BC69B1';
        $order_state->unremovable = false;
        $order_state->hidden = false;
        $order_state->logable = true;
        $order_state->delivery = false;
        $order_state->shipped = false;
        $order_state->paid = false;
        $order_state->deleted = false;
        $order_state->name = array();
        $order_state->template = false;

        foreach (Language::getLanguages(false) as $language) {
            $order_state->name[(int)$language['id_lang']] = 'Aguardando Reembolso';
            //$order_state->template[(int)$language['id_lang']] = 'refund';
        }

        if (!$order_state->add()) {
            return false;
        }

        $file = _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif';
        Tools::copy((dirname(__file__).'/views/img/pagarme_x16.gif'), $file);
        Configuration::updateValue('PAGARME_DEFAULT_PENDING_REFUND', $order_state->id);

        //REFUNDED
        $order_state = new OrderState();
        $order_state->invoice = false;
        $order_state->send_email = true;
        $order_state->module_name = $this->name;
        $order_state->color = '#FF21E1';
        $order_state->unremovable = false;
        $order_state->hidden = false;
        $order_state->logable = true;
        $order_state->delivery = false;
        $order_state->shipped = false;
        $order_state->paid = false;
        $order_state->deleted = false;
        $order_state->name = array();
        $order_state->template = array();

        foreach (Language::getLanguages(false) as $language) {
            $order_state->name[(int)$language['id_lang']] = 'Reembolsado';
            $order_state->template[(int)$language['id_lang']] = 'refund';
        }

        if (!$order_state->add()) {
            return false;
        }

        $file = _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif';
        Tools::copy((dirname(__file__).'/views/img/pagarme_x16.gif'), $file);
        Configuration::updateValue('PAGARME_DEFAULT_REFUNDED', $order_state->id);




        //REFUSED
        $order_state = new OrderState();
        $order_state->invoice = false;
        $order_state->send_email = true;
        $order_state->module_name = $this->name;
        $order_state->color = '#F21D27';
        $order_state->unremovable = false;
        $order_state->hidden = false;
        $order_state->logable = true;
        $order_state->delivery = false;
        $order_state->shipped = false;
        $order_state->paid = false;
        $order_state->deleted = false;
        $order_state->name = array();
        $order_state->template = array();

        foreach (Language::getLanguages(false) as $language) {
            $order_state->name[(int)$language['id_lang']] = 'Negado';
            $order_state->template[(int)$language['id_lang']] = 'order_canceled';
        }

        if (!$order_state->add()) {
            return false;
        }

        $file = _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif';
        Tools::copy((dirname(__file__).'/views/img/pagarme_x16.gif'), $file);
        Configuration::updateValue('PAGARME_DEFAULT_REFUSED', $order_state->id);

        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPagarmeModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPagarmeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate'),
                        'name' => 'PAGARME_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Activate this module'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'required' => true,
                        'desc' => $this->l('Choose your integration mode'),
                        'name' => 'PAGARME_INTEGRATION_MODE',
                        'label' => $this->l('Integration'),
                        'options' => array(
                            'id' => 'id_integration',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id_integration' => 'checkout_transparente',                 // The value of the 'value' attribute of the <option> tag.
                                    'name' => $this->l('Checkout Transparente'),             // The value of the text content of the  <option> tag.
                                ),
                                array(
                                    'id_integration' => 'gateway',                 // The value of the 'value' attribute of the <option> tag.
                                    'name' => $this->l('Gateway Simples'),               // The value of the text content of the  <option> tag.
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'required' => true,
                        'name' => 'PAGARME_API_KEY',
                        'label' => $this->l('API Key'),
                        'desc' => $this->l('You can find this key in your').' <a href="https://dashboard.pagar.me/" target="blank">'.$this->l('Pagar.me Dashboard').'</a>',
                    ),
                    array(
                        'type' => 'text',
                        'required' => true,
                        'name' => 'PAGARME_ENCRYPTION_KEY',
                        'label' => $this->l('Encryption Key'),
                        'desc' => $this->l('You can find this key in your').' <a href="https://dashboard.pagar.me/" target="blank">'.$this->l('Pagar.me Dashboard').'</a>',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'required' => true,
                        'desc' => $this->l('Choose between Credit card and Boleto, or both'),
                        'name' => 'PAGARME_PAY_WAY',
                        'label' => $this->l('Payment way'),
                        'options' => array(
                            'id' => 'id_payway',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id_payway' => 'both',                 // The value of the 'value' attribute of the <option> tag.
                                    'name' => $this->l('Credit Card and Boleto'),             // The value of the text content of the  <option> tag.
                                ),
                                array(
                                    'id_payway' => 'credit_card',                 // The value of the 'value' attribute of the <option> tag.
                                    'name' => $this->l('Credit Card only'),               // The value of the text content of the  <option> tag.
                                ),
                                array(
                                    'id_payway' => 'boleto',                 // The value of the 'value' attribute of the <option> tag.
                                    'name' => $this->l('Boleto only'),               // The value of the text content of the  <option> tag.
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('One Click Buy'),
                        'name' => 'PAGARME_ONE_CLICK_BUY',
                        'is_bool' => true,
                        'desc' => $this->l('Activate the One Click Buy feature on your store'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Confirmar dados do comprador no checkout Pagar.me'),
                        'name' => 'PAGARME_CONFIRM_CUSTOMER_DATA_IN_CHECKOUT_PAGARME',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Allow installment'),
                        'name' => 'PAGARME_INSTALLMENT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'prefix' => 'R$',
                        'label' => $this->l('Min parcel value'),
                        'name' => 'PAGARME_INSTALLMENT_MIN_VALUE',
                        'desc' => $this->l('Minimum installment value'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Max parcel number'),
                        'name' => 'PAGARME_INSTALLMENT_MAX_NUMBER',
                        'desc' => $this->l('Maximum installment number'),
                        'options' => array(
                            'id' => 'id_max',
                            'name' => 'name',
                            'query' => array(
                                array('id_max' => '2', 'name' => '2 '.$this->l('Times')),
                                array('id_max' => '3', 'name' => '3 '.$this->l('Times')),
                                array('id_max' => '4', 'name' => '4 '.$this->l('Times')),
                                array('id_max' => '5', 'name' => '5 '.$this->l('Times')),
                                array('id_max' => '6', 'name' => '6 '.$this->l('Times')),
                                array('id_max' => '7', 'name' => '7 '.$this->l('Times')),
                                array('id_max' => '8', 'name' => '8 '.$this->l('Times')),
                                array('id_max' => '9', 'name' => '9 '.$this->l('Times')),
                                array('id_max' => '10', 'name' => '10 '.$this->l('Times')),
                                array('id_max' => '11', 'name' => '11 '.$this->l('Times')),
                                array('id_max' => '12', 'name' => '12 '.$this->l('Times')),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Installment Tax rate'),
                        'name' => 'PAGARME_INSTALLMENT_TAX',
                        'prefix' => '%',
                        'maxlength' => 13,
                        'desc' => $this->l('Installment Tax rate in percent'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Desconto Boleto'),
                        'name' => 'PAGARME_DISCOUNT_BOLETO',
                        'prefix' => '%',
                        'maxlength' => 13,
                        'desc' => $this->l('Installment Tax rate in percent'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Parcels without tax'),
                        'name' => 'PAGARME_INSTALLMENT_TAX_FREE',
                        'desc' => $this->l('Parcels without tax application'),
                        'options' => array(
                            'id' => 'id_max',
                            'name' => 'name',
                            'query' => array(
                                array('id_max' => 'all', 'name' => $this->l('Without tax at all')),
                                array('id_max' => 'none', 'name' => $this->l('Every parcel with tax')),
                                array('id_max' => '2', 'name' => '2 '.$this->l('Parcels without tax')),
                                array('id_max' => '3', 'name' => '3 '.$this->l('Parcels without tax')),
                                array('id_max' => '4', 'name' => '4 '.$this->l('Parcels without tax')),
                                array('id_max' => '5', 'name' => '5 '.$this->l('Parcels without tax')),
                                array('id_max' => '6', 'name' => '6 '.$this->l('Parcels without tax')),
                                array('id_max' => '7', 'name' => '7 '.$this->l('Parcels without tax')),
                                array('id_max' => '8', 'name' => '8 '.$this->l('Parcels without tax')),
                                array('id_max' => '9', 'name' => '9 '.$this->l('Parcels without tax')),
                                array('id_max' => '10', 'name' => '10 '.$this->l('Parcels without tax')),
                                array('id_max' => '11', 'name' => '11 '.$this->l('Parcels without tax')),
                                array('id_max' => '12', 'name' => '12 '.$this->l('Parcels without tax')),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Boleto - Due delay'),
                        'name' => 'PAGARME_BOLETO_DELAY',
                        'desc' => $this->l('Days before the due date of the boletos'),
                        'options' => array(
                            'id' => 'id_max',
                            'name' => 'name',
                            'query' => array(
                                array('id_max' => '2', 'name' => '2 '.$this->l('Days')),
                                array('id_max' => '3', 'name' => '3 '.$this->l('Days')),
                                array('id_max' => '4', 'name' => '4 '.$this->l('Days')),
                                array('id_max' => '5', 'name' => '5 '.$this->l('Days')),
                                array('id_max' => '6', 'name' => '6 '.$this->l('Days')),
                                array('id_max' => '7', 'name' => '7 '.$this->l('Days')),
                                array('id_max' => '8', 'name' => '8 '.$this->l('Days')),
                                array('id_max' => '9', 'name' => '9 '.$this->l('Days')),
                                array('id_max' => '10', 'name' => '10 '.$this->l('Days')),
                                array('id_max' => '11', 'name' => '11 '.$this->l('Days')),
                                array('id_max' => '12', 'name' => '12 '.$this->l('Days')),
                                array('id_max' => '13', 'name' => '13 '.$this->l('Days')),
                                array('id_max' => '14', 'name' => '14 '.$this->l('Days')),
                                array('id_max' => '15', 'name' => '15 '.$this->l('Days')),
                                array('id_max' => '16', 'name' => '16 '.$this->l('Days')),
                                array('id_max' => '17', 'name' => '17 '.$this->l('Days')),
                                array('id_max' => '18', 'name' => '18 '.$this->l('Days')),
                                array('id_max' => '19', 'name' => '19 '.$this->l('Days')),
                                array('id_max' => '20', 'name' => '20 '.$this->l('Days')),
                                array('id_max' => '21', 'name' => '21 '.$this->l('Days')),
                                array('id_max' => '22', 'name' => '22 '.$this->l('Days')),
                                array('id_max' => '23', 'name' => '23 '.$this->l('Days')),
                                array('id_max' => '24', 'name' => '24 '.$this->l('Days')),
                                array('id_max' => '25', 'name' => '25 '.$this->l('Days')),
                                array('id_max' => '26', 'name' => '26 '.$this->l('Days')),
                                array('id_max' => '27', 'name' => '27 '.$this->l('Days')),
                                array('id_max' => '28', 'name' => '28 '.$this->l('Days')),
                                array('id_max' => '29', 'name' => '29 '.$this->l('Days')),
                                array('id_max' => '30', 'name' => '30 '.$this->l('Days')),
                                array('id_max' => '31', 'name' => '31 '.$this->l('Days')),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Bill descriptor'),
                        'name' => 'PAGARME_SOFT_DESCRIPTOR',
                        'maxlength' => 13,
                        'desc' => $this->l('Text that will appear on the credit card bill of your clients (13 char max) '),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate logs'),
                        'name' => 'PAGARME_ACTIVATE_LOG',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Apresentar lista para data de expiração'),
                        'name' => 'PAGARME_EXPIRATION_COMBO',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PAGARME_LIVE_MODE' => Configuration::get('PAGARME_LIVE_MODE'),
            'PAGARME_INTEGRATION_MODE' => Configuration::get('PAGARME_INTEGRATION_MODE'),
            'PAGARME_API_KEY' => Configuration::get('PAGARME_API_KEY'),
            'PAGARME_ENCRYPTION_KEY' => Configuration::get('PAGARME_ENCRYPTION_KEY'),
            'PAGARME_PAY_WAY' => Configuration::get('PAGARME_PAY_WAY'),
            'PAGARME_ONE_CLICK_BUY' => Configuration::get('PAGARME_ONE_CLICK_BUY'),
            'PAGARME_CONFIRM_CUSTOMER_DATA_IN_CHECKOUT_PAGARME' => Configuration::get('PAGARME_CONFIRM_CUSTOMER_DATA_IN_CHECKOUT_PAGARME'),
            'PAGARME_INSTALLMENT' => Configuration::get('PAGARME_INSTALLMENT'),
            'PAGARME_INSTALLMENT_MIN_VALUE' => Configuration::get('PAGARME_INSTALLMENT_MIN_VALUE'),
            'PAGARME_INSTALLMENT_MAX_NUMBER' => Configuration::get('PAGARME_INSTALLMENT_MAX_NUMBER'),
            'PAGARME_BOLETO_DELAY' => Configuration::get('PAGARME_BOLETO_DELAY'),
            'PAGARME_SOFT_DESCRIPTOR' => Configuration::get('PAGARME_SOFT_DESCRIPTOR'),
            'PAGARME_INSTALLMENT_TAX_FREE' => Configuration::get('PAGARME_INSTALLMENT_TAX_FREE'),
            'PAGARME_INSTALLMENT_TAX' => Configuration::get('PAGARME_INSTALLMENT_TAX'),
            'PAGARME_ACTIVATE_LOG' => Configuration::get('PAGARME_ACTIVATE_LOG'),
            'PAGARME_DISCOUNT_BOLETO' => Configuration::get('PAGARME_DISCOUNT_BOLETO'),
            'PAGARME_EXPIRATION_COMBO' => Configuration::get('PAGARME_EXPIRATION_COMBO'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     **/
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        //		if ((bool)Configuration::get('PAGARME_LIVE_MODE') == false)
        //			return;

        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false) {
            return false;
        }

        $encryption_key = Configuration::get('PAGARME_ENCRYPTION_KEY');
        $api_key = Configuration::get('PAGARME_API_KEY');

        if (empty($encryption_key) || empty($api_key)) {
            return false;
        }

        $this->smarty->assign('module_dir', $this->_path);

        $integrationMode = Configuration::get('PAGARME_INTEGRATION_MODE');
        $payWay = Configuration::get('PAGARME_PAY_WAY');

        $return = '';
        if ($integrationMode == 'checkout_transparente') {
            $cart = Context::getContext()->cart;

            $confirm_customer_data = Configuration::get('PAGARME_CONFIRM_CUSTOMER_DATA_IN_CHECKOUT_PAGARME') == 1 ? "true" : "false";

            $total_order = $cart->getOrderTotal();
            $customer = new Customer((int)$cart->id_customer);
            $address = new Address((int)$cart->id_address_invoice);
            $state = new State((int)$address->id_state);

            $phone = !empty($address->phone_mobile)?$address->phone_mobile:$address->phone;
            $ddd = '';

            if (!empty($phone) && Tools::strlen($phone) > 2) {
                if (strrpos($phone, '(') !== false && strrpos($phone, ')') !== false && strrpos($phone, '(') < strrpos($phone, ')')) {
                    preg_match('#\((.*?)\)#', $phone, $match);
                    $ddd = $match[1];
                    $phone = trim(Tools::substr($phone, strrpos($phone, ')')+1));
                } else {
                    $ddd = Tools::substr($phone, 0, 2);
                    $phone = Tools::substr($phone, 2, Tools::strlen($phone));
                }
            }

            $max_installments = 1;
            if ((bool)Configuration::get('PAGARME_INSTALLMENT') == true) {
                $max_installments = Pagarmeps::getInstallmentMaxi($total_order);
            }

            $interest_rate = '';
            $conf_val = Configuration::get('PAGARME_INSTALLMENT_TAX');
            if (!empty($conf_val)) {
                $interest_rate = Configuration::get('PAGARME_INSTALLMENT_TAX');
            }

            $free_installments = '';
            $conf_val = Configuration::get('PAGARME_INSTALLMENT_TAX_FREE');
            if (!empty($conf_val)) {
                $free_installments = Configuration::get('PAGARME_INSTALLMENT_TAX_FREE');
                if ($free_installments == 'all') { // Sem juros nenhum
                    $free_installments = $max_installments;
                } elseif ($free_installments == 'none') { // todas as parcelas com juros
                    $free_installments = 1;
                }
            }

            $addressNumber = explode(',', $address->address1);

            if (isset($addressNumber[1])) {
                if (isset($addressNumber[2])) {
                    $addressComplement = $addressNumber[2];
                } else {
                    $addressComplement = null;
                }

                $addressNumber = $addressNumber[1];
            }

            $this->context->smarty->assign(array(
                'cart_id' => $cart->id,
                'total_order' => $total_order,
                'encryption_key' => $encryption_key,
                'pay_way' => $payWay,
                'integration_mode' => $integrationMode,
                'secure_key' => Context::getContext()->customer->secure_key,
                'confirm_customer_data' => $confirm_customer_data,
                'customer_name' => $customer->firstname.' '.$customer->lastname,
                'customer_email' => $customer->email,
                'address_street' => $address->address1,
                'address_street_number' => filter_var($addressNumber, FILTER_SANITIZE_NUMBER_INT),
                'address_complementary' => ($address->other) ? $address->other : $addressComplement,
                'address_neighborhood' => $address->address2,
                'address_city' => $address->city,
                'address_state' => $state->name,
                'address_zipcode' => $address->postcode,
                'phone_ddd' => $ddd,
                'phone_number' => $phone,
                'customer_document_number' => Pagarmeps::getCustomerCPFouCNPJ($address, (int)$cart->id_customer),
                'max_installments' => $max_installments,
                'interest_rate' => $interest_rate,
                'free_installments' => $free_installments,
                'boleto_discount_amount' => $this->calculateBoletoDiscount()
            ));
            $this->smarty->assign('pay_way', $payWay);

            $return = $this->display(__FILE__, 'views/templates/hook/payment-transparent.tpl');
        } elseif ($integrationMode == 'gateway' && $payWay == 'credit_card') {
            $return = $this->display(__FILE__, 'views/templates/hook/payment-card.tpl');
        } elseif ($integrationMode == 'gateway' && $payWay == 'boleto') {
            $return = $this->display(__FILE__, 'views/templates/hook/payment-boleto.tpl');
        } elseif ($integrationMode == 'gateway' && $payWay == 'both') {
            $return = $this->display(__FILE__, 'views/templates/hook/payment-card.tpl');
            $return = $return.$this->display(__FILE__, 'views/templates/hook/payment-boleto.tpl');
        }

//		//One Click Buy
//		if ((bool)Configuration::get('PAGARME_ONE_CLICK_BUY') == true) {
//			$cart = Context::getContext()->cart;
//			if(PagarmepsCardClass::hasRegisteredCard((int)$cart->id_customer)){
//				$return = $return.$this->display(__FILE__, 'views/templates/hook/payment-oneclick.tpl');
//			}
//		}

        return $return;
    }

    protected function calculateBoletoDiscount()
    {
        $taxCalculationMethod = Group::getPriceDisplayMethod((int)Group::getCurrent()->id);
        $useTax = !($taxCalculationMethod == PS_TAX_EXC);

        $cart = $this->context->cart;
        $shippingAmount = $cart->getOrderTotal($useTax, Cart::ONLY_SHIPPING, null, $cart->id_carrier, false);

        $totalAmount = $cart->getOrderTotal();
        $totalAmountFreeShipping = $totalAmount - $shippingAmount;

        $discountAmount = $this->calculatePorcetage(Configuration::get('PAGARME_DISCOUNT_BOLETO'), $totalAmountFreeShipping);

        return number_format($discountAmount, '2', '', '');
    }

    private function calculatePorcetage($porcentagem, $total)
    {
        return ($porcentagem / 100) * $total;
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ((bool)Configuration::get('PAGARME_LIVE_MODE') == false) {
            return;
        }

        $order = $params['objOrder'];
        $transactionId = PagarmepsTransactionClass::getTransactionIdByOrderId($order->id);

        $api_key = Configuration::get('PAGARME_API_KEY');
        Pagarme::setApiKey($api_key);
        $transaction = PagarMe_Transaction::findById($transactionId);

        $boleto_url = $transaction->boleto_url; // URL do boleto bancário
        $boleto_barcode = $transaction->boleto_barcode; // código de barras do boleto bancário

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'boleto_url' => $boleto_url,
            'boleto_barcode' => $boleto_barcode,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    public function hookActionPaymentCCAdd()
    {
        /* Place your code here. */
    }

    public function hookActionPaymentConfirmation()
    {
        /* Place your code here. */
    }

    public function hookDisplayHeader()
    {
        $this->hookHeader();
    }

    public function hookDisplayPayment($params)
    {
        return $this->hookPayment($params);
    }

    public function hookDisplayPaymentReturn($params)
    {
        return $this->hookPaymentReturn($params);
    }

    public function hookDisplayPaymentTop()
    {
    }


    public function hookProductActions($params)
    {
        /*$cookie = $params['cookie'];

        $this->smarty->assign(array(
            'id_product' => (int)Tools::getValue('id_product'),
        ));

        if (isset($cookie->id_customer))
            $this->smarty->assign(array(
                'wishlists' => WishList::getByIdCustomer($cookie->id_customer),
            ));

        return ($this->display(__FILE__, 'blockwishlist-extra.tpl'));*/
    }

    public function hookCustomerAccount($params)
    {
        if ((bool)Configuration::get('PAGARME_ONE_CLICK_BUY') == true) {
            if (PagarmepsCardClass::hasRegisteredCard((int)$params['cookie']->id_customer)) {
                return $this->display(__FILE__, 'views/templates/hook/my-account.tpl');
            }
        }
    }

    public function hookDisplayMyAccountBlock($params)
    {
        return $this->hookCustomerAccount($params);
    }


    public function hookShoppingCartExtra($params)
    {
        if ((bool)Configuration::get('PAGARME_ONE_CLICK_BUY') == true) {
            if (PagarmepsCardClass::hasRegisteredCard((int)$params['cookie']->id_customer)) {
                $this->smarty->assign(array(
                    'id_customer' => (int)$params['cookie']->id_customer,
                    'zone' => 'cart-extra',
                ));

                return $this->display(__FILE__, 'views/templates/hook/payment-oneclick.tpl');
            }
        }
    }

    public function hookDisplayShoppingCart($params)
    {
        return $this->hookShoppingCartExtra($params);
    }


    public function getPath()
    {
        return $this->_path;
    }

    public static function getStatusId($state)
    {
        $idStatus = Configuration::get('PAGARME_DEFAULT_STATUS');
        //processing, waiting_payment, authorized, paid, pending_refund, refunded, refused

        if ($state == 'processing') {
            $idStatus = Configuration::get('PAGARME_DEFAULT_PROCESSING');
        } elseif ($state == 'waiting_payment') {
            $idStatus = Configuration::get('PAGARME_DEFAULT_WAITING_PAYMENT');
        } elseif ($state == 'authorized') {
            $idStatus = Configuration::get('PAGARME_DEFAULT_AUTHORIZED');
        } elseif ($state == 'paid') {
            $idStatus = Configuration::get('PAGARME_DEFAULT_PAID');
        } elseif ($state == 'pending_refund') {
            $idStatus = Configuration::get('PAGARME_DEFAULT_PENDING_REFUND');
        } elseif ($state == 'refunded') {
            $idStatus = Configuration::get('PAGARME_DEFAULT_REFUNDED');
        } elseif ($state == 'refused') {
            $idStatus = Configuration::get('PAGARME_DEFAULT_REFUSED');
        }
        return $idStatus;
    }

    public static function getInstallmentOptions($amount)
    {
        $installmentsReturn = null;

        //If installment option is activated
        if ((bool)Configuration::get('PAGARME_INSTALLMENT') == true) {
            $interest_rate = 0;
            $conf_val = Configuration::get('PAGARME_INSTALLMENT_TAX');
            if (!empty($conf_val)) {
                $interest_rate = Configuration::get('PAGARME_INSTALLMENT_TAX');
            }

            $max_installments = Configuration::get('PAGARME_INSTALLMENT_MAX_NUMBER');

            $free_installments = 1;
            $conf_val = Configuration::get('PAGARME_INSTALLMENT_TAX_FREE');
            if (!empty($conf_val)) {
                $free_installments = Configuration::get('PAGARME_INSTALLMENT_TAX_FREE');
                if ($free_installments == 'all') { // Sem juros nenhum
                    $free_installments = $max_installments;
                } elseif ($free_installments == 'none') { // todas as parcelas com juros
                    $free_installments = 1;
                }
            }

            $api_key = Configuration::get('PAGARME_API_KEY');
            Pagarme::setApiKey($api_key);
            $installments = PagarMe_Transaction::calculateInstallmentsAmount($amount*100, $interest_rate, $max_installments, $free_installments);
            $installmentsReturn = array();

            $installment_min_value = Configuration::get('PAGARME_INSTALLMENT_MIN_VALUE');
            foreach ($installments['installments'] as $key => $installment) {
                if ($installment['installment_amount']/100 >= $installment_min_value) {
                    $installmentsReturn[$key] = $installment;
                }
            }
        }

        return $installmentsReturn;
    }

    public static function getInstallmentMaxi($value)
    {
        $options = Pagarmeps::getInstallmentOptions($value);
        return count($options);
    }

    public static function addLog($message, $severity = 1, $error_code = null, $object_type = null, $object_id = null, $allow_duplicate = false, $id_employee = null)
    {
        if ((bool)Configuration::get('PAGARME_ACTIVATE_LOG') == true) {
            PrestaShopLogger::addLog($message.' TS='.microtime(), $severity, $error_code, $object_type, $object_id, $allow_duplicate, $id_employee);
        }
    }

    /*
    * There si no standar in PS for CPF or CNPJ. Many addons are implementing it in their own way
    * So this function have to be adapted for each kind of implementation, and will be used internaly to get CPF / CNPJ information
    */
    public static function getCustomerCPFouCNPJ($address, $id_customer)
    {
        try {
            if (isset($address->cpf_cnpj)) {
                if (count(str_replace(array('-','.'), '', $address->cpf_cnpj)) === 11) {
                    return  $address->cpf_cnpj;
                }

                return $address->cpf_cnpj;
            }

            if (file_exists('../djtalbrazilianregister/djtalbrazilianregister.php')) {
                include_once('../djtalbrazilianregister/djtalbrazilianregister.php');
            }


            if (method_exists('BrazilianRegister', 'getByCustomerId') && method_exists('Djtalbrazilianregister', 'mascaraString')) {
                $documentNumber = BrazilianRegister::getByCustomerId($id_customer);

                if ($documentNumber['cpf']) {
                    return $documentNumber['cpf'];
                }

                return $documentNumber['cnpj'];
            } else {
                return '';
            }
        } catch (Exception $e) {
            return null;
        }
    }

    public static function mask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i<=strlen($mask)-1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }


    public function hookDisplayOrderDetail($params)
    {
        $order = $params['order'];
        $boletoUrl = null;
        $order_status = new OrderState($order->getCurrentState(), (int)$order->id_lang);

        if ($order->module == 'pagarmeps' && $order_status->paid == 0) {
            $api_key = Configuration::get('PAGARME_API_KEY');
            Pagarme::setApiKey($api_key);
            $transactionId = PagarmepsTransactionClass::getTransactionIdByOrderId($order->id);
            $transaction = PagarMe_Transaction::findById($transactionId);

            if ($transaction->payment_method == 'boleto') {
                $boletoUrl = $transaction->boleto_url;
            }
        }

        $this->context->smarty->assign(
            array(
                'boleto_url' => $boletoUrl,
            )
        );

        return $return.$this->display(__FILE__, 'views/templates/hook/boleto_detail.tpl');
    }
}
