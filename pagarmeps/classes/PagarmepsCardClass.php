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
class PagarmepsCardClass extends ObjectModel
{
	/** @var integer editorial id*/
	public $id;
	public $id_client;
	public $id_object_card_pagarme;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'pagarme_card',
		'primary' => 'id_pagarme_card',
		'multilang' => false,
		'fields' => array(
			'id_client' => array('type' => self::TYPE_INT, 'required' => true),
			'id_object_card_pagarme' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 256),
		)
	);
	
	public static function hasRegisteredCard($id_client){
		if (!$count = Db::getInstance()->getValue('SELECT count(`id_client`) FROM`'._DB_PREFIX_ .'pagarme_card` WHERE id_client='.(int)$id_client))
			return false;
		if($count > 0) {
			return true;
		}
		return false;
	}
	
	public static function deleteClientCard($card_id){
		Db::getInstance()->execute('DELETE FROM`'._DB_PREFIX_ .'pagarme_card` WHERE id_object_card_pagarme = \''.pSQL($card_id).'\'');
	}
	
	public static function getClientCard($id_client){
		$res = Db::getInstance()->executeS('SELECT * FROM`'._DB_PREFIX_ .'pagarme_card` WHERE id_client='.(int)$id_client);
		$cards = array();
		
		$api_key = Configuration::get('PAGARME_API_KEY');
		Pagarme::setApiKey($api_key);
		foreach ($res as $row) {
			try{
				$card = PagarMe_Card::findById($row['id_object_card_pagarme']);
				array_push($cards, $card);
			} catch (PagarMe_Exception $e) {
				//PagarmepsCardClass::deleteClientCard($row['id_object_card_pagarme']);
				$card = array (
					'object' => 'card',
					'id' => 'card_UNVALID',
					'date_created' => '2015-03-06T21:21:25.000Z',
					'date_updated' => '2015-03-06T21:21:26.000Z',
					'brand' => 'brand_UNVALID',
					'holder_name' => 'UNVALID',
					'first_digits' => 'UNVALID',
					'last_digits' => 'UNVALID',
					'fingerprint' => "UNVALID",
					'customer' => null,
					'valid' => false
				);
				array_push($cards, $card);
			}
		}
		return $cards;
	}
}