<?php

require_once("common.php");

$connection = init_page("manage_allowed_values");

echo "<p><hr></p>";

//Caso seja recebido o estado via REQUEST, guarda-o na váriavel $estado, caso contrário a váriavel é inicializada com o valor "consulta" (mostra a tabela)
if(isset($_REQUEST["estado"]))
	$estado = $_REQUEST["estado"];
else
	$estado = "consulta";
	
//Mostra a tabela de Gestão de valores permitidos
if($estado == "consulta") {

	//SQL para selecionar todos os items, cujo "value_type" seja "enum" e ordena por nome do item
	$sql = "select distinct i.id, i.name from item as i, subitem as s where i.id = s.item_id and s.value_type = 'enum' order by i.name;";
	$res = mysqli_query($connection, $sql);
	
	if(mysqli_num_rows($res) > 0)
	{
		/*
			Se existem items, mostra uma tabela HTML com 7 colunas (TD):
			item.name, subitem.id, subitem.name, subitem_allowed_value.id, subitem_allowed_value.value, subitem_allowed_value.state
			e uma coluna com ações (editar, desativar e apagar).
		*/
		echo'
		<table class="mytable" style="text-align: left; width: 100%;" border="1" cellpadding="2" cellspacing="2">
		    <tr>
		    <th>item</th>
			<th>id</th>
			<th>subitem</th>
			<th>id</th>
			<th>valores permitidos</th>
			<th>estado</th>
			<th>ação</th>
		    </tr>
		';
		
		// Ciclo que vai ser executado para cada item...
		while($row = mysqli_fetch_assoc($res)){
			
			//Variavel que vai controlar se já foi preenchido o nome do item (fica a 1). Para cada item novo (novo nome), vai inicializar o seu valor a 0
			$col1 = 0;
			
			//SQL para contar quantos "valores" o item atual tem (necessário para o rowspan da coluna "item")
			$sqlValues = "select sav.value from item as i, subitem_allowed_value as sav right join subitem as s on sav.subitem_id = s.id where i.id = s.item_id and s.value_type = 'enum' and i.id ='" . $row["id"] . "'";
			$resValues = mysqli_query($connection, $sqlValues);
			$countValues = mysqli_num_rows($resValues);
			
			$sqlSub = "select id, name from subitem where value_type = 'enum' and item_id=".$row["id"] . " order by name";
			$resSub = mysqli_query($connection, $sqlSub);

			// Ciclo que vai ser executado para cada subitem...
			while($rowSub = mysqli_fetch_assoc($resSub)) {
				
				//Variavel que vai controlar se já foram preenchidos o id e nome do subitem (fica a 1). Para cada subitem novo, vai inicializar o seu valor a 0
				$col2 = 0;
				
				//SQL para contar quantos "valores" existem para o subitem atual.
				$sqlSubValues = "select id from subitem_allowed_value where subitem_id = " . $rowSub["id"];				
				$resSubValues = mysqli_query($connection, $sqlSubValues);
				$countSubValues = mysqli_num_rows($resSubValues);
				
				/*
				 SQL para selecionar o id, value e state da tabela "subitem_allowed_value" para o subitem atual.
				 É utilizado o RIGHT JOIN para que no caso de não existirem valores, retornar um registo com valor NULL
				*/
				$sqlSAV = "select sav.id, sav.value, sav.state from subitem_allowed_value as sav right join subitem as s
				on sav.subitem_id = s.id where s.id=" . $rowSub["id"] . " order by sav.id";
				$resSAV = mysqli_query($connection, $sqlSAV);
				
				while($rowSAV = mysqli_fetch_assoc($resSAV)) {
					echo "<tr>";	//Abre a TAG HTML de uma linha da tabela de consulta
					
					//Lógica que permite fazer o rowspan para o item (coluna 1 da tabela HTML)
					if($col1 == 0) {
						/*
						 caso não existam valores ($countValues == 0) usamos o valor 1 como rowspan
						 caso existam valores ($countValues > 0) usamos então esse valor
						 
						 NOTA: O aspecto da tabela HTML fica corrompido caso o valor do rowspan seja 0
						*/
						$rowSpan = $countValues == 0 ? 1 : $countValues;
					
						echo "<td rowspan=" . $rowSpan. ">" . $row["name"] . "</td>";

						$col1 = 1;
					}
					
					//Lógica que permite fazer o rowspan para o id e subitem (colunas 2 e 3 da tabela HTML)
					if($col2 == 0) {
						/*
						 caso não existam valores ($countSubValues == 0) usamos o valor 1 como rowspan
						 caso existam valores ($countSubValues > 0) usamos então esse valor
						 
						 NOTA: O aspecto da tabela HTML fica corrompido caso o valor do rowspan seja 0
						*/
						$rowSpan = $countSubValues == 0 ? 1 : $countSubValues;
								
						echo "<td rowspan=" . $rowSpan . ">" . $rowSub["id"] . "</td>";
						echo "<td rowspan=" . $rowSpan . "><a href='" .$current_page. "?estado=introducao&subitem_id=" . $rowSub["id"] . "'>[" . $rowSub["name"] . "]</a></td>";
						
						$col2 = 1;
					}
					
					//Testa se o subitem tem valores ou não e mostra-os ou mostra uma mensagem a informar que não existem...
					if ($rowSAV['id'] == NULL) {
						echo "<td colspan=4>Não há valores permitidos definidos</td>";
					}
					else {
						echo "<td>" . $rowSAV["id"] . "</td>";
						echo "<td>" . $rowSAV["value"] . "</td>";
						echo "<td>" . $rowSAV["state"] . "</td>";
                        echo "<td>" . generate_change_state_button($connection, "subitem_allowed_value", $rowSAV["id"]) . generate_edit_data_buttons($connection, "subitem_allowed_value", $rowSAV["id"]) . "</td>";
					}
					
					echo "</tr>";	//Fecha a TAG HTML de uma linha da tabela de consulta
					
				}	//Fim do While dos valores permitidos
				
			}	//Fim do While para os subitems
				 
		}	//Fim do While para os items
		
		echo "</table>";	//Fecha a TAG HTML da tabela de consulta
	}
	else 
		echo "<h3>Não há itens</h3>";	//Mostra uma mensagem caso não exista nenhum item na BD
}

/*
Mostra formulário para introdução de um novo valor permitido para um determinado subitem.
O valor do subitem é passado por GET e guardado numa váriavel de sessão.
*/	
if($estado == "introducao") {
	
	$_SESSION["subitem_id"] = $_REQUEST["subitem_id"];

	echo "<h3> Gestão de valores permitidos - introdução</h3>";
	
	echo'
	<form action="" method="get">
		Valor (obrigatório): <input type="text" name="valor" value=""> <br>
		<input type="hidden" name="estado" value="inserir"> <br>
		<input type="submit" value="Inserir valor permitido">
	</form>
	<br><br>
	';
}

/*
Grava na BD o novo valor permitido para o subitem identificado na váriavel de sessão "subitem_id".
Caso a gravação ocorra com sucesso, mostra uma mensagem e um link para mostrar novamente todos os registos.
*/
if($estado == "inserir") {

	echo "<h3> Gestão de valores permitidos - inserção</h3>";

	$sqlquery = "insert into subitem_allowed_value VALUES(0, " . $_SESSION["subitem_id"] . ",'" . $_GET['valor'] . "','active')";
	
	if (mysqli_query($connection, $sqlquery)) {
		echo "Inseriu os dados de novo valor permitido com sucesso.<br>";
		echo "Clique em <a href='".$current_page."'>Continuar</a> para avançar<br>";	
	}
	else 
		echo "Error: " . $sqlquery . "<br>" . $connection->error;
}

?>