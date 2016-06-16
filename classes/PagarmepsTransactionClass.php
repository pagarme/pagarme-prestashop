<?php
/**
* 2007-2014 PrestaShop
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

/**
 * PagarmepsCardClass Class Doc Comment
 *
 * @category Class
 * @package  PagarmepsCardClass
 * @author   Pagar.me
 * 
 */
class PagarmepsTransactionClass extends ObjectModel
{
	/** @var integer editorial id*/
	public $id;
	public $id_order;
	public $id_object_pagarme;
	public $current_status;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'pagarme_transaction',
		'primary' => 'id_pagarme_transaction',
		'multilang' => false,
		'fields' => array(
			'id_order' => array('type' => self::TYPE_INT, 'required' => true),
			'id_object_pagarme' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 256),
			'current_status' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 256),
		)
	);
	
	public static function getOrderIdByTransactionId ($id_object_pagarme) {
		$res = Db::getInstance()->executeS('SELECT id_order FROM `'._DB_PREFIX_ .'pagarme_transaction` WHERE id_object_pagarme = \''.pSQL($id_object_pagarme).'\'');
		return count($res)>0?$res[0]['id_order']:null;
	}
	
	public static function getByTransactionId ($id_object_pagarme) {
		$res = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_ .'pagarme_transaction` WHERE id_order = 0 AND id_object_pagarme = \''.pSQL($id_object_pagarme).'\'');
		$id_pagarme_transaction = count($res)>0?$res[0]['id_pagarme_transaction']:null;
		if($id_pagarme_transaction != null) {
			return new PagarmepsTransactionClass($id_pagarme_transaction);
		} else {
			return null;
		}
	}
	
	public static function getTransactionIdByOrderId ($id_order) {
		$res = Db::getInstance()->executeS('SELECT id_object_pagarme FROM `'._DB_PREFIX_ .'pagarme_transaction` WHERE id_order='.(int)$id_order);
		return count($res)>0?$res[0]['id_object_pagarme']:null;
	}
}

