# Prestashop Extension for [Pagar.me](https://pagar.me) Payment Gateway (Prestashop)

### Instalação:

- Execute o comando git clone git@github.com:pagarme/pagarme-prestashop.git pagarmeps
- Copie a pasta *pagarmeps* para dentro da pasta *modules* em sua instalação PrestaShop;
- Certifique-se de que as permissões das pastas e arquivos recém copiados sejam, respectivamente, definidas como 755 e 644;
- Acesse a categoria Payments & Gateways, localize o módulo Pagar.me e faça a instalação

## Configuração

* Configure o modulo em ```Módulos > Módulos e Serviços > Pagarmeps > Pagar.me - Configuração``` e informe a ```Chave de API``` e a ```Chave de criptografia```, obtidos a partir da sua conta no [Pagar.me](https://pagar.me)
* No campo Integração selecione o tipo de integração que você deseja, se é checkout Transparente Pagar.me ou Gateway Simples
* ```Meio de Pagarmento```, aqui selecione o tipo de pagamento (Boleto,Cartão de Crédito ou Cartão de Crédito e Boleto).
* ```One Click Buy```, aqui você pode ativar ou desativar a opção de compra com um click.
* ```Autorizar parcelamento```, aqui você pode habilitar ou não parcelamento nas compras feitas com cartão de crédito.
* ```parcelamento - Taxa de juros```, aqui você pode selecionar a taxa de juros cobrada sobre compras parceladas.
* ```Parcelas sem juros```, aqui você pode configurar o número de parcelas sem juros no cartão de crédito.
* ```Boleto - Prazo```, aqui você pode configurar o prazo para pagamento do boleto.
* ```Descrição da fatura```, aqui você pode configurar uma descrição na fatura.
* ```Activate logs```, aqui você pode habilitar ou desabilitar os log
