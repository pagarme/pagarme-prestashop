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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'pagarme_transaction` (
    `id_pagarme_transaction` int(11) NOT NULL AUTO_INCREMENT,
    `id_order` int(11) NOT NULL,
    `id_object_pagarme` varchar(256) NOT NULL,
    `current_status` varchar(256) DEFAULT NULL,
    PRIMARY KEY  (`id_pagarme_transaction`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'pagarme_card` (
    `id_pagarme_card` int(11) NOT NULL AUTO_INCREMENT,
    `id_client` int(11) NOT NULL,
    `id_object_card_pagarme` varchar(256) NOT NULL,
    PRIMARY KEY  (`id_pagarme_card`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query)
	if (Db::getInstance()->execute($query) == false)
		return false;
