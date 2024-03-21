<?php
require_once("custom/php/common.php");
VoltarAtras();
global $link;
$link = init_page('manage_unit_types');
if ($link == NULL)
{
    echo "Não tem autorização para aceder a esta página";
}
else
{
    if (isset($_REQUEST['estado']) == false)
    {
        $querysubitemunittype = "SELECT name, id FROM subitem_unit_type";
        $resultadosubitemunit = mysqli_query($link,$querysubitemunittype);
        if($resultadosubitemunit==NULL)
        {
            echo "Não há tipos de unidades";
        }
        else
        {
            echo
            '<table>
            <thead>
            <tr>
            <th>id</th>
            <th>unidade</th>
            <th>subitem</th>
            <th>ação</th>
            </tr>
            </thead>
            <tbody>';
            while ($colunasdaquerysubunit = mysqli_fetch_assoc($resultadosubitemunit))
            {
                $querysubitemname = "SELECT id,name,item_id, unit_type_id FROM subitem WHERE unit_type_id LIKE '$colunasdaquerysubunit[id]'";
                $resultadosubitemname = mysqli_query($link,$querysubitemname);
                $resultadotamanho = mysqli_query($link, $querysubitemname);
                $colunasquerytamanho = mysqli_fetch_assoc($resultadotamanho);
                $todasascolunas = mysqli_fetch_all($resultadotamanho, 1);
                $column_size = count(array_column($todasascolunas, 'id'));
                echo
                    "<tr>
                    <td>{$colunasdaquerysubunit['id']}</td>
                    <td>{$colunasdaquerysubunit['name']}</td>
                    <td>";
                    while($colunasquerysubitemname = mysqli_fetch_assoc($resultadosubitemname))
                    {
                        $queryitemid = "SELECT name, id FROM item WHERE id LIKE'$colunasquerysubitemname[item_id]'GROUP BY name ";
                        $resultadoqueryitemid = mysqli_query($link, $queryitemid);

                        while( $colunasqueryitemid = mysqli_fetch_assoc($resultadoqueryitemid))
                        {
                            if( $column_size > 0)
                            {
                                 echo "$colunasquerysubitemname[name]($colunasqueryitemid[name]),";
                            }
                            else
                            {
                                 echo "$colunasquerysubitemname[name]($colunasqueryitemid[name])";
                            }
                            $column_size--;
                        }
                    }
               echo
                    "</td>
                    <td>
                    <a href=" . $current_page . '?estado=editar&item=' . $colunasdaquerysubunit['id'] . ">
                    [editar]
                    </a>
                    <a href=" . $current_page . '?estado=apagar&item=' . $colunasdaquerysubunit['id'] . ">
                    [apagar]
                    </a>
                    </td>
                    </tr>";
            }
            echo '</tbody></table>';
            echo '<style>
                    red{
                       color: red;
                      }
                  </style>';
            echo
                "<h3>Gestão de unidades - introdução<h3>";
            echo '<label>
                 <red>* Obrigatorio</red><br><br>';
                 //onsubmit="return FormValidacaoReg(this)"
            echo '<form onsubmit="return validargestaounidades(this)">
                 <label for="nome">
                     Nome da unidade:<red>*</red></label>
                 <input
                     type="text" id="nomeunidade" name="nomeunidade">
                 <button
                     type="submit">Submeter</button>
                 <input
                     type="hidden" name="estado" value="inserir">
                 </form>';


            if ( isset($_request["nomeunidade"]) )
            {
                $_session['nomeunidade'] = $_request['nomeunidade'];
            } 
        }
    }
    else if($_REQUEST['estado'] == 'inserir')
    {
        if(preg_match("/^[a-zA-Z]+[0-9]+/", $_REQUEST['nomeunidade']) ||
           preg_match("/^[a-zA-Z]+/", $_REQUEST['nomeunidade']) ||
           preg_match("/^[ a-zA-Z\\ ]+$/", $_REQUEST['nomeunidade']) ) //validar com um padrao decente
        {
            echo
                "<h3><strong>Gestão de unidades - introdução</strong><h3>";
            $queryinserirunidade = "INSERT INTO `subitem_unit_type` (`id`,`name`)
                                    VALUES ( NULL , '$_REQUEST[nomeunidade]')";
            $resultadoinserir = mysqli_query($link, $queryinserirunidade);
            if ($resultadoinserir)
            {
                echo"<p>
                     Inseriu a unidade:$_REQUEST[nomeunidade]
                     </p>";
                echo "Inseriu os dados de um novo tipo de unidade com sucesso! Clique em continuar para anvançar";
                echo "<form action='http://localhost/sgbd/gestao-de-unidades/'>
                     <input type='submit' value='Continuar'>
                     </form>";
            }
            else
            {
                echo "<h2>
                     <strong>
                     Erro de inserção
                     </strong>
                     <h2>";
                echo "<a href='/sgbd/gestao-de-unidades'>";
                echo "<h3>
                     Voltar a tentar
                     <h3>";
            }
        }
        else
        {
            $_SESSION['nomeunidade_vazio'] = "Insira um nome de uma unidade valida";
            echo $_SESSION['nomeunidade_vazio'];
        }
    }
}
?>
<script src="/sgbd/custom/js/script.js"></script>