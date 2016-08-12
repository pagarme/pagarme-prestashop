<?php

class OrderPayment extends OrderPaymentCore
{
    public $order_reference;
    public $id_currency;
    public $amount;
    public $payment_method;
    public $conversion_rate;
    public $transaction_id;
    public $card_number;
    public $card_brand;
    public $card_expiration;
    public $card_holder;
    public $date_add;
    public $installments;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'order_payment',
        'primary' => 'id_order_payment',
        'fields' => array(
            'order_reference' =>     array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 9),
            'id_currency' =>         array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'amount' =>              array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'required' => true),
            'payment_method' =>      array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'conversion_rate' =>     array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'transaction_id' =>      array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 254),
            'card_number' =>         array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 254),
            'card_brand' =>          array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 254),
            'card_expiration' =>     array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 254),
            'card_holder' =>         array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 254),
            'date_add' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'installments' =>        array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 254),
        )
    );
}