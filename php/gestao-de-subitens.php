<?php

require_once("common.php");

$connection = init_page("manage_subitems");

echo "<p><hr></p>";

//Caso seja recebido o estado via POST, se não houverem erros no formulário de inserção, guarda-o na váriavel $estado, caso contrário a váriavel é inicializada com o valor "consulta" (mostra a tabela)
if(isset($_POST["estado"]))
    if(!(preg_match('/^[A-Za-zÀ-úçÇ\s]+$/u', $_POST["subName"]))) {
        VoltarAtras();
        $estado = "";
        echo "<h3>Input inválido para 'nome do subitem'. Por favor insira um nome válido.<h3>";
    }
    elseif(!(is_numeric($_POST["ffo"]) && intval($_POST["ffo"]) > 0)) {
        VoltarAtras();
        $estado = "";
        echo "<h3>Input inválido para 'ordem do campo no formulário'. Por favor insira um inteiro maior do que 0.<h3>";
    }
    else {
        $estado = $_POST["estado"];
    }

else
	$estado = "consulta";

//Mostra a tabela de Gestão de subitens e o formulário para inserir um novo subitem
if($estado == "consulta") {

    //SQL para selecionar todos os item.name e ordena por nome do item
    $sqlItens = "select distinct i.name from item as i order by i.name";
    $resItens = mysqli_query($connection, $sqlItens);

    //SQL para selecionar todos os valores dos subitens e ordenar por nome do item e id do subitem
    $sqlSub = "select s.id, s.name, s.value_type, s.form_field_name, s.form_field_type, s.unit_type_id, s.form_field_order, s.mandatory, s.state
    from subitem as s, item as i where i.id = s.item_id order by i.name, s.form_field_order, s.id";
    $resSub = mysqli_query($connection, $sqlSub);

    //SQL para selecionar todos o subitem_unit_type.name e ordenar por nome do item e id do subitem
    $sqlUnitType = "select sut.name from subitem as s, subitem_unit_type as sut, item as i where i.id = s.item_id and s.unit_type_id = sut.id order by i.name, s.form_field_order, s.id";
    $resUnitType = mysqli_query($connection, $sqlUnitType);

    if(mysqli_num_rows($resItens) > 0) {
        /*
			Se existem items, mostra uma tabela HTML com 11 colunas (TD):
			item.name, subitem.id, subitem.name, subitem.value_type, subitem.form_field_name, subitem.form_field_type,
            subitem_unit_type.name, subitem.form_field_order, subitem.mandatory, subitem.state
			e uma coluna com ações (editar, desativar e apagar).
		*/

	    echo'
		<table class="mytable" style="text-align: left; width: 100%;" border="1" cellpadding="2" cellspacing="2">
		    <tr>
		    <th>item</th>
			<th>id</th>
			<th>subitem</th>
			<th>tipo de valor</th>
			<th>nome do campo no formulário</th>
			<th>tipo do campo no formulário</th>
			<th>tipo de unidade</th>
            <th>ordem do campo no formulário</th>
            <th>obrigatório</th>
            <th>estado</th>
            <th>ação</th>
		    </tr>
        ';

        while ($rowItens = mysqli_fetch_assoc($resItens)) {

            //SQL para obter todos os subitem.id de um item para o rowspan do mesmo
            $sqliItem = "select s.id from subitem as s, item as i where i.id = s.item_id and i.name = '" . $rowItens["name"] . "'";
            $resItem = mysqli_query($connection, $sqliItem);
            $rowspanItem = mysqli_num_rows($resItem);
            
            //Se o item em questão não tem subitens...
            if($rowspanItem == 0) {
                echo "<tr'>";
                echo "<td>" . $rowItens["name"] . "</td>";
                echo "<td colspan = 10 style='text-align: center'>Este item não tem subitens</td>";
                echo "</tr>";
            }
            //Caso contrário, a célula na coluna "item" terá um rowspan equivalente ao número de subitens que possui
            else {
                echo "<tr>";
                echo "<td rowspan =" . $rowspanItem . ">" . $rowItens["name"] . "</td>";
    
                //Coloca os atributos de um subitem do item ao longo da linha na tabela. Repete o processo um número de vezes equivalente ao rowspan do item 
                for($num = 0; $num < $rowspanItem; $num++) {
                    $rowSub = mysqli_fetch_assoc($resSub);
                    echo "<td>" . $rowSub["id"] . "</td>";
                    echo "<td>" . $rowSub["name"] . "</td>";
                    echo "<td>" . $rowSub["value_type"] . "</td>";
                    echo "<td>" . $rowSub["form_field_name"] . "</td>";
                    echo "<td>" . $rowSub["form_field_type"] . "</td>";
                    
                    //Se o subitem tiver unit_type_id...
                    if($rowSub["unit_type_id"]) {
                        $rowUnitType = mysqli_fetch_assoc($resUnitType);
                        echo "<td>" . $rowUnitType["name"] . "</td>";
                    }
                    else
                        echo "<td>-</td>";

                    echo "<td>" . $rowSub["form_field_order"] . "</td>";
                    echo "<td>" . $rowSub["mandatory"] . "</td>";
                    echo "<td>" . $rowSub["state"] . "</td>";
                    echo "<td>" . generate_change_state_button($connection, "subitem", $rowSub["id"]) . generate_edit_data_buttons($connection, "subitem", $rowSub["id"])."</td>";
                    echo "</tr>"; 
                }
            }
        }
        
        echo "</table>";

        //Gestão de subitems - introdução
        echo "<h3><p><hr>Gestão de subitems - introdução</p></h3>";

        //Array com os tipos de valor da tabela "subitem" para preencher os inputs do radio "Tipo de valor"
        $valueTypes = get_enum_values($connection, "subitem", "value_type");

        //Array com os tipos de campo do formulário da tabela "subitem" para preencher os inputs do radio "Tipo do campo do formulário"
        $formTypes = get_enum_values($connection, "subitem", "form_field_type");

        //SQL para selecionar os nomes e ids dos items para preencher as options do selectbox "Item" 
        $sqlItensForm = "select distinct i.name, i.id from item as i order by i.name";
        $resItensForm = mysqli_query($connection, $sqlItensForm);

        //SQL para selecionar os nomes e ids dos subitem_unit_types para preencher as options do selectbox "Tipo de unidade"
        $sqlUnitTypeForm = "select id, name from subitem_unit_type";
        $resUnitTypeForm = mysqli_query($connection, $sqlUnitTypeForm);

        echo'
        <form action="" method="post">
        Nome do subitem (obrigatório): 
            <input type="text" name="subName" value="" required><br><br>

        Tipo de valor (obrigatório):<br>';
            foreach ($valueTypes as $type) {
                echo '<input type="radio" name="value_type" value="' . $type . '" required>' . $type . '<br>';
           };

        //NOTA: é guardado o nome e id do item escolhido
        echo'
        <br>
        Item (obrigatório):
            <select name="item" required>
            <option value=""></option>';
            while($rowItensForm = mysqli_fetch_assoc($resItensForm)) {
                echo '<option value="' . $rowItensForm["name"] . '|' . $rowItensForm["id"] . '">' . $rowItensForm["name"] . '</option>';
            };
            echo "</select><br><br>";

        echo'
        Tipo do campo do formulário (obrigatório):<br>';
            foreach ($formTypes as $type) {
                echo '<input type="radio" name="fft" value="' . $type . '" required>' . $type . '<br>';
            };

        //NOTA: é guardado o nome e id do unit_type escolhido
        echo'
        <br>
        Tipo de unidade:
            <select name="unitType">;
            <option value=""></option>';
            while($rowUnitTypeForm = mysqli_fetch_assoc($resUnitTypeForm)) {
                echo '<option value="' . $rowUnitTypeForm["name"] . '|' . $rowUnitTypeForm["id"] . '">' . $rowUnitTypeForm["name"] . '</option>';
            };
            echo "</select><br><br>";

        echo'
        Ordem do campo no formulário (obrigatório e um número superior a 0): 
        <input type="text" name="ffo" value="" required><br><br>';

        echo'
        Obrigatório (obrigatório):<br>
            <input type="radio" name="mandatory" value="1" required>1<br>
            <input type="radio" name="mandatory" value="0" required>0<br>
        ';
            
        echo'
            <input type="hidden" name="estado" value="inserir"><br>
            <input type="submit" value="Inserir subitem">
        </form>
        <br><br>
        ';
    }
    else 
        echo "<h3>Não há itens</h3>";	//Mostra uma mensagem caso não exista nenhum item na BD
}

//Insere o novo subitem na BD e mostra uma mensagem de sucesso ou erro
if($estado == "inserir") {

    //Variáveis com os dados da submissão
    $subName = $_POST["subName"];
    $value_type = $_POST["value_type"];
    $fft = $_POST["fft"];

    //Como na submissão, o nome e id do item estão na mesma string (ex: medidas|1) é necessário separá-los em duas variáveis
    $itemID = preg_replace('/[^0-9]/', '', $_POST["item"]);
    $itemName = preg_replace('/[^A-Za-zÀ-úçÇ\s]+$/u', '', $_POST["item"]);

    if($_POST["unitType"])
        $unitTypeID = preg_replace('/[^0-9]/', '', $_POST["unitType"]); //Separa o id do unit_type do seu nome se não for NULL
    else
        $unitTypeID = "NULL";

    $ffo = $_POST["ffo"];
    $mandatory = $_POST["mandatory"];

    //Retira as 3 primeiras letras do nome do item para usar no form_field_name (ffn)
    $ffn = preg_replace('/[^a-zA-Z]/', '', substr($itemName, 0, 3));

    echo "<p><h3>Gestão de subitens - inserção</h3></p>";

    //SQL que insere na BD um subitem com todos os atributos selecionados (com exceção do ffn) 
    $sqlInsert = "insert into subitem VALUES(0, '" . $subName . "', " . $itemID . ", '" . $value_type . "', '', '" . $fft . "', " . $unitTypeID . ", " . $ffo . ", " . $mandatory . ", 'active')";

    //Se a inserção foi bem sucedida...
    if (mysqli_query($connection, $sqlInsert)) {

        //Adquire o id gerado pelo "insert" mais recente
        $newSubID = mysqli_insert_id($connection);

        //Remove os acentos e os espaços do nome do subitem
        $removedChar = ["'", '"', "~", "^", "`"];
        $subName = iconv('UTF-8','ASCII//TRANSLIT', $subName);
        $subName = str_replace($removedChar, "", $subName);
        $subName = str_replace(" ", "_", $subName);

        //Adiciona à string "ffn" o id do novo subitem e o seu nome 
        $ffn = $ffn . "-" . $newSubID . "-" . $subName;

        //SQL que adiciona o atributo "ffn" completo ao novo subitem com um "update" 
        $sqlUpdate = "update subitem set form_field_name = '" . $ffn . "' where subitem.id = " . $newSubID . "";

        //Se o update foi bem sucedido...
        if(mysqli_query($connection, $sqlUpdate)) {
            echo "Inseriu o subitem com sucesso.<br>";
            VoltarAtras();
        }
        else {
            echo "Erro: " . $sqlUpdate . "<br>" . $connection->error . "<br>";
            VoltarAtras();
        }
	}
	else {
		echo "Erro: " . $sqlInsert . "<br>" . $connection->error . "<br>";
        VoltarAtras();
    }
}
?>