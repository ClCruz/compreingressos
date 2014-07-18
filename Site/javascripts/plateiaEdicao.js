$(function() {
  $('.number').onlyNumbers();
  $('.alpha').onlyAlpha();
	
  $.busyCursor();
	
  $('.button').button();
	
  var selected = $([]), offset = {
    top:0,
    left:0
  },
  defaultImage = '../images/palco.png',
  uploadPath = '';
	
  $.get('../settings/settings.php', {
    'var': 'uploadPath'
  }, function(data) {
    uploadPath = data;
    uploaderInit();
  });
	
  function changeImage(image) {
    var img = $('#mapa_de_plateia img');
		
    img.fadeOut('fast', function() {
      img.attr('src', image);
      img.fadeIn('slow');
    });
  }
	
  $('#removerImagem').click(function() {
    changeImage(defaultImage);
  });
	
  function annotation(obj) {
    return $(document.createElement('span'))
    //.text("\u25CF")
    .addClass('annotation')
    .addClass('diametro')
    .draggable({
      containment: 'parent',
      stack: 'span',
      distance: 10,
      //revert: 'valid',
      start: function(event, ui) {
        $(this).is(".ui-selected") || $(".ui-selected").removeClass("ui-selected");
        selected = $(".ui-selected").each(function() {
          var el = $(this);
          el.data("offset", el.offset());
        });
        offset = $(this).offset();
      },
      drag: function(event, ui) {
        var dt = ui.position.top - offset.top, dl = ui.position.left - offset.left;
							
        selected.not(this).each(function() {
          var el = $(this), off = el.data("offset");
          el.css({
            top: off.top + dt,
            left: off.left + dl
          });
        });
				
      },
      stop: function(ev, ui) {}
    })/*
      .droppable({
              tolerance: 'touch',
              //accept: '*:not(span)',
              drop: function(event, ui) {
                      if (ui.draggable.is('span')) {
                              $(this).effect('pulsate', 100);
                      }
              }
      })//*/;
  }
	
  $('#mapa_de_plateia').selectable({
    distance: 1,
    filter: 'span'
  });
	
  $('#teatroID').change(function() {
    if ($(this).val() != '') {
      var $this = $(this);
			
      $('#loadingIcon').fadeIn('fast')
			
      $.ajax({
        url: '../settings/functions.php',
        type: 'post',
        data: 'exec=echo comboSala("salaID", '+$this.val()+');',
        success: function(data) {
          changeImage(defaultImage);
          $('#xReset').click();
          $('#yReset').click();
          $('#mapa_de_plateia').removeAnnotations();
          $('#celSala').html(data);
        },
        complete: function() {
          $('#loadingIcon').fadeOut('slow');
        }
      });

      loadListaFotos();

      $('#areaUploadFotos').show(function(){
        $('#fotos').uploadifySettings('folder', uploadPath + 'fotos/' + $('#teatroID').val());
      });
    }
  });
	
  $('#celSala').delegate('select', 'change', function() {
    $('#carregaEvento').click();
  });
	
  $('#salvarEvento').click(function() {
    if ($('#teatroID').val() != '' && $('#salaID').val() != '') {
      $('#loadingIcon').fadeIn('fast')
			
      var objs = $('#mapa_de_plateia span').seralizeAnnotations(),
      xScale = $('#xScale').slider('value'),
      yScale = $('#yScale').slider('value'),
      size = $('#ScaleSize').slider('value'),
      dados = '[';
			
      $.each(objs, function(key, val) {
        dados += '{';
        $.each(val, function(key, val) {
          dados += '"' + key + '":"' + val + '",';
        });
        dados = dados.substr(0, dados.length - 1);
        dados += '},';
      });
			
      dados = dados.substr(0, dados.length - 1) + ']';
			
      if ($('#mapa_de_plateia img').attr('src') != defaultImage) {
        var img_src = $('#mapa_de_plateia img').attr('src').split('/'),
        imageName = img_src[img_src.length - 1];
      }
			
      $.ajax({
        url: 'mapaPlateia.php?action=save',
        type: 'post',
        data: 'obj=' + dados +
        '&teatro=' + $('#teatroID').val() +
        '&sala=' + $('#salaID').val() +
        ((imageName != undefined) ? '&image=' + imageName : '') +
        ((xScale != 630) ? '&xScale=' + xScale : '') +
        ((yScale != 510) ? '&yScale=' + yScale : '') +
        ((size != 0) ? '&Size=' + size : '')
        ,
        success: function(data) {
          $.dialog({
            title: 'Aviso...',
            text: data
          });
        },
        complete: function() {
          $('#loadingIcon').fadeOut('slow');
        }
      });
    }
  });
	
  $('#carregaEvento').click(function() {
    loadAnnotations('');
  });
	
  $('#resetEvento').click(function() {
    loadAnnotations('&reset=1');
  });

  $('#lista_fotos').on('click', 'img,span', function() {
    if ($(".ui-selected")[0]) {
      var path = $(this).attr('src') ? $(this).attr('src') : '';

      $("#dialog-confirm")
        .find('.img').html($(this).clone().wrap('<span />').parent().html()).end()
        .find('.text').text('Deseja ' + (path ? 'aplicar a imagem ao lado aos' : 'remover as imagens dos') + ' itens selecionados?').end()
        .dialog('open');
    }
  });

  $("#dialog-confirm").dialog({
    resizable: false,
    height: 'auto',
    modal: true,
    autoOpen: false,
    buttons: {
      OK: function() {
        var path = $("#dialog-confirm .img").find('img,span').attr('src');
        path = path ? path : '';

        $(".ui-selected").each(function() {
          $(this).data('img', path);
        });

        $(this).dialog("close");
      },
      Cancelar: function() {
          $(this).dialog("close");
      }
    }
  });
	
  function loadAnnotations(dados) {
    if ($('#teatroID').val() != '' && $('#salaID').val() != '') {
      dados = 'teatro='+$('#teatroID').val()+'&sala='+$('#salaID').val()+'&xmargin='+$('#xMargin').slider('value')+'&ymargin='+$('#yMargin').slider('value') + dados;
      size = 10;

      $('#loadingIcon').fadeIn('fast')
			
      $.ajax({
        url: 'mapaPlateia.php?action=load',
        type: 'post',
        data: dados,
        success: function(data) {
          data = data.split('||');
          $('#mapa_de_plateia').removeAnnotations();
					
          changeScale(data[2], data[3]);
          size = data[4];
					
          $('#mapa_de_plateia').addAnnotations(annotation, eval(data[0]));
          $('#mapa_de_plateia span').tooltip({
            track: true,
            content: function() {
              var element = $(this),
                  text = element.attr("title");

              if (element.data('img')) {
                text += "<br /><br /><img src='"+element.data('img')+"' class='foto-plateia' />";
              }

              return text;
            }
          });
					
          if (data[1].length > 0) {
            changeImage(uploadPath + data[1]);
          } else {
            changeImage(defaultImage);
          }
        },
        complete: function() {
          changeSize(size);
          $('#loadingIcon').fadeOut('slow');
        }
      });
    }
  }
	
  $('#xMargin, #yMargin').slider({
    value: 0.1,
    min: 0.01,
    max: 0.99,
    step: 0.01
  });
	
  $('#xScale').slider({
    value: 630,
    min: 300,
    max: 1500,
    step: 10,
    slide: updateX,
    stop: stopX
  });
	
  $('#yScale').slider({
    value: 510,
    min: 300,
    max: 1500,
    step: 10,
    slide: updateY,
    stop: stopY
  });

  $('#ScaleSize').slider({
    value: 10,
    min: 1,
    max: 40,
    step: 1,
    slide: updateSize,
    stop: stopSize
  });
	
  function updateX(event, ui) {
    $('#xScaleAmount').val(ui.value + 'px');
    $('#mapa_de_plateia, #mapa_de_plateia img').width(ui.value);
  }
  function updateY(event, ui) {
    $('#yScaleAmount').val(ui.value + 'px');
    $('#mapa_de_plateia, #mapa_de_plateia img').height(ui.value);
  }
  function updateSize(event, ui){    
    $('#Size').val(ui.value + 'px');
    $('.diametro').width(ui.value);
    $('.diametro').height(ui.value);
  }
  function stopY(event, ui) {
    if (ui.value > 1000) {
      $('#yScale').slider('option', 'max', ui.value * 2);
    } else {
      $('#yScale').slider('option', 'max', 1500);
    }
    $('#yScale').slider('value', ui.value);
  }
  function stopX(event, ui) {
    if (ui.value > 1000) {
      $('#xScale').slider('option', 'max', ui.value * 2);
    } else {
      $('#xScale').slider('option', 'max', 1500);
    }
    $('#xScale').slider('value', ui.value);
  }
  function stopSize(event, ui) {
    $('#ScaleSize').slider('value', ui.value);
    $('.diametro').width(ui.value);
    $('.diametro').height(ui.value);
  }
	
  $('#xReset').click(function(event) {
    event.preventDefault();
    $('#xScale').slider('value', 630);
    updateX(event, {
      value: 630
    });
  });
  $('#yReset').click(function(event) {
    event.preventDefault();
    $('#yScale').slider('value', 510);
    updateY(event, {
      value: 510
    });
    stopY(event, {
      value: 510
    });
  });
  $('#sizeReset').click(function(event) {
    event.preventDefault();
    $('#ScaleSize').slider('value', 10);
    updateSize(event, {
      value: 10
    });
    stopSize(event, {
      value: 10
    });
  });
	
  function uploaderInit() {
    $('#background').uploadify({
      uploader: '../javascripts/uploadify/uploadify.swf',
      checkScript: '../javascripts/uploadify/check.php',
      script: '../javascripts/uploadify/uploadify.php',
      cancelImg: '../javascripts/uploadify/cancel.png',
      auto: true,
      folder: uploadPath.substr(0, uploadPath.length - 1),
      fileDesc: 'Apenas Imagens',
      fileExt: '*.gif;*.jpg;*.jpeg;*.png;',
      queueID:'uploadifyQueue2',
      width: 300,
      onComplete: function(event, queueID, fileObj, response, data) {
        if (response.substr(0, 4) == 'true') {
          changeImage(response.split('?')[1]);
        } else {
          $.dialog({
            text: response
          });
        }
      }
    });

    $('#fotos').uploadify({
      uploader: '../javascripts/uploadify/uploadify.swf',
      checkScript: '../javascripts/uploadify/check.php',
      script: '../javascripts/uploadify/uploadify.php',
      cancelImg: '../javascripts/uploadify/cancel.png',
      auto: true,
      multi: true,
      folder: uploadPath + 'fotos',
      fileDesc: 'Apenas Imagens',
      fileExt: '*.gif;*.jpg;*.jpeg;*.png;',
      queueID:'uploadifyQueue3',
      width: 300,
      onComplete: function(event, queueID, fileObj, response, data) {
        if (response.substr(0, 4) == 'true') {
          loadListaFotos();
        } else {
          $.dialog({
            text: response
          });
        }
      }
    });
  }
	
  function changeScale(x, y) {
    if (x.length > 0) {
      $('#xScale').slider('value', parseInt(x));
      updateX(null, {
        value: parseInt(x)
      });
    } else {
      $('#xScale').slider('value', 630);
      updateX(null, {
        value: 630
      });
    }
    if (y.length > 0) {
      $('#yScale').slider('value', parseInt(y));
      updateY(null, {
        value: parseInt(y)
      });
    } else {
      $('#yScale').slider('value', 510);
      updateY(null, {
        value: 510
      });
      stopY(null, {
        value: 510
      });
    }    
  }

  function changeSize(size){
    if(size.length > 0){
      $('#ScaleSize').slider('value', parseInt(size));
      updateSize(null, {
        value: parseInt(size)
      });
    }else{
      $('#ScaleSize').slider('value', parseInt(10));
      updateSize(null, {
        value: 10
      });
      stopSize(null, {
        value: 10
      });
    }
  }

  function loadListaFotos() {
    $.ajax({
        url: 'mapaPlateia.php?action=lista_fotos',
        type: 'post',
        data: 'teatro='+$('#teatroID').val(),
        success: function(data) {
          $('#lista_fotos').html(data);
        }
    });
  }
});