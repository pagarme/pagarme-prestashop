const pagarme = require('pagarme')
const Nightmare = require('nightmare')

exports.simulate_checkout = function(nightmare_process, amount, payment_method, encryption_key){
  pagarme.client
    .connect({encryption_key: encryption_key})
    .then(client => {
      return client.transactions.create({
        amount: Number(amount),
        payment_method: payment_method,
        card_holder_name: 'qwe',
        card_expiration_date: '1221',
        card_cvv: '122',
        card_number: '4242424242424242',
        customer: {
            email: 'qwe@qwe.com',
            document_number: '040.036.762-97',
            name: 'qwe',
            address: {
                street: 'rua',
                street_number: '123',
                neighborhood: 'bairro',
                zipcode: '64216772'
            },
            phone: {
                ddd: '11',
                number: '987654321'
            }
        }
      })
    })
    .then(transaction => {
        nightmare_process
            .insert('#pagarme_payment_transparent_auto #token', transaction.token)
            .insert('#pagarme_payment_transparent_auto #payment_method', transaction.payment_method)
            .evaluate(() => {
                document.getElementById('pagarme_payment_transparent_auto').submit()
            })
    })
    .catch((e) => {
        console.log(e.response.errors)
    })
}

exports.select_carrier = function(nightmare_process){
  nightmare_process
    .wait('input[id*=delivery_option]')
    .click('input[id*=delivery_option]:first-of-type')
}

exports.fill_customer_form = function(nightmare_process){
  return nightmare_process
    .wait('button#opc_guestCheckout').click('button#opc_guestCheckout')
    .wait('#new_account_form')
    .select('select#days', '1')
    .select('select#months', '6')
    .select('select#years', '1990')
    .insert('#email', new Date().getTime() + '@qwe.com')
    .insert('#customer_firstname', 'qwe')
    .insert('#customer_lastname', 'qwe')
    .insert('#company', 'qwe')
    .insert('#vat_number', '35965816804')
    .insert('#address1', 'qwe')
    .insert('#postcode', '06350270')
    .insert('#city', 'Cidade')
    .insert('#phone_mobile', '11987654321')
    .select('#id_country', '58')//brazil
    .select('#id_state', '337')//são paulo
    .click('button#submitGuestAccount')
}

exports.add_product_to_basket = function(nightmare_process){
  return nightmare_process
    .wait('a[data-id-product]')
    .click('a[data-id-product]:first-of-type')
    .wait('.layer_cart_overlay[style*="display: block;"]')
    .click('a[title="Proceed to checkout"]')
}

exports.new_nightmare_instance = function(){
  return nightmare = new Nightmare({
      show: true,
      waitTimeout: 10000
    })
}