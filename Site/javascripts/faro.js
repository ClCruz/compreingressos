/**
 * @author BlackLotus
 */

jQuery(document).ready(function($){

	function buscaEspetaculos(){
	  $busca = $('input[name="busca"]');
	  $btn = $('#busca-espetaculos');

	  if ($busca.val() == '' || $busca.val().length < 4) 
	  {
	    return false;
	  }else{
	      $btn.click();
	  }
	}

	function midiasSociais(maximo){
	  $('.midias_sociais').css('display','block');
	  $('.midias_sociais').css('top',(maximo+10).toString()+'px');
	  maxt = maximo-$('.container_mini_cabecalho').height();
	  $(window).scroll(function(){
	    if($(window).scrollTop() > maxt){
	      $('.midias_sociais').addClass('fixed');
	      $('.midias_sociais').css('top','82px');
	    } else if($(window).scrollTop() <= maxt){
	      $('.midias_sociais').removeClass('fixed');
	      $('.midias_sociais').css('top',(maximo+10).toString()+'px');
	    }
	  });
	}

	function posicionaCursor(obj){
	  var obj = obj.get(0);
	  if (obj.createTextRange) {
	    var range = obj.createTextRange();
	    range.collapse(true);
	    //range.move('character', 0);
	    range.moveEnd('character', 0);
	    range.moveStart('character', 0);
	    range.select();
	  } else if (obj.setSelectionRange) {
	    obj.focus();
	    obj.setSelectionRange(0, 0);
	  }
	}

	function setaInput(campo,mensagem){
	  campo.on('focus',function(){
	    if(campo.val() == mensagem){
	      posicionaCursor($(this));
	      campo.css('color','#B6B6B6');
	    }
	  }).on('keypress',function(){
	    if(campo.val() == mensagem){
	      campo.attr('value','');
	    }
	    campo.css('color','#FFF');
	  }).on('focusout',function(){
	    if(campo.val() == ''){
	      campo.attr('value',mensagem);
	    }
	    campo.css('color','#FFF');
	  });
	  if (campo.val() == ''){
	    campo.attr('value',mensagem);
	  }
	}

	function configuraInputs(){
	  setaInput($('#newsletter input[name=nome]'),'insira seu nome');
	  setaInput($('#newsletter input[name=email]'),'insira seu e-mail');
	}

	/* REDIRECIONA USUARIOS DE "MOBILE" PARA A HOME DE ESPETACULOS CONFORME SUA CIDADE VIA IP */
	function mobileRedirect(){
	  if (window.innerWidth<=640){
	    window.location.replace(location.protocol+'//'+location.host+"/espetaculos?auto=true");
	  }
	  $(window).resize(function(){
	    if (window.innerWidth<=640){
	      window.location.replace(location.protocol+'//'+location.host+"/espetaculos?auto=true");
	    }
	  });
	}


	/* DIVIDE TAMANHOS EM PIXELS POR X DOS ELEMENTOS DECLARADOS PARA PAGINAS COM BACKGROUND PERSONALIZADO*/
	function mobileDivisor(d,p){
	  function mD(divisor,params,restaurador){
	    for (p=0 ; p<params.length ; p++){
	      for (i=1 ; i<params[p].length ; i++){
	        var valor = $(params[p][0]).css(params[p][i]);
	        if (restaurador==1){
	          valor = Math.ceil(parseInt(valor)*divisor)+"px !important;";
	        } else {
	          valor = Math.ceil(parseInt(valor)/divisor)+"px !important;";
	        }
	        $(params[p][0]).css(params[p][i],valor);
	        $(params[p][0]).attr('style',params[p][i]+":"+valor);
	      }
	    }
	  }
	  $(document).ready(function(){
	    var pa = p;
	    var r = 1;
	    if(window.innerWidth<=640 && mobileversion==1){
	      r = 0;
	      mD(d,pa,r);
	      r = 3;
	    }
	    $(window).resize(function(){
	      if(window.innerWidth<=640 && mobileversion==1){
	        if (r==1){
	          r = 0;
	          mD(d,pa,r);
	          r = 3;
	        }
	      } else if (r==0 || r==3) {
	        r = 1;
	        mD(d,pa,r);
	      }
	    });
	  });
	}

	function mobileMap(mobileDivisor){
	  function mM(md,restaurador){
	    $("map[name=header_map] area").each(function(){
	      var coords = $(this).attr('coords').split(',');
	      for(i=0 ; i<coords.length ; i++){
	        if (restaurador==1){
	          coords[i] = Math.ceil(coords[i]*md);
	        } else {
	          coords[i] = Math.ceil(coords[i]/md);
	        }
	        
	      }
	      $(this).attr('coords',coords.join(','));
	    });
	  }
	  
	  $(document).ready(function(){
	    var md = mobileDivisor;
	    var r = 1;
	    if(window.innerWidth<=640 && mobileversion==1){
	      r = 0;
	      mM(md,r);
	      r = 3;
	    }
	    $(window).resize(function(){
	      if(window.innerWidth<=640 && mobileversion==1){
	        if (r==1){
	          r = 0;
	          mM(md,r);
	          r = 3;
	        }
	      } else if (r==0 || r==3) {
	        r = 1;
	        mM(md,r);
	      }
	    });
	  });
	}

	function mobileImages(){
	  function images(){
	    $('div#menu_topo a.logo').data('href',$('div#menu_topo a.logo').attr('href'));
	    if (window.innerWidth<=640 && mobileversion==1){
	      $('div#menu_topo a.logo').attr('href',"/espetaculos?auto=true");
	      $('div.card a img').each(function(){
	        if ($(this).attr('height')==180){
	          $(this).attr('height','148');
	        } else if($(this).attr('height')==102){
	          $(this).attr('height','84');
	        }
	      });
	    } else {
	      $('div#menu_topo a.logo').attr('href',$('div#menu_topo a.logo').data('href'));
	      $('div.card a img').each(function(){
	        if ($(this).attr('height')==148){
	          $(this).attr('height','180');
	        } else if($(this).attr('height')==84){
	          $(this).attr('height','102');
	        }
	      });
	    }
	  }
	  
	  images();
	  $(window).resize(function(){
	    images();
	  });
	}


	// function desktopVersion(){
	//   function desktop(){
	//     var href = $('a.link_adptativo').attr('href').split('desktop=');
	//     if (location.href.indexOf("desktop=") != -1) {
	//       href = location.href.replace(/(\&|\?)(desktop=)(true|fase)/, "$1$2");
	//     } else if(location.search === "") {
	//       href = location.href+"?desktop=";
	//     } else {
	//       href = location.href+"&desktop=";
	//     }
	    
	//     if (window.innerWidth<=640 && mobileversion==1){
	//       $('a.link_adptativo').attr('href',href+"true");
	//       $('a.link_adptativo').html('visualizar na versão desktop');
	//       $('a.link_adptativo').show();
	//     } else if (mobileversion==0){
	//       $('p.creditos').css('margin-bottom','5px');
	//       $('a.link_adptativo').attr('href',href+"false");
	//       $('a.link_adptativo').html('visualizar na versão celular');
	//       $('a.link_adptativo').show();
	//     } else {
	//       $('a.link_adptativo').hide();
	//     }
	//   }
	  
	//   desktop();
	//   $(window).resize(function(){
	//     desktop();
	//   });
	// }


	function visorMobile(){
	  function mobile(){
	    if ($('div.swiper-wrapper').length > 0){
	      if (window.innerWidth<=640 && mobileversion==1){
	        $('div.swiper-wrapper').children().each(function(){
	          /*if($(this).attr('class')=='swiper-slide'){
	            $(this).remove();
	          }*/
	          $(this).children().attr('style', $(this).children().attr('style').replace('visor.jpg','visor_mobile.jpg'));
	        })
	      } else {
	        $('div.swiper-wrapper').children().each(function(){
	          $(this).children().attr('style', $(this).children().attr('style').replace('visor_mobile.jpg','visor.jpg'));
	        })
	      }
	    }
	  }
	  
	  mobile();
	  $(window).resize(function(){
	    mobile();
	  });
	}

	/* HAILITA O CLICK NO BACKGROUND DA HOME */
	function backgroundClick(el,eb,dl,db){
	  $(document).ready(function(){
	    if(window.innerWidth>1000){
	      var dw = Math.ceil((window.innerWidth-976)/2);
	      $("#background_holder").append($("<a></a>").attr('style','width:'+dw+'px').attr('class','bgclick left').attr('href',el).attr('target',eb ? '_blank':''));
	      $("#background_holder").append($("<a></a>").attr('style','width:'+dw+'px').attr('class','bgclick right').attr('href',dl).attr('target',db ? '_blank':''));
	    }
	    
	    $(window).resize(function(){
	      if(window.innerWidth>640){
	        var dw = (window.innerWidth-980)/2;
	        $(".bgclick").css('width',dw+'px');
	      }
	    });
	  });
	}

	/* VALIDACAO DO FORMULARIO DO GUIA DE ESPETACULOS */
	/* VARIAVEIS GLOBAIS PARA A VALIDAÇÃO DOS CAMPOS */
	var err = 1;
	var nomee = 1;
	var emaile = 1;
	var estadoe = 1;
	var acordoe = 1;

	/* FUNCAO DE VALIDACAO CHAMADA AO ENVIAR O FORMULARIO */
	function validaNewsletter(campo){
	  function validaEmail(email){
	    var filtro = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
	    if(filtro.test(email)){
	      return true;
	    } else {
	      return false;
	    }
	  }
	  
	  var nome = $('#newsletter input[name=nome]').val();
	  var email = $('#newsletter input[name=email]').val();
	  var estado = $('#newsletter input[name=estado]').val();
	  var acordo = $('#newsletter input[name=acordo]').val();
	  var fade = 50;
	    
	  if(campo=='nome' || campo==''){
	    if(nome=='' || nome=='insira seu nome'){
	      err = 1;
	      nomee = 1;
	      if($('#newsletter .container .um .check').css('background-position')!='0px -20px'){
	        $('#newsletter .container .um').css('background-position','50% 0');
	        $('#newsletter .container .um .check').fadeOut(function(){
	          $('#newsletter .container .um .check').css('background-position','0px -20px').fadeIn(fade);
	        });
	      }
	    } else{
	      err = 0;
	      nomee = 0;
	      if($('#newsletter .container .um .check').css('background-position')!='0px 0px'){
	        $('#newsletter .container .um').css('background-position','50% -58px');
	        $('#newsletter .container .um .check').fadeOut(function(){
	          $('#newsletter .container .um .check').css('background-position','0px 0px').fadeIn(fade);
	        });
	      }
	    }
	  }
	  
	  if(campo=='email' || campo==''){
	    if(email=='' || email=='insira seu e-mail' || !validaEmail(email)){
	      err = 1;
	      emaile = 1;
	      if($('#newsletter .container .dois .check').css('background-position')!='0px -20px'){
	        $('#newsletter .container .dois').css('background-position','50% 0');
	        $('#newsletter .container .dois .check').fadeOut(function(){
	          $('#newsletter .container .dois .check').css('background-position','0px -20px').fadeIn(fade);
	        });
	      }
	    } else{
	      err = 0;
	      emaile = 0;
	      if($('#newsletter .container .dois .check').css('background-position')!='0px 0px'){
	        $('#newsletter .container .dois').css('background-position','50% -58px');
	        $('#newsletter .container .dois .check').fadeOut(function(){
	          $('#newsletter .container .dois .check').css('background-position','0px 0px').fadeIn(fade);
	        });
	      }
	    }
	  }
	  
	  if(campo=='estado' || campo==''){
	    if($('#guia_espetaculos input[name=estado]').val()=='0' || $('#guia_espetaculos input[name=estado]').val()=='undefined'){
	      err = 1;
	      estadoe = 1;
	      if($('#newsletter .container .tres .check').css('background-position')!='0px -20px'){
	        $('#newsletter .container .tres').css('background-position','50% 0');
	        $('#newsletter .container .tres .check').fadeOut(function(){
	          $('#newsletter .container .tres .check').css('background-position','0px -20px').fadeIn(fade);
	        });
	      }
	    } else {
	      err = 0;
	      estadoe = 0;
	      if($('#newsletter .container .tres .check').css('background-position')!='0px 0px'){
	        $('#newsletter .container .tres').css('background-position','50% -58px');
	        $('#newsletter .container .tres .check').fadeOut(function(){
	          $('#newsletter .container .tres .check').css('background-position','0px 0px').fadeIn(fade);
	        });
	      }
	    }
	  }
	  
	  if(campo!='' && campo!='acordo'){
	    if(nomee==1 || emaile==1 || estadoe == 1){
	      if($('#newsletter .container.status .status').css('background-position') != '50% -276px'){
	        $('#newsletter .container.status .status').fadeOut(function(){
	          $('#newsletter .container.status .status').css('background-position','50% -276px').fadeIn(fade);
	        });
	      }
	    } else if(nomee==0 && emaile==0 && estadoe == 0){
	      $('#newsletter .container.status .status').fadeOut(function(){
	        $('#newsletter .container.status .status').css('background-position','50% 0').fadeIn(fade);
	      });
	    }
	  }
	  
	  
	  if(campo=='acordo' || campo==''){
	    if($('#newsletter .container.status :checkbox:checked').length > 0){
	      err = 0;
	      acordoe = 0;
	    } else {
	      err = 1;
	      acordoe = 1;
	    }
	  }
	  if(campo!='' && campo=='acordo'){
	    //alert(nomee+' '+emaile+' '+estadoe);
	    if($('#newsletter .container.status :checkbox:checked').length > 0){
	      $('#newsletter .container.status .status').fadeOut(function(){
	        $('#newsletter .container.status .status').css('background-position','50% 0').fadeIn(fade);
	      });
	    } else {
	      $('#newsletter .container.status .status').fadeOut(function(){
	        $('#newsletter .container.status .status').css('background-position','50% -368px').fadeIn(fade);
	      });
	    }
	  }
	  
	  
	  if(campo=='') {
	    if(acordoe==1) {
	      $('#newsletter .container.status .status').fadeOut(function() {
	        $('#newsletter .container.status .status').css('background-position','50% -368px').fadeIn(200);
	      });
	    } else if(err==1 || nomee==1 || emaile==1 || estadoe == 1) {
	      $('#newsletter .container.status .status').fadeOut(function() {
	        $('#newsletter .container.status .status').css('background-position','50% -276px').fadeIn(200);
	      });
	    } else {
	      // campos válidos "enviar dados para o servidor"
	      $('#newsletter .container.status .status').fadeOut(50,function() {
	        $('#newsletter .container.status .loading').fadeIn(50,function() {
	        	$('#newsletter .container.status .loading').fadeOut(50,function() {
					$('#newsletter .container.status .status').css('background-position','50% -92px').fadeIn(200);
					$('#guia_espetaculos').html('');
					$('#guia_espetaculos').css('background-image','url(../images/newsletter_cadastrado.jpg)');
	            });
	        });
	      });
	    }
	  }
	}

	/* FUNCAO DE ABRIR O OVERLAY E TRAVAR A ROLAGEM EM APARELHOS TOUCH */
	var sctop = 0; // Var global para o scroll da página ficar fixo nos aparelhos touchscreen
	var gallery = 0; // Var global para não sobrepor a chamada da galeria de imagens do overlay
	function overlay(fade,hashtag){
	  /* Scroll do overlay sobrepor o da pagina */
	  $("body").css({
	    "height":$(window).height()+'px',
	    "overflow-y":'scroll'
	  });
	  $('html').css('overflow','auto');
	  window.location.hash = hashtag;

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
	  
	  /* Seta o navigation da galeria de imagens*/
	  $('div.navigation').css({'width' : '150px', 'float' : 'left'});
	  $('div.content').css('display', 'block');

	  /* Inicializa o plugin Galleriffic Gallery da galeria de fotos */
	  if(!gallery && $('.container_overlay.fotos').length > 0){
	    gallery = $('.container_overlay .thumbs_container').galleriffic({
	      delay:                     2500,
	      numThumbs:                 6,
	      preloadAhead:              7,
	      enableTopPager:            false,
	      enableBottomPager:         true,
	      maxPagesToShow:            4,
	      imageContainerSel:         '.slideshow',
	      //controlsContainerSel:      '#controls',
	      //captionContainerSel:       '#caption',
	      loadingContainerSel:       '.loading',
	      renderSSControls:          false,
	      renderNavControls:         false,
	      playLinkText:              '> Slideshow',
	      pauseLinkText:             '|| Slideshow',
	      prevLinkText:              '< Foto anterior',
	      nextLinkText:              'Próxima foto >',
	      nextPageLinkText:          '>',
	      prevPageLinkText:          '<',
	      enableHistory:             false,
	      autoStart:                 false,
	      syncTransitions:           true,
	      enableKeyboardNavigation:  false,
	      defaultTransitionDuration: 900
	    });
	  }

	  /* Verifica se abre com fade ou nao */
	  if(fade==1){
	    $('#overlay').fadeTo("slow",1.0,function(){
	      $('#overlay iframe').css('display','block');
	    });
	  } else {
	    $('#overlay').css('display','block');
	    $('#overlay iframe').css('display','block');
	  }
	  
	  
	  /* Desabilita a rolagem via touchscreen para nao rolar a pagina abaixo do overlay */
	  var overlayHeight = $('#overlay div.centraliza').height();
	  if(maskHeight > overlayHeight){
	    document.ontouchmove = function(e){e.preventDefault();}
	  }
	}

	function setCookie(cname, cvalue, exdays) {
	  var d = new Date();
	  d.setTime(d.getTime() + (exdays*24*60*60*1000));
	  var expires = exdays>0 ? "; expires="+d.toUTCString():'';
	  console.log(cname + "=" + cvalue + expires);
	  document.cookie = cname + "=" + cvalue + expires;
	}

	function getCookie(cname) {
	    var name = cname + "=";
	    var ca = document.cookie.split(';');
	    for(var i=0; i<ca.length; i++) {
	        var c = ca[i];
	        while (c.charAt(0)==' ') c = c.substring(1);
	        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
	    }
	    return "";
	}

	/* ALTERA IMAGES PARA VERSAO MOBILE DO VISOR/SWIPER */
	visorMobile();

	$(document).ready(function(){
	  /* ABRE APP SOMENTE EM MOBILES OU TELAS PEQUENAS */
	  if (window.innerWidth<=640){
	    // Le o cookie que previne do banner abrir
	    var baixeapp = (getCookie('baixeapp'));
	    // Verifica se já foi clicado/fechado o banner ou não
	    if (baixeapp!='1'){
	      $('.baixe_app').show();
	      $('body').css('overflow','hidden');
	      //document.ontouchmove = function(e){e.preventDefault();}
	    } 
	  }
	  
	  /* ALTERA URL DO LOGO PARA LAYOUT RESPONSIVO E IMAGENS DOS ESPETACULOS PARA A ALTURA DO RESPONSIVO */
	  mobileImages();
	  
	  /* LINK PARA VISUALIZACAO EM VERSAO DESKTOP DO SITE INTEIRO */
	  desktopVersion();

	  var topCidade = $("#buscaCidade");
	  var btnCidade = $("#btnbuscaCidade");

	  var topGenero = $("#buscaGenero");
	  var btnGenero = $("#btnbuscaGenero");

	  cfgShow(btnCidade, topCidade);
	  cfgShow(btnGenero, topGenero);

	  function cfgShow(btn, div)
	  {
	    $(div).on('click mouseleave',function () {
	      $(this).slideUp();
	      $(this).removeAttr('data-topstatus');
	    });

	    $(btn).click(function(){

	      var outro = $('[data-topstatus="show"]');

	      //Se for o mesmo, apenas esconder.
	      if (outro[0] == div[0])
	      {
	        $(div[0]).slideUp();
	        $(div[0]).removeAttr('data-topstatus');
	        return false;
	      }

	      //Se houver outro em exibição, esconder e depois mostrar o novo
	      if (outro[0])
	      {
	        $(outro).removeAttr('data-topstatus');
	        $(outro).slideUp('fast', function () {
	          show(div);
	        });
	      }
	      else
	      {
	        show(div);
	      }
	    });


	    function show(div)
	    {
	      $div = $(div);
	      var status = $div.css('display');

	      if (status == 'none')
	      {
	        var menuHeight = $('#novo_menu').outerHeight();
	        $div.css('top', menuHeight );
	        $div.slideDown();
	        $div.attr('data-topstatus','show');
	      }
	    }
	  }  

	  /* POSICIONA O CURSOR NO INICIO DO CAMPO NO GUIA DE ESPETACULOS */
	  configuraInputs();
	  
	  /* ABRE/FECHA GUIA DE ESPETACULOS */
	 var msalturadefault = parseInt($('.midias_sociais').css('top'));
	  
	  /* CLIQUE DO CAMPO ESTADO */
	  $('.estadoselecionado').on('click',function(){
	    if($('#guia_espetaculos input[name=estado]').val()=='0' || $('#guia_espetaculos input[name=uf]').val()=='undefined'){
	      if($('#newsletter .container .tres .check').css('background-position')!='0px -20px'){
	          //clear
	      }
	    }
	    if ($('.estadoselecionado span').css('background-position') == '0px 0px'){
	      $('.estadoselecionado span').css('background-position','0px -26px');
	      $('ul.containerestados').fadeIn();
	    } else {
	      $('.estadoselecionado span').css('background-position','0px 0px');
	      $('ul.containerestados').fadeOut();
	    }
	  });
	  
	  /* ESCOLHER ESTADO */
	  $('.containerestados li').on('click',function(){
	    $('#guia_espetaculos .estadoselecionado').html($(this).html()+' <span></span>');
	    $('#guia_espetaculos input[name=estado]').attr('value',$(this).attr('rel'));
	    $('.containerestados').fadeOut(100);
	    validaNewsletter('estado');
	  });

	  /* VALIDA O FORMULARIO ANTES DE ENVIAR */
	  $('#newsletter').on('submit',function(e){
	    e.preventDefault();
	    validaNewsletter('');
	  });
	  
	  /* VALIDA AO DIGITAR NO CAMPO E AO SAIR DO CAMPO */
	  $('#newsletter input').on('keypress, focus, change, change keyup',function(e){
	    if($(this).val().length > 2){
	      if ($(this).attr('name')=='email'){
	        if($(this).val().indexOf('@')){
	          validaNewsletter($(this).attr('name'));
	        }
	      } else {
	        validaNewsletter($(this).attr('name'));
	      }
	    }
	  });
	  
	  /* VALIDA AO CLICAR NO CHECKBOX */
	  $('#newsletter input[name=acordo]').on('change',function(){
	    validaNewsletter($(this).attr('name'));
	  });
	  
	  /* PREENCHE COR NA CLASSIFICACAO FAIXA ETARIA DOS ESPETACULOS */
	  if($('div.container_mini_topo span.classificacao').length > 0){
	    var n = $('div.container_mini_topo span.classificacao').html();
	    var cor = '';
	    if(n == 'L'){
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
	    $('div.container_mini_topo span.classificacao').css('background-color',cor);
	  }

	  /* VERIFICA SE ABRE O OVERLAY AO DIGITAR HASHTAG NO LINK */
	  if(window.location.hash == '#informacoes'){
	    $('#overlay .informacoes').show();
	    overlay(0,'informacoes');
	  } else if(window.location.hash == '#datas'){
	    $('#overlay .datas').show();
	    overlay(0,'datas');
	  }
	  
	  /* RECALCULA O TAMANHO DO OVERLAY */
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

	  /* ABRE OVERLAY */
	  $('.container_mini_topo span.sinopse, .container_mini_topo span.fotos, .container_mini_topo span.imagens, .container_mini_topo span.videos, .container_mini_topo span.mapa_de_plateia').on('click',function(e){
	    e.preventDefault();
	    sctop = $(document).scrollTop();
	    var divoverlay = "#overlay .informacoes .container_overlay."+$(this).attr('class').replace('icone ','');
	    $(divoverlay).insertBefore($('#overlay .informacoes .container_overlay').first());
	    $('#overlay .informacoes').show();
	    overlay(1,'informacoes');
	  });
	  $('#iframe .todasdatas').on('click',function(e){
	    e.preventDefault();
	    sctop = $(document).scrollTop();
	    $('#overlay .datas').show();
	    overlay(1,'datas');
	  });
	  
	  /* ABRE OVERLAY COM TEXTO DESCRITIVO DO PACOTE */
	  $("div.card p.pacote_detalhes2").on('click',function(){
	    sctop = $(document).scrollTop();
	    var divinf = $(this).next().next();
	    $('#overlay div.informacoes div.conteudo').html(divinf.html());
	    $('#overlay div.cont_teatro p.nome_pacote').html($(this).attr('title'));
	    $('#overlay .informacoes').show();
	    overlay(1,'assinatura');
	  });
	  
	  /* FECHA OVERLAY */
	  $('#overlay, #overlay div.fechar').on('click',function(e){
	    if (e.target.className == 'fechar' || e.target.id == 'overlay'){
	      $('#overlay iframe').css('display','none');
	      $('#overlay').fadeTo(300,0,function(){
	        $('#overlay').css('display','none');
	        $("#pai").css('margin-top','0px');
	        $("body").css({
	          "height":'0px',
	          "overflow":'visible',
	          "position":'static'
	        });
	        $('#overlay .informacoes, #overlay .datas').hide();
	        $('html').css('overflow','visible');
	        if($('#video_player').length > 0){
	          $('#video_player')[0].contentWindow.postMessage('{"event":"command","func":"' + 'pauseVideo' + '","args":""}', '*');
	        }
	        
	        window.location.hash = '';
	        $(document).scrollTop(sctop); // É necessário porque quando a ancora é apagada o navegador volta para o topo
	        document.ontouchmove = function(e){return true;} // Caso o touch tenha sido desabilitado reabilita.
	      });
	    }
	  });
	  
	  /* ABRE INFORMACOES DO BOTAO DO ESPETACULO ESPECIAL */
	  $('div.botoes_sessoes a.botao').on('click',function(){
	    var bt = $(this).attr('class').split(' '); // Separa as classes usadas
	    // Se tiver mais do que 2 classes declara significa que o botao é desativo e não faz nada
	    if(bt.length<=2){
	      // Se a dive que foi requisitada a ser aberta já estiver aberta não faz nada
	      if($('div.detalhes_espetaculo.especial div#'+bt[1]).css('display')!='block'){
	        // Fecha todas as divs de informacões abertas, somente é possível ter uma aberta mas para não ficar procurando por nome opter por fechar todas
	        $('div.detalhes_espetaculo.especial div.cont').each(function(){
	          if ($(this).css('display') == 'block'){
	            $('div.detalhes_espetaculo.especial div.cont iframe').hide(); // Também escondo a div com o iframe do vídeo do youtube pois se ele não acompanha o efeito de rolagem
	            $(this).fadeOut(400);
	          }
	        });
	        if($('#video_player').length > 0){
	          $('#video_player')[0].contentWindow.postMessage('{"event":"command","func":"' + 'pauseVideo' + '","args":""}', '*');
	        }
	        // Remove a classe ativo do ultimo botao clicado
	        $('div.botoes_sessoes a.botao').each(function(){
	          $(this).removeClass('ativo');
	        });
	        // Quando é um vídeo é necessário que o iframe do youtube seja visivel somente após o fade completo da página pois ele não acompanha o efeito de rolagem
	        if (bt[1]=='videos'){
	          $('div.detalhes_espetaculo.especial div#'+bt[1]).fadeIn(400);
	          $('div.detalhes_espetaculo.especial div.cont iframe').show(); // Mostra o iframe do vídeo
	          $('div.detalhes_espetaculo.especial').css('height',$('div.detalhes_espetaculo.especial div#'+bt[1]).outerHeight()); // Abre o container até certo tamanho para o rodape não sobrepor o conteudo
	        } else {
	          $('div.detalhes_espetaculo.especial').css('height',$('div.detalhes_espetaculo.especial div#'+bt[1]).outerHeight()); // Abre o container até certo tamanho para o rodape não sobrepor o conteudo
	          $('div.detalhes_espetaculo.especial div#'+bt[1]).fadeIn(400);
	        }
	        window.location.hash = "#cabecalho"
	      }
	    }
	    // Adicionar a class ativo ao botao clicado
	    $(this).addClass('ativo');
	  });
	  
	  function getOffset(el){
	    var t = 0;
	    var b = 0;
	    var l = 0;
	    var r = 0;
	    t += Math.floor(el.offset().top-$(window).scrollTop());
	    b += Math.floor($(window).height()-(el.offset().top-$(window).scrollTop()+el.height()));
	    l += Math.floor($(window).width()-($(window).width()-el.offset().left));
	    r += Math.floor($(window).width()-el.offset().left);
	    return {left: l, right: r, top: t, bottom: b};
	  }
	  
	  /* ABRE TOPUP DA PAGINA DE PACOTES */
	  $("div.card p.pacote_detalhes").hover(
	    function(){
	      var divinf = $(this).next().next();
	      var dh = divinf.height()+22; // + margin and border
	      var dw = divinf.width()+22; // + margin and border
	      var pos = getOffset($(this));
	      
	      console.log(pos);
	      var ph = 22;
	      if (pos.bottom < dh ){
	        ph = ph+dh;
	      }
	      divinf.css("margin-top","-"+ph+"px");
	      
	      if(pos.right-36 < dw){
	        console.log(1);
	        var pw = -20;
	        pw = pw+(pos.right-dw);
	        pw = pw<0 ? pw*-1 : pw;
	        divinf.css("margin-left","-"+pw+"px");
	      }
	      divinf.show();
	      divinf.hover(function(){
	        divinf.show();
	      },
	      function(){
	        divinf.hide();
	      });
	    }, 
	    function(){
	      $(this).next().next().hide();
	    }
	  );
	  
	  $(".baixe_app_close").on('click',function(e){
	    e.stopPropagation();
	    $(this).parent().hide();
	    setCookie('baixeapp',1,0);
	    $('body').css('overflow','auto');
	    //document.ontouchmove = function(e){return true;}
	  });
	  
	  $(".baixe_app").on('click',function(e){
	    e.stopPropagation();
	    setCookie('baixeapp',1,0);
	    window.location.replace(location.protocol+'//'+location.host+"/app");
	  });

	  var $meuMenu = $('#novo_menu');
	  if($meuMenu.length > 0) {
	    var offset = $('#novo_menu').offset().top;
	    $(document).on('scroll', function () {
	        if (offset < $(window).scrollTop()) {
	            $meuMenu.addClass('fixar');
	        } else {
	            $meuMenu.removeClass('fixar');
	        }
	    });
	  }

	});

});

function changeAba(target) {
	jQuery('.aba_interna').hide();
	jQuery("#"+target).fadeIn('fast');
	jQuery(".aba_minha_conta").removeClass("aba_down");
	jQuery("#aba_"+target).addClass('aba_down')
}
