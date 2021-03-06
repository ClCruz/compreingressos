$(function() {
    $('.menu_conta').on('click', 'a.botao', function(e) {
        var target_href = $(this).attr('href');

        if (target_href.substr(0, 1) == '#') {
            e.preventDefault();
        } else {
            return true;
        }

        $('#detalhes_pedido').hide();
        $('#detalhes_historico').hide();
        $('#meus_pedidos tbody tr').show();
        $('#assinaturas tbody tr').show();

        $('.menu_conta a').each(function() {
            var $this = $(this),
            this_href = $this.removeClass('ativo').attr('href');

            if (this_href != target_href) {
                $(this_href).hide();
            } else {
                $this.addClass('ativo');
                $(this_href).show();
            }
        });
    }).find('a:first').click();

    $('#meus_pedidos').on('click', 'a', function(event) {
        event.preventDefault();

        var $this = $(this),
        href = $this.attr('href').split('?');

        $.ajax({
            url: href[0],
            data: href[1],
            success: function(data) {
                $('#meus_pedidos tbody tr').hide();

                $this.closest('tr').show();

                $('#detalhes_pedido').html(data).show();
            }
        });
    });

    $('#assinaturas').on('click', 'a', function(event) {
        event.preventDefault();
        var $this = $(this), href = $this.attr('href').split('?');
        $.ajax({
            url: href[0],
            data: href[1],
            success: function(data) {
                $('#assinaturas tbody tr').hide();
                $this.closest('tr').show();
                $('#detalhes_historico').html(data).show();
            }
        });
    });


    $.ajax({
        url: 'atualizarAssinatura.php?action=load&local=139',
        success: function(data) {
            $('#assinaturas tbody').html(data).show();
            if($('#acao').length === 0){
                $('input[name*=pacote]').hide();
            }
        }
    });    

    $('#acao').change(function(event) {
        event.preventDefault();
        pacotes = new Array();
        cadeiras = new Array();
        var mensagem = "";
        var pacoteAux = null;
        $this = $(this);
        var dtInicio = $("input[type=hidden][name='dtInicio']").val();
        var dtFim = $("input[type=hidden][name='dtFim']").val();
        var qtdCheck = $("input[name*='pacote']:checked");
        var qtdSelecionado = qtdCheck.length;

        if ($(this).val() !== "-") {
            $this.next('div.sbHolder').removeClass('destaque');

            if (qtdSelecionado > 0) {
                // if ($(this).val() === "renovar") {                    
                //     for (i = 0; i < qtdSelecionado; i++) {
                //         if (pacoteAux === null) {
                //             pacoteAux = qtdCheck.eq(i).val();
                //         } else {
                //             if (pacoteAux != qtdCheck.eq(i).val()) {
                //                 $.dialog({
                //                     title: 'Aviso',
                //                     text: "Não é possível renovar Assinaturas diferentes no mesmo pedido, por favor, selecione apenas Assinaturas iguais para dar continuidade no processo de Renovação"
                //                 });
                //                 return false;
                //             } else {
                //                 pacoteAux = qtdCheck.eq(i).val();
                //             }
                //         }
                //     }
                //     mensagem = "";
                // }
                if ($(this).val() === "solicitarTroca") {
                    mensagem = "Você está solicitando a troca da(s) assinatura(s) selecionada(s). Você deverá finalizar a(s) troca(s), selecionando outro(s) lugar(es) ou confirmando o(s) lugar(es) atual(is), no período de " + dtInicio + " à " + dtFim;
                }
                if ($(this).val() === "cancelar") {
                    mensagem = "Ao solicitar o cancelamento da Assinatura, você estará disponibilizando seu lugar para que outras pessoas possam adquiri-lo. Deseja continuar?";
                }
                if ($(this).val() === "efetuarTroca") {
                    mensagem = "Você está iniciando o processo de troca dos seus lugares. Após a efetivação da troca de lugares, os seus lugares atuais, envolvidos na troca, não poderão ser mais utilizados por você. Continuar no processo de troca dos lugares?";
                }
                if ($(this).val() === "renovar") {
                    mensagem = "Você está solicitando a renovação da(s) assinatura(s) selecionada(s). Ao confirmar esta ação você está assegurando o(s) mesmo(s) lugar (es) para a próxima temporada. Efetuar a renovação?";
                }

                $.confirmDialog({
                    text: mensagem,
                    uiOptions: {
                        buttons: {
                            'Não': [" ", function() {
                                fecharOverlay();
                            }],
                            'Sim': [" ", function() {
                                $.ajax({
                                    url: 'atualizarAssinatura.php?action=' + $this.val(),
                                    data: $('#frmAssinatura').serialize(),
                                    type: 'post',
                                    success: function(data) {
                                        if ($this.val() === "solicitarTroca" || $this.val() === "cancelar") {
                                            $.ajax({
                                                url: 'atualizarAssinatura.php?action=load&local=139',
                                                success: function(data) {
                                                    $('#assinaturas tbody').html(data).show();
                                                }
                                            });
                                        }
                                        
                                        if(data.substring(0, 4) == 'redi'){
                                            document.location = data;
                                            return false;
                                        }

                                        if (data !== 'true') {
                                            tratarResposta(data, function(){
                                                $.dialog({
                                                    title: 'Aviso',
                                                    text: data
                                                });
                                                fecharOverlay();
                                            });
                                            
                                        } else {
                                            fecharOverlay();
                                        }                                        
                                    }
                                });
                            }]
                        }
                    }
                });
            } else {
                $.dialog({
                    title: 'Aviso',
                    text: "Selecione alguma assinatura para executar uma ação."
                });
            }
        } else {
            $this.next('div.sbHolder').addClass('destaque');
        }
    }).change();    

    if ($.getUrlVar('pedido') != undefined && $.getUrlVar('pedido') != '') {
        $('.menu_conta a[href*="#meus_pedidos"]').click();
        $('#meus_pedidos a[href*="detalhes_pedido.php?pedido=' + $.getUrlVar('pedido') + '"]').click();
    }

    if($.getUrlVar('assinaturas') != undefined){
        $('.menu_conta a[href*="#frmAssinatura"]').click();
    }
});