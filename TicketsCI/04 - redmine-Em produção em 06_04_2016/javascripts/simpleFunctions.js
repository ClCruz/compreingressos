$(document).ready(function () {
    simples.init();
});

function hasNewLine() {
    if ($('#newLine').length == 0) {
        return true;
    } else {
        $.dialog({
            title: 'Atenção...',
            text: 'Já existe uma linha em edição!<br><br>Favor salvá-la antes de continuar.'
        });
        return false;
    }
}

function setDatePickers() {
    $('input.datePicker').datepicker({
        minDate: +0,
        changeMonth: true,
        changeYear: true
    });
    $('input.datePicker').datepicker('option', $.datepicker.regional['pt-BR']);
}

function verificaCPF(cpf) {
    if(isNaN(cpf) == true) {
        return false;
    } else {
        if((cpf == '11111111111') || (cpf == '22222222222') ||
            (cpf == '33333333333') || (cpf == '44444444444') ||
            (cpf == '55555555555') || (cpf == '66666666666') ||
            (cpf == '77777777777') || (cpf == '88888888888') ||
            (cpf == '99999999999') || (cpf == '00000000000')) {
            return false;
        } else {
            //PEGA O DIGITO VERIFIACADOR
            var dv_informado = cpf.substr(9, 2);
            var digito = [];
            for(var i=0; i <= 8; i++) {
                digito[i] = cpf.substr(i, 1);
            }

            //CALCULA O VALOR DO 10º DIGITO DE VERIFICAÇÂO
            var posicao = 10;
            var soma = 0;

            for(i = 0; i <= 8; i++) {
                soma += digito[i] * posicao;
                posicao--;
            }

            digito[9] = soma % 11;

            if (digito[9] < 2) {
                digito[9] = 0;
            } else {
                digito[9] = 11 - digito[9];
            }

            //CALCULA O VALOR DO 11º DIGITO DE VERIFICAÇÃO
            posicao = 11;
            soma = 0;

            for (i = 0; i <= 9; i++) {
                soma += digito[i] * posicao;
                posicao--;
            }

            digito[10] = soma % 11;

            if (digito[10] < 2) {
                digito[10] = 0;
            } else {
                digito[10] = 11 - digito[10];
            }

            //VERIFICA SE O DV CALCULADO É IGUAL AO INFORMADO
            var dv = digito[9] * 10 + digito[10];

            if (dv != dv_informado) {
                return false;
            } else {
                return true;
            }
        }
    }
}

function verificaCNPJ(str){
    str = str.replace('.','');
    str = str.replace('.','');
    str = str.replace('.','');
    str = str.replace('-','');
    str = str.replace('/','');
    cnpj = str;
    var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
    digitos_iguais = 1;
    if (cnpj.length < 14 && cnpj.length < 15)
        return false;
    for (i = 0; i < cnpj.length - 1; i++)
        if (cnpj.charAt(i) != cnpj.charAt(i + 1))
        {
            digitos_iguais = 0;
            break;
        }
    if (!digitos_iguais)
    {
        tamanho = cnpj.length - 2
        numeros = cnpj.substring(0,tamanho);
        digitos = cnpj.substring(tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (i = tamanho; i >= 1; i--)
        {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2)
                pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0))
            return false;
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0,tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (i = tamanho; i >= 1; i--)
        {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2)
                pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(1))
            return false;
        return true;
    }
    else
        return false;
}

var simples =
{
    urlParamns: null,

    init: function () {
        this.setVars();
        this.getUrlParamns();
    },

    setVars: function ()
    {

    },

    /*
     * Pega os parametros GET e envia para um objeto Javascript
     * */
    getUrlParamns: function()
    {
        var paramns = document.location.search;
        paramns = paramns.replace('?','&');
        paramns = paramns.split('&');
        var obj = {};
        var i =0;
        for(x in paramns)
        {
            var item = paramns[x];
            if (item != '')
            {
                item = item.split('=');
                eval("obj['"+item[0]+"'] = {}");
                eval("obj['"+item[0]+"'].value = '"+item[1]+"';");
                i++;
            }
        }
        simples.urlParamns = obj;
    },

    /*
     * Cria ou altera novos parametros GET que serão utilizados depois em goTo()
     * */
    setParamns: function(paramn, value, paramns)
    {
        paramns = ( typeof paramns == 'object' ) ? paramns : {};

        if (eval('simples.urlParamns.'+paramn))
        {
            eval('simples.urlParamns.'+paramn+'.value = "'+value+'"');
        }
        else
        {
            eval("simples.urlParamns['"+paramn+"'] = {}");
            eval("simples.urlParamns['"+paramn+"'].value = '"+value+"';");
        }

        if ( paramns.reload == true ) { simples.reloadWithParamns(); }
    },

    reloadWithParamns: function()
    {
        var i 	= 0;
        var str = '';
        for(x in simples.urlParamns)
        {
            str += ( i == 0 ) ? '?' : '&';
            str += x+'='+simples.urlParamns[x].value;
            i++;
        }

        document.location = document.location.origin + document.location.pathname + str;
    },

    /*
    * Função para selecionar página de navegação via SELECT
    * utilização em Paginator::__paginate()
    * */
    selectPage: function (e)
    {
        this.setParamns('page', e.value);
        this.reloadWithParamns();
    }

};
