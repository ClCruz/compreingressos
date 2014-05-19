function posicionaCursor(obj){
  var obj = obj[0];
  if (obj.createTextRange) {
    var range = obj.createTextRange();
    range.collapse(true);
    //range.move('character', 0);
    range.moveEnd('character', 0);
    range.moveStart('character', 0);
    range.select();
  } else if (obj.selectionStart != null) {
    obj.focus();
    obj.setSelectionRange(0, 0);
  }
}

function setaInput(campo,mensagem,senha){
  campo.on('focus',function(){
    if(campo.val() == mensagem){
    if(senha){
    var newO=document.createElement('input');
    newO.setAttribute('type','password');
    newO.setAttribute('id',campo.attr('id'));
    newO.setAttribute('class',campo.attr('class'));
    campo[0].parentNode.replaceChild(newO,campo[0]);
    newO.focus();
    }
      campo.css('color','#AAA');
      posicionaCursor($(this));
    }
  }).on('keypress',function(){
    if(campo.val() == mensagem){
      campo.attr('value','');
    }
    campo.css('color','#4D4D4D');
  }).on('focusout',function(){
    if(campo.val() == ''){
    if(senha){
    var newO=document.createElement('input');
    newO.setAttribute('type','text');
    newO.setAttribute('id',obj.attr('id'));
    newO.setAttribute('class',obj.attr('class'));
    campo[0].parentNode.replaceChild(newO,campo[0]);
    }
      campo.attr('value',mensagem);
    }
    campo.css('color','#4D4D4D');
  });
  if (campo.val() == ''){
    campo.attr('value',mensagem);
  }
}

var sctop = 0;        
function overlay(fade){
  /* HACK para o scroll do overlay sobrepor o da pagina */
  $("body").css({
    "height":$(window).height()+'px',
    "overflow-y":'scroll'/*,
    "position":'fixed'*/
  });
  $('html').css('overflow','auto');
  
  // window.location.hash = "overlay";
  /* Seta a mascara negra */
  var maskHeight = $(window).height();
  var maskWidth = $(window).width();
  $('#overlay').css({
    'width':maskWidth,
    'height':maskHeight,
    'overflow-y':'scroll'
  });
  $("body").css({
    "height":$(window).height()+'px',
    "overflow-y":'scroll'
  });
  $("#pai").css('margin-top','-'+sctop+'px');
  
  if(fade==1){
    $('#overlay').fadeTo("slow",1.0);
  } else {
    $('#overlay').css('display','block');
  }
  $('#overlay iframe').css('display','block');
  
  // Desabilitar a rolagem via touchscreen para nao rolar a pagina de baixo quando o conteudo do overlay é menor que a tela.
  var overlayHeight = $('#overlay div.centraliza').height();
  //alert(maskHeight+'|'+overlayHeight);
  if(maskHeight > overlayHeight){
    document.ontouchmove = function(e){e.preventDefault();}
  }
}

/* ABRE OVERLAY */
function abreOverlay(id){
  $('div.alert').css('display','none');
  $('div#'+id).css('display','block');
  sctop = $(document).scrollTop();
  overlay(1);
}

/* FECHA OVERLAY */
function fecharOverlay(){
  $('#overlay iframe').css('display','none');
  $('#overlay').fadeTo("slow",0,function(){
    $('#overlay').css('display','none');
    $("#pai").css('margin-top','0px');
    $("body").css({
      "height":'0px',
      "overflow":'visible',
      "position":'static'
    });
    $('html').css('overflow','visible');
    // window.location.hash = '';
    $(document).scrollTop(sctop); // É necessário porque quando a ancora é apagada o navegador volta para o topo
    document.ontouchmove = function(e){return true;} // Caso o touch tenha sido desabilitado reabilita.
  });
  $('#overlay div.centraliza').each(function(){
    $(this).css('display','none');
  });
}

/* ABRE LOADING */
function abreLoading(){
  if(window.location.hash !== '' && window.location.hash !== '#'){
    sctop = $('a'+window.location.hash).offset().top - 10;
    window.location.hash = '';
  } else {
    sctop = $(document).scrollTop();
  }
  /* HACK para o scroll do overlay sobrepor o da pagina */
  $("body").css({
    "height":$(window).height()+'px',
    "overflow-y":'scroll'/*,
    "position":'fixed'*/
  });
  $('html').css('overflow','auto');
  /* Seta a mascara negra */
  var maskHeight = $(window).height();
  var maskWidth = $(window).width();
  $('#overlay').css({
    'width':maskWidth,
    'height':maskHeight,
    'overflow-y':'scroll'
  });
  $("body").css({
    "height":$(window).height()+'px',
    "overflow-y":'scroll'
  });
  $("#pai").css('margin-top','-'+sctop+'px');
  
  $('#loading').css('display','block');
  $('#loading img').css('margin-top',($(window).height()/2)-10+'px');
  
  // Desabilitar a rolagem via touchscreen para nao rolar a pagina de baixo quando o conteudo do overlay é menor que a tela.
  var overlayHeight = $('#loading div.centraliza').height();
  if(maskHeight > overlayHeight){
    document.ontouchmove = function(e){e.preventDefault();}
  }
}

/* FECHA LOADING */
function fechaLoading(){
  $('#loading iframe').css('display','none');
  $('#loading').css('display','none');
  $("#pai").css('margin-top','0px');
  $("body").css({
    "height":'0px',
    "overflow":'visible',
    "position":'static'
  });
  $('html').css('overflow','visible');
  $(document).scrollTop(sctop); // É necessário porque quando a ancora é apagada o navegador volta para o topo
  document.ontouchmove = function(e){return true;} // Caso o touch tenha sido desabilitado reabilita.
}

/* Normalizar numeros floats no IE */
function toFixed(value, precision) {
    var precision = precision || 0,
    neg = value < 0,
    power = Math.pow(10, precision),
    value = Math.round(value * power),
    integral = String((neg ? Math.ceil : Math.floor)(value / power)),
    fraction = String((neg ? -value : value) % power),
    padding = new Array(Math.max(precision - fraction.length, 0) + 1).join('0');
    return precision ? integral + ',' +  padding + fraction : integral;
}


$(document).ready(function(){
  /* MENU CIDADES E GENEROS */
  $(".container.cidade").click(function(){
    if($('.menu_busca.cidade').css('display')=='block'){
      $('.menu_busca.cidade').slideUp();
    } else {
      function menuBuscaCidade(){
        $('.menu_busca.cidade').slideDown();
        $(".menu_busca.cidade").hover(
          function(){
            $('.menu_busca.cidade').css('display', 'block');
          }, 
          function(){
            $('.menu_busca.cidade').slideUp(150);
          }
        );
      }
      
      if($('.menu_busca.genero').css('display')=='block'){
        $('.menu_busca.genero').slideUp(150,function(){
          menuBuscaCidade();
        });
      } else {
        menuBuscaCidade();
      }
    }
  });
  $(".container.genero").click(function(){
    if($('.menu_busca.genero').css('display')=='block'){
      $('.menu_busca.genero').slideUp();
    } else {
      function menuBuscaGenero(){
        $('.menu_busca.genero').slideDown();
        $(".menu_busca.genero").hover(
          function(){
            $('.menu_busca.genero').css('display', 'block');
          }, 
          function(){
            $('.menu_busca.genero').slideUp(150);
          }
        );
      }
      
      if($('.menu_busca.cidade').css('display')=='block'){
        $('.menu_busca.cidade').slideUp(150,function(){
          menuBuscaGenero();
        });
      } else {
        menuBuscaGenero();
      }
    }
  });
  
  /* Fixa janela de erro */
  if($('div.alert').length > 0){
  $('div.alert a').on('click',function(){
    $('div.alert').slideUp(150);
  });
  var alerttop = $('div.alert').offset().top;
  $(document).scroll(function(){
    if($(window).scrollTop() > alerttop){
      $('div.alert').css('position','fixed');
      $('div.alert').css('top','0');
    } else if($(window).scrollTop() <= alerttop){
      $('div.alert').css('position','absolute');
      $('div.alert').css('top',alerttop.toString()+'px');
    }
  });
  }
  
  /* Mascaras, placeholder e custom selectbox */
  $("select").selectbox({
    onOpen: function(inst){
      $(this).next().css('z-index',2);
    },
    onClose: function(inst){
      $(this).next().css('z-index',1);
    }
  });
  $('input, textarea').placeholder();
  $('input[name=cpf]').mask('000.000.000-00', {reverse: true});
  $('input[name=cep]').mask('00000-000');

  /* VERIFICA SE ABRE O OVERLAY VIA HASH NO LINK OU NAO */
  /*$(document).ready(function(){
    if(window.location.hash == '#overlay'){
      overlay(0);
    }
  });*/
  
  /* RECALCULA O TAMANHO DO OVERLAY */
  $(function(){
      $(window).resize(function(){
        if($('#overlay').css('display')=='block'){
          var maskHeight = $(window).height();
          var maskWidth = $(window).width();
          $('#overlay').css({
            'width':maskWidth,
            'height':maskHeight
          });
        }
      });
  });

  /* ABRE OVERLAY */
  $('div.outras_datas, a.termos_de_uso, a.politica_de_privacidade').on('click',function(e){
    e.preventDefault();
    abreOverlay($(this).attr('class'));
  });
  
  /* FECHA OVERLAY */
  $('#overlay, #overlay div.fechar').on('click',function(){
    fecharOverlay();
  }).children().click(function(e) {
    return false;
  });
  $('#overlay .unica').on('click',function(){
    fecharOverlay();
  });
  
  /* IMG DO CODIGO DO CARTAO */
  $('a.meu_codigo_cartao').on('click',function(e){
    e.preventDefault();
    if($('div.img_cod_cartao').css('display')=='none'){
      var cod = $('input[name=cartao]:checked').val();
      var img = '';
      var frase = '';
      if(cod>0){
        if(cod==3){
          img = 'amex';
          frase = 'O código de segurança<br />está localizado na parte<br />frontal do cartão e<br />corresponde aos 4 dígitos,<br />acima da faixa numérica';
          } else {
          img = 'geral';
          frase = 'O código de segurança<br />são os 3 últimos números<br />da faixa numérica presente<br />no verso do cartão';
        }
        $('div.img_cod_cartao img').attr('src','images/img_cod_'+img+'.png');
        $('div.img_cod_cartao p').html(frase);
        $('div.img_cod_cartao').fadeIn(500,function(){
          $('body').on('click',function(){
            if($('div.img_cod_cartao').css('display')=='block'){
              $('div.img_cod_cartao').fadeOut(200,function(){
                $('body').off('click');
              });
            }
          });
        });
      }
    } else {
      $('div.img_cod_cartao').fadeOut(200);
      $('body').off('click');
    }
  });
  $('input[name=cartao]').on('change',function(){
    $('div.img_cod_cartao').fadeOut(200);
  });
  
  /* ABRIR COMBO DE ESTADO AO SELECIONAR A FORMA DE ENTREGA */
  $('select[name=tipo]').on('change',function(){
    var cod = $('select[name=tipo] :selected').val();
    if(cod==2){
      $('div.selecione_estado').fadeIn(1000);
    }
  });
  $('select[name=estado]').on('change',function(){
    var custo = 0;
    var cod = $('select[name=estado] :selected').val();
    if(cod=='AC'){
      custo = 20.30;
    } else if(cod=='AL'){
      custo = 10.50;
    } else if(cod=='AM'){
      custo = 8.75;
    } else if(cod=='AP'){
      custo = 20;
    } else if(cod=='BA'){
      custo = 5;
    } else if(cod=='CE'){
      custo = 8;
    }
    if(custo>0){
      $('div.pedido_entrega div.valor').html('<span>R$</span> '+toFixed(custo,2));
      var total = $('.pedido_total .valor').html();
      total = parseFloat(total.replace(',','.'));
      $('.pedido_total .valor').html(toFixed(total+custo,2));
    }
  });
  
  /* Coloca cor na classificacao */
  if($('div.cont_gen_class_dura span.classificacao').length > 0){
    var n = $('div.cont_gen_class_dura span.classificacao').html();
    n = n.toLowerCase();
    var cor = '';
    if(n == 'l'){
      cor = '#118242';
    } else if(n == 10){
      cor = '#4374B9';
    } else if(n == 12){
      cor = '#FDD015';
    } else if(n == 14){
      cor = '#F6821F';
    } else if(n == 16){
      cor = '#B50A37';
    } else if(n == 18){
      cor = '#060709';
    }
    $('div.cont_gen_class_dura span.classificacao').css('background-color',cor);
  }
});