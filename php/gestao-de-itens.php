<?php
require_once("custom/php/common.php");
VoltarAtras();
$connection = init_page("manage_items");
/*Validar*/
if(isset($_REQUEST["estado"])){
    if ($_REQUEST["estado"] == "inserir")
    {
        

        if(FormularioErrosItems())
        {
            echo "<hr>";
            echo "<h3> Gestão de Itens - Inserção</h3>";
            echo "<hr>";
            echo "Estamos prestes a inserir os dados abaixo na base de dados.
            Confirma que os dados estão correctos e pretende submeter os mesmos?"."<br><br>";
            echo "Nome do item: ".$_REQUEST["nome_item"]."<br>";

            echo "Tipo de item: ".$_REQUEST["item_tipo"]."<br>";

            echo "Estado: ".$_REQUEST["item_estado"]."<br><br>";

            echo '
            <form>
            <input type="hidden" name="estado" value="inserirfinal">
            <input type="hidden" name="nome_item" value="' . $_REQUEST["nome_item"] . '">
            <input type="hidden" name="item_tipo" value="' . $_REQUEST["item_tipo"] . '">
            <input type="hidden" name="item_estado" value="' . $_REQUEST["item_estado"] . '">
            <input type="submit" value="Submeter">';
            

        }
    }
    if ($_REQUEST["estado"] == "inserirfinal")
    {
        if(FormularioErrosItems())
        {
            $_REQUEST['nome_item']=mysqli_real_escape_string($connection,$_REQUEST['nome_item']);
            $_REQUEST['item_tipo']=mysqli_real_escape_string($connection,$_REQUEST['item_tipo']);
            $_REQUEST['item_estado']=mysqli_real_escape_string($connection,$_REQUEST['item_estado']);
            $colunaidnew = (int) MAXID($connection) + 1;
            $sqlinserir = "INSERT INTO `item` (`id`, `name`, `item_type_id`, `state`) 
            VALUES ('$colunaidnew', '$_REQUEST[nome_item]', '$_REQUEST[item_tipo]', '$_REQUEST[item_estado]')";

            $resultado = mysqli_query($connection, $sqlinserir);
        if($resultado)
        {
            echo "Inseriu os dados com sucesso<p></p>";
            echo "<a href=/sgbd/gestao-de-itens>Clique em Continuar para avançar</a>";
        }
        else
        {
            echo "Erro de inserção";
        }
        }
    }
}
else{
    ItemsTabela($connection,$current_page);
    echo "<p></p><hr>";
    echo '<h3>Gestão de itens - Introdução</h3>';
    echo "<p></p><hr>";
    FormularioItems($connection);

}
function FormularioItems($connection){
    echo '
    <form onsubmit="return formValidacaoItems(this)">
        <label for="nome">Nome:</label><br>
        <input placeholder="ITEM" type="text" id="nome_item" name="nome_item">';
    echo "<br>";
    echo "<br>";
        echo'<label for="item_tipo">Tipo:</label><br>';
        
            $sqlitemtypename = "SELECT * from item_type;";
            $result = mysqli_query($connection, $sqlitemtypename);
    
        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){     
                echo '<input type="radio" id="item_tipo" name="item_tipo" value="' . $row['id'] . '">' . $row['name'] . '<br>';
            }
        }

        echo '<br><label for="estado">Estado:<br></label>
        <input type="radio" id="item_estado0" name="item_estado" value="active"> Ativo<br>
        <input type="radio" id="item_estado1" name="item_estado" value="inactive"> Inativo<br>

        <input type="hidden" name="estado" value="inserir"><br>
        <br><br><input type="submit" value="Submeter">
    </form>';
}

function NumeroItemTypes($connection){
    $sqltype="select count(item_type.name) from item_type;";
    $result = mysqli_query($connection, $sqltype);
    if (mysqli_num_rows($result) > 0){
        $NITEMS = mysqli_fetch_row($result);
    }
    return $NITEMS[0];
}
function ItemsTabela($connection, $current_page)
{
    $numtipositems = NumeroItemTypes($connection);

    if ($numtipositems > 0) {
        echo '<table><thead>
        <tr>
            <th>Tipo de item</th>
            <th>ID</th>
            <th>Nome do item</th>
            <th>Estado</th>
            <th>Ação</th>
        </tr>
            </thead><tbody>';
        //item_type.name organizado alfabeticamente
        $sqlnometiposnomes = "SELECT item_type.name FROM item_type ORDER BY item_type.name ASC;";
        $resultnometiposnomes = mysqli_query($connection, $sqlnometiposnomes);
        
        while ($rownometiposnomes = mysqli_fetch_assoc($resultnometiposnomes)) {
            //seleciona item em que o item_type_id é igual ao nome do item_type
            
            $sqlItems = "SELECT * FROM item WHERE item_type_id = (SELECT id FROM item_type WHERE name = '$rownometiposnomes[name]');";
            $resultItems = mysqli_query($connection, $sqlItems);
            //mysqli_num_rows conta o numero de rows que será usado no rowspan para a coluna rowspan ficar certa
            $numItems = mysqli_num_rows($resultItems);

            echo "<tr>
                    <td colspan=1 rowspan='$numItems'>$rownometiposnomes[name]</td>";

                if ($numItems > 0) {
                    
                    while ($rowItem = mysqli_fetch_assoc($resultItems)) {
                        echo "<td>{$rowItem['id']}</td>
                            <td>{$rowItem['name']}</td>
                            <td>{$rowItem['state']}</td>
                            <td><a href=" . $current_page . '?estado=editar&item=' . $rowItem["id"] . ">[editar]</a>
                                    <a href=" . $current_page . '?estado=desativar&item=' . $rowItem["id"] . ">[desativar]</a>
                                    <a href=" . $current_page . '?estado=apagar&item=' . $rowItem["id"] . ">[apagar]</a></td>
                            </tr>";
                        }
                    }
                    else {
                        
                        echo "<td colspan='4'>Não existem itens para este tipo de item</td></tr>";
                    }
                }
        
                echo '</tbody></table>';
    }
}
function MAXID($connection){
    $sqlmaxid="SELECT MAX(item.id) FROM `item`;";
    $sqlmaxidresultado = mysqli_query($connection, $sqlmaxid);
    if (mysqli_num_rows($sqlmaxidresultado) > 0)
    {
        $MAXID_array = mysqli_fetch_row($sqlmaxidresultado);
    }
    return $MAXID_array[0];
}
function ErrosNome()
        {
            if (empty($_REQUEST["nome_item"]))
            {
                $_SESSION["nome_item_err"] = "Introduza um nome";
                return true;
            }
            /*caso em que o nome tem apenas letras podendo conter espaços*/
            elseif (!preg_match("/^[\p{L}\s]+$/u", $_REQUEST["nome_item"]))
            {
                $_SESSION["nome_item_err"] = "Introduza um nome válido";
                return true;
            } 
            /*valido*/
            else 
            {
                return false;
            }
        }
    function ErrosItemTipo()
    {
        if(empty($_REQUEST["item_tipo"]))
        {
            $_SESSION["item_tipo_err"] = "Escolha um tipo";
            return true;
        }
    }

    function ErrosAtivoDesativado()
    {
        if(empty($_REQUEST["item_estado"]))
        {
            $_SESSION["item_estado_err"] = "Escolha um estado";
            return true;
            
        }
    }

    function FormularioErrosItems()
    {
        if (ErrosNome())
        {
        echo $_SESSION["nome_item_err"];
        echo "<p></p>";
        }
        if(ErrosItemTipo())
        {
            echo $_SESSION["item_tipo_err"];
            echo "<p></p>";

        }
        if(ErrosAtivoDesativado())
        {
            echo $_SESSION["item_estado_err"];
            echo "<p></p>";

        }
        elseif(!ErrosNome() && !ErrosItemTipo() && !ErrosAtivoDesativado())
        {
        return true;
        }
    
    }

?>
<script src="/sgbd/custom/js/script.js"></script>