/* 
 * Funções gerais de javascript
 * @author Edicarlos Barbosa <edicarlos.barbosa@cc.com.br>
 */

function mudarCidade(idEstado){
    $.ajax({
        url: "mudarMunicipio.php",
        type: 'post',
        data: 'idEstado='+idEstado,
        success: function(data){
            if(data != ""){
                $('#idmunicipio').html(data);
            }
        }
    });
};

