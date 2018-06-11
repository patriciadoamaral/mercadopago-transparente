# MP Checkout Transparente

Este é um exemplo de integração com o Mercado Pago utilizando Checkout Transparente demonstrando na prática como consumir as APIs disponibilizadas pelo Mercado Pago.

## Linguagens e plugins utilizados
- PHP 5.6, Html, Jquery 1.11.1
- Bootstrap 4.1
- Jquery Validate 1.17.0

## Obter chave (Public key e Access token)
https://www.mercadopago.com/mlb/account/credentials

Obs.: Pelo menos até a data atual, os testes devem ser feitos com a chave de produção, considerando que o Mercado Pago não disponibiliza um painel sandbox no momento.

## Composer 1.6.5
https://getcomposer.org

Após a instalação, para fazer o download do pacote do Mercado Pago via terminal:
```sh
composer require mercadopago/sdk:0.5.2
```

