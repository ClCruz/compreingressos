<?php
require_once('../settings/functions.php');
require('../settings/Log.class.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 15, true)) {

    function getChildren($conn, $idPrograma, $idUsuario, $nivel) {
        $query = 'SELECT P.ID_PROGRAMA, P.ID_PARENT, P.DS_PROGRAMA, P.DS_URL,
				(SELECT \'checked\' FROM MW_USUARIO_PROGRAMA UP2 WHERE UP2.ID_PROGRAMA = P.ID_PROGRAMA AND UP2.ID_USUARIO = ?) AS CHECKED
				 FROM MW_PROGRAMA P
				 WHERE P.ID_PARENT = ?
				 ORDER BY P.ID_ORDEM_EXIBICAO, P.DS_PROGRAMA';
        $result = executeSQL($conn, $query, array($idUsuario, $idPrograma));

        $hasRows = hasRows($result);

        if ($hasRows) {
            $nbsp = '';
            for ($i = 0; $i < $nivel; $i++) {
                $nbsp .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            while ($rs = fetchResult($result)) {
                echo '<tr>
						<td>' . $nbsp . '&nbsp;' . utf8_encode($rs['DS_PROGRAMA']) . '</td>
						<td style="text-align: center;">
							<input type="checkbox" name="programas[]" ' . $rs["CHECKED"] . ' value="' . $rs["ID_PROGRAMA"] . '" class="filho' . $rs["ID_PARENT"] . '" />
						</td>
					</tr>';
                getChildren($conn, $rs['ID_PROGRAMA'], $idUsuario, $nivel + 1);
            }
        }
    }

// Alterar programas 
    if (isset($_GET["action"]) && $_GET['action'] != 'view') {
        if ($_GET["action"] == "insert")

            $query = "INSERT INTO MW_USUARIO_PROGRAMA (ID_USUARIO, ID_PROGRAMA) VALUES(" . $_POST["usuario"] . "," . $_POST["programas"] . ")";
        else
            $query = "DELETE FROM MW_USUARIO_PROGRAMA WHERE ID_USUARIO = " . $_POST["usuario"] . " AND ID_PROGRAMA = " . $_POST["programas"];

        executeSQL($mainConnection, $query);

        $log = new Log($_SESSION['admin'], 'Direitos de Acesso', $query);
        $log->save($mainConnection);

        if (sqlErrors()) {
            echo sqlErrors();
        }else{
            echo "true";
        }
    }
?>

    <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
    <script type="text/javascript" src="../javascripts/jquery.cookie.js"></script>
    <script>
        $(function() {
            $('.button').button();
            $('#btnAlterar').click(function(){
                if($.cookie('erro') == 'true')
                 $.dialog({title: 'Sucesso...', text: 'Dados alterados com sucesso'});
            });

            $('tr:not(.ui-widget-header)').hover(function() {
                $(this).addClass('ui-state-hover');
            }, function() {
                $(this).removeClass('ui-state-hover');
            });

            $(':checkbox').change(function() {
                var $this = $(this),
                checked = $this.attr('checked');

                $(':checkbox.filho'+$this.val()).attr('checked', checked);
                if ($this.is(':checked')) {
                    url = "programaUsuario.php?action=insert";
                    $(':checkbox[value='+$this.attr('class').split('filho')[1]+']').attr('checked', true);
                }else{
                    url = "programaUsuario.php?action=delete";
                }
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: ({programas: $(this).val(),usuario: <?php echo $_POST["usuario"]; ?> }),
                    success: function(data){
                        if(data.substr(0,4) == 'true'){
                            $.cookie('erro', 'true');
                        }else{
                            $.dialog({title: 'Erro', text: data});
                        }

                    }
                });    
            });
        });
    </script>
    <table class="ui-widget ui-widget-content" id="tabPedidos">
        <thead>
            <tr class="ui-widget-header">
                <th>Programas</th>
                <th style="text-align: center;">Permitir</th>
            </tr>
        </thead>
        <tbody>
        <?php getChildren($mainConnection, 0, $_POST["usuario"], 0); ?>
    </tbody>
</table>
<div style="text-align: right; margin-top: 5px;"><input type="button" id="btnAlterar" value="Alterar" class="button" /></div>
<?php
    }
?>