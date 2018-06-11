<?php include("includes/head.php"); ?>

<style>
.container {
    max-width: 900px;
}
.error{
    color: #c90000
}
</style>

<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>

<script>

    Mercadopago.setPublishableKey("SUA-CHAVE");

    $(document).ready(function () {

        $("#form1").validate({

            rules: {
                email: {required: true},
                cartao_numero: {required: true},
                cartao_mes: {required: true},
                cartao_ano: {required: true},
                cartao_codigo: {required: true},
                parcelas_quantidade: {required: true}
            },

            messages: {
                email: "Digite seu e-mail.",
                cartao_numero: "Digite o número do cartão de crédito.",
                cartao_mes: "Digite o mês de validade do cartão.",
                cartao_ano: "Digite o ano de validade do cartão.",
                cartao_codigo: "Digite o código de segurança do cartão.",
                parcelas_quantidade: "Escolha uma forma de parcelamento."
            },

            submitHandler: function (form) {

                //Get Token
                var form = document.querySelector('#form1'); //Get form
                Mercadopago.createToken(form, tokenHandler);
            }
        });

        //Mostra Bandeira + parcelas
        if( $("#form1 #cartao_numero") != ""){

            Mercadopago.getPaymentMethod({
                "bin": getBin()
            }, setPaymentMethodInfo);
        }

        //Atualiza Valor total ao mudar qtd de parcelas
        $("#form1 select[name=parcelas_quantidade]").on('change', function () {

            var retorno = this.value.split("-");

            var v = retorno[1].toString();

            $(".total").val(v);
        });

        function addEvent(el, eventName, handler) {
            console.log("addEvent");
            if (el.addEventListener) {
                el.addEventListener(eventName, handler);
            } else {
                el.attachEvent('on' + eventName, function () {
                    handler.call(el);
                });
            }
        };

        function tokenHandler(status, response) {
            if (status != 200 && status != 201) {
                console.log("verify filled data");
            } else {
                //console.log("tokenHandler");
                var form = document.querySelector('#form1');
                //Cria um input
                var card = document.createElement('input');
                card.setAttribute('name', "token");
                card.setAttribute('type', "hidden");
                card.setAttribute('value', response.id);
                form.appendChild(card);
                doSubmit = true;
                form.submit();
            }
        };

        function getBin() {
            //Número do cartão formatado
            var ccNumber = document.querySelector('#form1 input[data-checkout="cardNumber"]');
            return ccNumber.value.replace(/[ .-]/g, '').slice(0, 6);
        };

        function guessingPaymentMethod(event) {
            var bin = getBin();

            if (event.type == "keyup") {
                if (bin.length >= 6) {
                    Mercadopago.getPaymentMethod({
                        "bin": bin
                    }, setPaymentMethodInfo);
                }
            } else {
                setTimeout(function () {
                    if (bin.length >= 6) {
                        Mercadopago.getPaymentMethod({
                            "bin": bin
                        }, setPaymentMethodInfo);
                    }
                }, 100);
            }
        };

        function setPaymentMethodInfo(status, response) {
            if (status == 200) {
                //EXECUTA ALGUMAS OPERAÇÕES ESSENCIAIS PARA O PAGAMENTO COMO DETERMINAR OS DETALHES DO MEIO DE PAGAMENTO SELECIONADO COMO POR EXEMPLO BANDEIRA DO CARTÃO DE CRÉDITO
                var form = document.querySelector('#form1');

                if (document.querySelector("input[name=paymentMethodId]") == null) {
                    //cria um hidden
                    var paymentMethod = document.createElement('input');
                    paymentMethod.setAttribute('name', "paymentMethodId");
                    paymentMethod.setAttribute('type', "hidden");
                    paymentMethod.setAttribute('value', response[0].id);
                    form.appendChild(paymentMethod);
                } else {
                    document.querySelector("input[name=paymentMethodId]").value = response[0].id;
                }

                var img = "<img src='" + response[0].thumbnail + "'>";
                $("#form1 #img_bandeira").empty();
                $("#form1 #img_bandeira").append(img);

                amount = $("#form1 .total").val();

                Mercadopago.getInstallments({
                    "bin": getBin(),
                    "amount": amount
                }, setInstallmentInfo);
            }
        };

        function setInstallmentInfo(status, response) {

            var selectorInstallments = document.querySelector("#form1 #parcelamento"),
                fragment = document.createDocumentFragment();
            selectorInstallments.options.length = 0;

            if (response.length > 0) {
                var option = new Option("Escolha...", ''),
                    payerCosts = response[0].payer_costs;
                fragment.appendChild(option);

                for (var i = 0; i < payerCosts.length; i++) {
                    option = new Option(payerCosts[i].recommended_message || payerCosts[i].installments, payerCosts[i].installments + '-' + payerCosts[i].total_amount);
                    fragment.appendChild(option);
                }

                selectorInstallments.appendChild(fragment);
                selectorInstallments.removeAttribute('disabled');
            }
        };

        addEvent(document.querySelector('#form1 input[data-checkout="cardNumber"]'), 'keyup', guessingPaymentMethod);
        addEvent(document.querySelector('#form1 input[data-checkout="cardNumber"]'), 'change', guessingPaymentMethod);

    });

</script>

<div class="container">

    <h2 class="text-center mt-3 mb-5">Exemplo Pagto com Cartão de Crédito</h2>

    <table class="table table-responsive">
        <thead class="thead-light">
        <tr>
            <th>Nome</th>
            <th>Preço</th>
            <th>Qtde</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Produto Teste</td>
            <td>10.50</td>
            <td>1</td>
        </tr>
        </tbody>
    </table>

    <hr>

    <div class="row">
        <div class="col">

            <form id="form1" action="autoriza_cartao.php" method="POST">

                <h4>Dados Comprador</h4>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">Nome:</label>
                    <input type="text" name="nome" class="form-control form-control-sm col-sm-3" value="Teste">

                    <label class="col-form-label col-sm-2">Sobrenome:</label>
                    <input type="text" name="sobrenome" class="form-control form-control-sm col-sm-3" value="Teste">
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">E-mail:</label>
                    <input type="text" name="email" id="email" class="form-control form-control-sm col-sm-3" value="teste@teste.com">

                    <label class="col-form-label col-sm-2">Telefone:</label>
                    <input type="text" name="ddd" class="form-control form-control-sm col-sm-1" value="11">
                    <input type="text" name="telefone" class="form-control form-control-sm col-sm-2" value="00000000">
                </div>

                <h4>Dados do cartão:</h4>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">Número do cartão:</label>
                    <input type="text" name="cartao_numero" id="cartao_numero" class="form-control form-control-sm col-sm-4 cartao" data-checkout="cardNumber" autocomplete=off value="5031433215406351">
                    <div id="img_bandeira" class="col"></div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">Validade Mês:</label>
                    <select name="cartao_mes" id="cartao_mes" data-checkout="cardExpirationMonth" class="form-control form-control-sm col-sm-1">
                        <option value="">Selecione...</option>
                        <?php for ($i = 1; $i <= 12; $i++) {
                            echo "<option value='" . $i . "' " . (($i == 12) ? "selected" : "") . ">{$i}</option>";
                        }
                        ?>
                    </select>

                    <label class="col-form-label col-md-1">Ano:</label>
                    <select name="cartao_ano" id="cartao_ano" data-checkout="cardExpirationYear" class="form-control form-control-sm col-sm-2">
                        <option value="">Selecione...</option>
                        <?php for ($i = date("Y"); $i <= date("Y") + 15; $i++) {
                            echo "<option value='" . $i . "' " . (($i == 2030) ? "selected" : "") . ">{$i}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">Cód. de Segurança:</label>
                    <input type="text" name="cartao_codigo" id="cartao_codigo" data-checkout="securityCode" value="123" class="form-control form-control-sm col-sm-1" maxlength="3">
                </div>

                <div class="form-group row">
                    <label class='col-form-label col-md-4' for="parcelamento">Parcelas:</label>
                    <select id="parcelamento" name='parcelas_quantidade' class='form-control form-control-sm col-md-4'></select>
                </div>

                <h4>Titular do cartão:</h4>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">Nome:</label>
                    <input type="text" name="titular_nome" data-checkout="cardholderName" class="form-control form-control-sm col-md-4" value="APRO">
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-4">CPF:</label>
                    <input type="text" name="titular_doc" data-checkout="docNumber" class="form-control form-control-sm col-md-4 cpf" value="19119119100">
                </div>

                <!--HIDDENS-->
                <input type="hidden" name="titular_doc_tipo" data-checkout="docType" value="CPF"/>
                <input type="hidden" data-checkout="siteId" value="MLB"/>
                <input type="hidden" name="paymentMethod" value="creditcard">
                <input type="hidden" name="id" value="1">
                <input type="hidden" name="nome" value="Produto Teste">
                <input type="hidden" name="qtde" value="1">
                <input type="hidden" name="total" class="total" value="10.50">
                <hr>

                <div class="form-group row">
                    <label class="col-form-label col-md-1">Total:</label>
                    <input type="text" name="total" class="form-control form-control-sm col-md-2 total" value="10.50" disabled>
                </div>
                <div class="form-group row float-right">
                    <button type="submit" class="btn btn-success ">Pagar com Cartão</button>
                </div>
            </form>

        </div>
    </div>
</div>