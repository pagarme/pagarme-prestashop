const assert = require('assert')
const helpers = require('./helpers.js')

let prestashop_url = 'http://localhost:8081'
let encryption_key = 'ek_test_7Yvi1oR5Yu2KsVMl6H9A1a2nZ63i7H'

describe('When purchasing something', function() {
  this.timeout('30s')
  let nightmare = null
  beforeEach(() => {
    nightmare = helpers.new_nightmare_instance()
    nightmare
      .goto(prestashop_url)
    helpers.add_product_to_basket(nightmare)
    helpers.fill_customer_form(nightmare)
    helpers.select_carrier(nightmare)
  })

  //"onepage checkout"
  //2 carriers
  //pagarme checkout
  // it('should load the pagarme button without error', done => {
  //   nightmare
  //     //change carrier
  //     .click('input[id*=delivery_option]:nth-of-type(1)')
  //     .wait('input#cgv').click('input#cgv')
  //     .wait('#HOOK_PAYMENT > div')
  //     .evaluate(() => {
  //       return document.querySelector('.pagarme-checkout-btn') !== null
  //     })
  //     .end()
  //     .then((pagarme_checkout_button_exists) => { 
  //       assert.ok(pagarme_checkout_button_exists)
  //       done()
  //     })
  //     .catch(done)
  // })
  
  //tax_free_installments = none
  it('should complete the transaction with no tax free installments', done => {
    nightmare
      .wait('input#cgv').click('input#cgv')
      .wait('.pagarme-checkout-btn').click('.pagarme-checkout-btn')
      .evaluate((helpers, encryption_key) => {
        let amount = document.getElementById('total_price').textContent.replace(/[^\d]/g, '')
        let payment_method = 'credit_card'
        return {
          amount: amount,
          payment_method: payment_method
        }
      }, helpers, encryption_key)
      .then((payment_options) => {
        helpers.simulate_checkout(nightmare, payment_options.amount, payment_options.payment_method, encryption_key)
      })

    nightmare
      .wait(5000)
      .end()
      // .then(() => {
      //   done()
      // })
      // .catch(done)
  })
  
})
