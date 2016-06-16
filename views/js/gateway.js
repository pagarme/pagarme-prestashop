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
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/
$(document).ready(function(){
	$('#pagarme_payment_form #card_number').mask('0000-0000-0000-0000');
	$('#pagarme_payment_form #card_expiration_month').mask('00');
	$('#pagarme_payment_form #card_expiration_year').mask('0000');
	$('#pagarme_payment_form #card_cvv').mask('000');
  
	PagarMe.encryption_key = encryption_key;

    var form = $('#pagarme_payment_form');

    form.submit(function(event) { // quando o form for enviado...
        // inicializa um objeto de cartão de crédito e completa
        // com os dados do form
		return validateForm();
    });
});

function validateForm() {
	var form = $('#pagarme_payment_form');
	var payement_way = null;
	if(pay_way == 'both') {
		payement_way = $('#pagarme_payment_form input[name=payment_way]').val();
	}
	
	//Only for credit card selection
	if(payement_way == 'card') {
		var creditCard = new PagarMe.creditCard();
		creditCard.cardHolderName = $('#pagarme_payment_form #card_holder_name').val();
		creditCard.cardExpirationMonth = $('#pagarme_payment_form #card_expiration_month').val();
		creditCard.cardExpirationYear = $('#pagarme_payment_form #card_expiration_year').val();
		var cardNumber = $('#pagarme_payment_form #card_number').val();
		if(cardNumber != ''){
			cardNumber = cardNumber.replace(/-/g, '');
			creditCard.cardNumber = cardNumber;
		}
		creditCard.cardCVV = $('#pagarme_payment_form #card_cvv').val();

		// pega os erros de validação nos campos do form
		var fieldErrors = creditCard.fieldErrors();

		//Verifica se há erros
		var hasErrors = false;
		for(var field in fieldErrors) { hasErrors = true; break; }

		$('#pagarme_payment_form input').removeClass('error');
		$('#pagarme_payment_form #field_errors').html('');
		$('#pagarme_payment_form #field_errors').hide();
		
		if(hasErrors) {
			// realiza o tratamento de errors
			var errorText = '';
			var count = 0;
			for(var key in fieldErrors) {
				count++;
				$('#pagarme_payment_form #'+key).addClass('error');
				errorText = errorText + fieldErrors[key] + '<br />';
			}
			if(count == 1) {
				errorText = '<strong> Um erro foi detectado: </strong> <br /><br />' + errorText;
			} else {
				errorText = '<strong> Varios erros foram detectados: </strong> <br /><br />' + errorText;
			}
			$('#pagarme_payment_form #field_errors').html(errorText);
			$('#pagarme_payment_form #field_errors').show();
			
		} else {
			// se não há erros, gera o card_hash...
			creditCard.generateHash(function(cardHash) {
				// ...coloca-o no form...
				form.append($('<input type="hidden" name="card_hash">').val(cardHash));
				// e envia o form
				form.get(0).submit();
			});
		}
	}
	
	//for Boleto payment
	if(payement_way == 'boleto') {
		form.get(0).submit();
	}
	return false;
}