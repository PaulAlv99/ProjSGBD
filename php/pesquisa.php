<?php

require_once("common.php");

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$connection = init_page("search");

if(isset($_REQUEST["estado"])) {
	echo "<a href='#' onclick='history.back()'><< Regressar à página anterior</a>";
	echo "<p><hr></p>";

	$estado = $_REQUEST["estado"];
	
	if(isset($_REQUEST["it_id"])) {
		$_SESSION["item_id"] = $_REQUEST["it_id"];
		//echo "<br>item_id:" . $_SESSION["item_id"];
	}

	if(isset($_REQUEST["it_nome"])) {
		$_SESSION["item_nome"] = $_REQUEST["it_nome"];
		//echo "<br>item_nome:" . $_SESSION["item_nome"];
	}
	
	if(isset($_REQUEST["atributos"])) {
		$_SESSION["atributos"] = $_REQUEST["atributos"];
		//echo "<br>atributo:" . $_SESSION["atributos"];
	}
	
	if(isset($_REQUEST["subitems"])) {
		$_SESSION["subitems"] = $_REQUEST["subitems"];
		//echo "<br>subitem:" . $_SESSION["subitems"];
	}

	if ($estado == "escolha") {
		echo '<h3>Pesquisa - formulário</h3>';
		show_form($connection);
	}
	
	if ($estado == "escolher_filtros") {
		echo "<h3>Pesquisa - escolher filtros</h3>";
		show_form_filtros($connection);
	}
	
	if ($estado == "execucao")
		show_execucao($connection);
}
else
{
	echo "<p><hr></p>";
	echo '<h3>Pesquisa - escolher item</h3>';
	
	$estado = "escolha";
	show_item_list($connection);
}

function show_item_list($connection): void{

	$itemTypeQuery = "select distinct item_type.id, item_type.name from item_type inner join item on item.item_type_id = item_type.id";

	$listItemTypes = mysqli_query($connection, $itemTypeQuery);

	echo '<ul>';

	while($itemType = mysqli_fetch_assoc($listItemTypes)){
		$itemQuery = "select id, name from item where state = 'active' and item_type_id = " . $itemType['id'];
		$listItems = mysqli_query($connection, $itemQuery);

		echo'<li>'. $itemType['name'] .'<ul>';
		
		while($item = mysqli_fetch_assoc($listItems)){
			echo "<li><a href='". $GLOBALS['current_page'] . "?estado=escolha&it_nome=". $item['name'] . "&it_id=" . $item['id'] . "'>[". $item['name'] ."]</a></li>";
		}
		
		echo'</li></ul>';

	}
	
	echo '</ul>';
}

function show_form($connection): void{

	$sql = "select s.id, s.name from subitem as s where s.item_id = " . $_SESSION["item_id"] . " order by s.name;";
	$res = mysqli_query($connection, $sql);
	
	$itemID = $_SESSION["item_id"];
	
	echo "<form action='" . $GLOBALS['current_page'] . "' method='post'>";
	
	echo'
	<table class="mytable" style="text-align: left; width: 500px;" border="1" cellpadding="2" cellspacing="2">
	    <tr>
	    	<th>Atributo</th>
			<th>Obter</th>
			<th>Filtro</th>
	    </tr>
	    <tr>
	        <td>id</td>
	    	<td><input type="checkbox" name="id[]" value="obter"></td>
	    	<td><input type="checkbox" name="id[]" value="filtro"></td>
	    </tr>
	    <tr>
	    	<td>name</td>
	    	<td><input type="checkbox" name="name[]" value="obter"></td>
	    	<td><input type="checkbox" name="name[]" value="filtro"></td>
	    </tr>
	    <tr>
	    	<td>birth_date</td>
	    	<td><input type="checkbox" name="birth_date[]" value="obter"></td>
	    	<td><input type="checkbox" name="birth_date[]" value="filtro"></td>
	    </tr>
	    <tr>
	    	<td>tutor_name</td>
	    	<td><input type="checkbox" name="tutor_name[]" value="obter"></td>
	    	<td><input type="checkbox" name="tutor_name[]" value="filtro"></td>
	    </tr>
	    <tr>
	    	<td>tutor_phone</td>
	    	<td><input type="checkbox" name="tutor_phone[]" value="obter"></td>
	    	<td><input type="checkbox" name="tutor_phone[]" value="filtro"></td>
	    </tr>
	    <tr>
	    	<td>tutor_email</td>
	    	<td><input type="checkbox" name="tutor_email[]" value="obter"></td>
	    	<td><input type="checkbox" name="tutor_email[]" value="filtro"></td>
	    </tr>
	</table>
	';
	
	echo'
	<table class="mytable" style="text-align: left; width: 500px;" border="1" cellpadding="2" cellspacing="2">
	    <tr>
	    	<th>Subitem</th>
			<th>Obter</th>
			<th>Filtro</th>
	    </tr>
	';
	
	while($row = mysqli_fetch_assoc($res)){
		echo "<tr>";
		echo "<td>" . $row["name"] . "</td>";
		echo "<td><input type='checkbox' name='". $row["name"] ."[]' value='obter'></td>";
		echo "<td><input type='checkbox' name='". $row["name"] ."[]' value='filtro'></td>";
		echo "</tr>";
	}
	
	echo "</table>";
	
	echo "<input type='hidden' name='estado' value='escolher_filtros'>";
	echo "<input type='hidden' name='it_nome' value='" . $_SESSION['item_nome'] . "'>";
	echo "<input type='hidden' name='it_id' value='" . $_SESSION['item_id'] . "'>";
	echo "<input type='submit' name='submit' value='Continuar'>";
	echo "</form>";
}

function show_form_filtros($connection): void{
	$item_nome = $_SESSION["item_nome"];
	$pagina = $GLOBALS["current_page"];
	
	$_SESSION["atributos"] = "";
	$_SESSION["subitems"] = "";
	
	echo'
	<span style="color: red; font-weight: bold;">*Campos obrigatórios</span></br>
	<span style="color: teal; font-weight: bold;">Irá ser realizada uma pesquisa que irá obter, como resultado, uma listagem, para cada criança, dos seguintes dados pessoais escolhidos:</span></br>
	
	<form action="' . $pagina . '" method="post">
	
	<table class="mytable" style="text-align: left; width: 800px;" border="1" cellpadding="2" cellspacing="2">
	';
	
	//Atributo "id"
	if(isset($_REQUEST["id"])) {
		echo "<tr>";
	    if ($_REQUEST["id"][0] == "obter") {
			echo "<td>id</td>";
			$_SESSION["atributos"] .= "id|";
		}
		else
			echo "<td>id (filtro)</td>";
		
		if ($_REQUEST["id"][0] == "filtro") {
			echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("int","id_op") . "</td>";
			echo "<td>id<span style='color: red;'>*</span></br><input type='text' name='id' required></td>";
		}
		else
			if(isset($_REQUEST["id"][1])) {
				if ($_REQUEST["id"][1] == "filtro") {
					echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("int","id_op") . "</td>";
					echo "<td>id<span style='color: red;'>*</span></br><input type='text' name='id' required></td>";
				}
			}
			else {	//Se não for escolhido filtrar, mostra as últimas duas colunas vazias...
				echo "<td></td><td></td>";
			}
		echo "</tr>";
	}
	
	//Atributo "name"
	if(isset($_REQUEST["name"])) {
		echo "<tr>";
	    if ($_REQUEST["name"][0] == "obter") {
			echo "<td>name</td>";
			$_SESSION["atributos"] .= "name|";
		}
		else
			echo "<td>name (filtro)</td>";
		
		if ($_REQUEST["name"][0] == "filtro") {
			echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("text","name_op") . "</td>";
			echo "<td>name<span style='color: red;'>*</span></br><input id='nome' type='text' name='nome' required></td>";	// o INPUT esta identificado como "nome" pq dá erro 404 ao usar "name"...
		}
		else
			if(isset($_REQUEST["name"][1])) {
				if ($_REQUEST["name"][1] == "filtro") {
					echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("text","name_op") . "</td>";
					echo "<td>name<span style='color: red;'>*</span></br><input id='nome' type='text' name='nome' required></td>"; // o INPUT esta identificado como "nome" pq dá erro 404 ao usar "name"...
				}
			}
			else {	//Se não for escolhido filtrar, mostra as últimas duas colunas vazias...
				echo "<td></td><td></td>";
			}
		echo "</tr>";
	}	


	//Atributo "birth_date"
	if(isset($_REQUEST["birth_date"])) {
		echo "<tr>";
	    if ($_REQUEST["birth_date"][0] == "obter") {
			echo "<td>birth_date</td>";
			$_SESSION["atributos"] .= "birth_date|";
		}
		else
			echo "<td>birth_date (filtro)</td>";
		
		if ($_REQUEST["birth_date"][0] == "filtro") {
			echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("int","birth_date_op") . "</td>";
			echo "<td>birth_date<span style='color: red;'>*</span></br><input type='text' name='birth_date' required></td>";
		}
		else
			if(isset($_REQUEST["birth_date"][1])) {
				if ($_REQUEST["birth_date"][1] == "filtro") {
					echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("int","birth_date_op") . "</td>";
					echo "<td>birth_date<span style='color: red;'>*</span></br><input type='text' name='birth_date' required></td>";
				}
			}
			else {	//Se não for escolhido filtrar, mostra as últimas duas colunas vazias...
				echo "<td></td><td></td>";
			}
		echo "</tr>";
	}	


	//Atributo "tutor_name"
	if(isset($_REQUEST["tutor_name"])) {
		echo "<tr>";
	    if ($_REQUEST["tutor_name"][0] == "obter") {
			echo "<td>tutor_name</td>";
			$_SESSION["atributos"] .= "tutor_name|";
		}
		else
			echo "<td>tutor_name (filtro)</td>";
		
		if ($_REQUEST["tutor_name"][0] == "filtro") {
			echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("text","tutor_name_op") . "</td>";
			echo "<td>tutor_name<span style='color: red;'>*</span></br><input type='text' name='tutor_name' required></td>";
		}
		else
			if(isset($_REQUEST["tutor_name"][1])) {
				if ($_REQUEST["tutor_name"][1] == "filtro") {
					echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("text","tutor_name_op") . "</td>";
					echo "<td>tutor_name<span style='color: red;'>*</span></br><input type='text' name='tutor_name' required></td>";
				}
			}
			else {	//Se não for escolhido filtrar, mostra as últimas duas colunas vazias...
				echo "<td></td><td></td>";
			}
		echo "</tr>";
	}	


	//Atributo "tutor_phone"
	if(isset($_REQUEST["tutor_phone"])) {
		echo "<tr>";
	    if ($_REQUEST["tutor_phone"][0] == "obter") {
			echo "<td>tutor_phone</td>";
			$_SESSION["atributos"] .= "tutor_phone|";
		}
		else
			echo "<td>tutor_phone (filtro)</td>";
		
		if ($_REQUEST["tutor_phone"][0] == "filtro") {
			echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("text","tutor_phone_op") . "</td>";
			echo "<td>tutor_phone<span style='color: red;'>*</span></br><input type='text' name='tutor_phone' required></td>";
		}
		else
			if(isset($_REQUEST["tutor_phone"][1])) {
				if ($_REQUEST["tutor_phone"][1] == "filtro") {
					echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("text","tutor_phone_op") . "</td>";
					echo "<td>tutor_phone<span style='color: red;'>*</span></br><input type='text' name='tutor_phone' required></td>";
				}
			}
			else {	//Se não for escolhido filtrar, mostra as últimas duas colunas vazias...
				echo "<td></td><td></td>";
			}
		echo "</tr>";
	}


	//Atributo "tutor_email"
	if(isset($_REQUEST["tutor_email"])) {
		echo "<tr>";
	    if ($_REQUEST["tutor_email"][0] == "obter") {
			echo "<td>tutor_email</td>";
			$_SESSION["atributos"] .= "tutor_email";
		}
		else
			echo "<td>tutor_email (filtro)</td>";
		
		if ($_REQUEST["tutor_email"][0] == "filtro") {
			echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("text","tutor_email_op") . "</td>";
			echo "<td>tutor_email<span style='color: red;'>*</span></br><input type='text' name='tutor_email' required></td>";
		}
		else
			if(isset($_REQUEST["tutor_email"][1])) {
				if ($_REQUEST["tutor_email"][1] == "filtro") {
					echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select("text","tutor_email_op") . "</td>";
					echo "<td>tutor_email<span style='color: red;'>*</span></br><input type='text' name='tutor_email' required></td>";
				}
			}
			else {	//Se não for escolhido filtrar, mostra as últimas duas colunas vazias...
				echo "<td></td><td></td>";
			}
		echo "</tr>";
	}
	
	echo <<<XXX
	</table>
	</br>
	<span style="color: teal; font-weight: bold;">e do item: * $item_nome * uma listagem dos valores dos subitems:</span></br>
	<table class="mytable" style="text-align: left; width: 800px;" border="1" cellpadding="2" cellspacing="2">
	XXX;
	
	//Array contendo os identificadores das "keys" que queremos ignorar (só queremos depois trabalhar com as keys dos subitems)
	$listaKeysIgnorar = array("id","name","birth_date","tutor_name","tutor_phone","tutor_email","estado","it_nome","it_id","submit");
	
	//Para cada chave/valor do pedido web, só listamos as relativas aos subitems...
	foreach ($_REQUEST as $key => $value) {
	
		//Se a chave atual não está no array de chaves a ignorar, então é uma chave relativa aos subitems...
		if (!in_array($key, $listaKeysIgnorar)) {
			
			//Necessário nos casos em que os nomes dos subitems têm espaços que durante o submit do form são convertidos em "_"
			$nome = str_replace("_"," ", $key);	
				
		    $subitemQuery = "select distinct value_type from subitem where item_id = ". $_SESSION['item_id'] . " and name = '" . $nome . "' and state ='active'";
    		$res = $connection->query($subitemQuery);
			$row = $res->fetch_assoc();
			
	    	echo "<tr>";
	    		
	    	if ($_REQUEST[$key][0] == "obter") {
			echo "<td>" . $nome . "</td>";
			$_SESSION["subitems"] .= $nome ."|";
		}
		else
			echo "<td>" . $nome . " (filtro)</td>";
			
		if ($_REQUEST[$key][0] == "filtro") {
			echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select($row["value_type"],$key ."_op") . "</td>";
			
			//Se o subitem é do tipo enum, carregamos os valores permitidos para este subitem num input do tipo SELECT, caso contrario será um input do tipo TEXT
			if ($row["value_type"] == "enum")
				echo "<td>" . $nome . "<span style='color: red;'>*</span></br>" . generate_HTML_Select_Enum($connection,$nome) . "</td>";
			else
				echo "<td>" . $nome . "<span style='color: red;'>*</span></br><input type='text' name='" . $key . "' required></td>";
		}
		else
			if(isset($_REQUEST[$key][1])) 
				if ($_REQUEST[$key][1] == "filtro") {
					echo "<td>Operador<span style='color: red;'>*</span></br>" . generate_HTML_Select($row["value_type"],$key ."_op") . "</td>";
					
					//Se o subitem é do tipo enum, carregamos os valores permitidos para este subitem num input do tipo SELECT, caso contrario será um input do tipo TEXT
					if ($row["value_type"] == "enum")
						echo "<td>" . $nome . "<span style='color: red;'>*</span></br>" . generate_HTML_Select_Enum($connection,$nome) . "</td>";
					else
						echo "<td>" . $nome . "<span style='color: red;'>*</span></br><input type='text' name='" . $key . "' required></td>";
				}
				else
					echo "<td>-</td><td>-</td>";
			echo "</tr>";
		}
	    
	}
	
	echo "</table>";

	echo "<input type='hidden' name='estado' value='execucao'>";
	echo "<input type='hidden' name='atributos' value='" . $_SESSION["atributos"] . "'>";
	echo "<input type='hidden' name='subitems' value='" . $_SESSION["subitems"] . "'>";
	echo "<input type='submit' name='submit' value='Pesquisar'>";
	echo "</form>";
	echo "</br>";
}

function show_execucao($connection) {

	$spreadsheet = new Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	
	$existemOperadoresChild = false;	//Para testar se foram escolhidos filtros na criança
	
	$mensagem = "Nesta tabela são apresentados os atributos ";
	$strQuery = "SELECT ";	//Inicio da string para seleção dos atributos
	$strWhere = "WHERE ";	//Inicio da string para as condições dos filtros
	
	//Cria um array contendo todos os atributos seleccionados na pesquisa.
	//O array é preenchido atraves da funcção explode() que separa os elementos da string guardada na sessão e que estão separados por "|".
	//É retirado o último caracter separador "|" no fim da string, caso exista, para evitar o array ter um elemento nulo.
	$atributos = explode("|", rtrim($_SESSION["atributos"], "|"));
	
	//Aqui é feito o mesmo que foi feito para os atributos, só que desta vez tratamos dos subitems seleccionados na pesquisa.
	$subitems = explode("|", rtrim($_SESSION["subitems"], "|"));	
	
	//Variavel boleana para verificar se o ID foi selecionado para visualizado. Caso não o tenha sido,
	//Vamos adicioná-lo manualmente de modo a poder ser utilizado na query dos subitems.
	$IDfound = false;	 
	
	//Adiciona à string da query (SELECT) os atributos seleccionados para serem obtidos (separados por virgula)...
	foreach ($atributos as $atributo) {
		//Testa se o atributo atual é o id, caso seja, colocamos a váriavel $IDfound = true
		if ($atributo == "id")
			$IDfound = true;
			
		if($atributo != "")
			$strQuery .= $atributo . ", ";	//Cada atributo é adicionado ao SELECT...
		
		$mensagem .= "<strong>" . $atributo . "</strong>, ";	//Os nomes dos atributos seleccionados são também adicionados à mensagem de descrição da pesquisa
	}
	
	//Se o id não foi um dos atributos selecionados, insere-o manualmente...
	if (!$IDfound)
		$strQuery .= " id";
	else
		$strQuery = rtrim($strQuery, ", ");	// Removemos a última virgula referente ao último atributo...

	$mensagem = rtrim($mensagem, ", ");
	$mensagem .= " da informação que está armazenada na tabela 'child'";
	
	//INICIO do WHERE
	
	//Verificamos se foi submetido um operador para um dos atributos e caso exista, concatenamos o nome do atributo (estatico), 
	//O operador (vindo no REQUEST) e o valor a comparar (vindo do REQUEST).
	//Isto é feito para cada um dos 6 atributos da tabela CHILD.
	
	//NOTA IMPORTANTE: se algum dos atributos selecionados, tiverem o seu valor na BD como NULL, os operadores aqui representados não funcionam
	//e não será retornado nenhum registo. Aqui é utilizada a função COALESCE() do MySQL no atributo "tutor_email" para converter o valor NULL para ""
	//e poder ser efectuada as pesquisas (na BD existem 3 registos com valor NULL).	

	if (isset($_REQUEST["id_op"])) {
		$strWhere .= "id " . $_REQUEST["id_op"] . " " . $_REQUEST["id"] . " AND ";
		$existemOperadoresChild = true;
	}
	
	if (isset($_REQUEST["name_op"])) {
		$strWhere .= "name " . $_REQUEST["name_op"] . " '" . $_REQUEST["nome"] . "' AND ";
		$existemOperadoresChild = true;
	}
	
	if (isset($_REQUEST["birth_date_op"])) {
		$strWhere .= "birth_date " . $_REQUEST["birth_date_op"] . " '" . $_REQUEST["birth_date"] . "' AND ";
		$existemOperadoresChild = true;
	}
	
	if (isset($_REQUEST["tutor_name_op"])) {
		$strWhere .= "tutor_name " . $_REQUEST["tutor_name_op"] . " '" . $_REQUEST["tutor_name"] . "' AND ";
		$existemOperadoresChild = true;
	}
	
	if (isset($_REQUEST["tutor_phone_op"])) {
		$strWhere .= "tutor_phone " . $_REQUEST["tutor_phone_op"] . " '" . $_REQUEST["tutor_phone"] . "' AND ";
		$existemOperadoresChild = true;
	}
	
	if (isset($_REQUEST["tutor_email_op"])) {
		$strWhere .= "COALESCE(tutor_email,'') " . $_REQUEST["tutor_email_op"] . " '" . $_REQUEST["tutor_email"] . "' AND ";
		$existemOperadoresChild = true;
	}	
	
	$strWhere = rtrim($strWhere, " AND ");	//Remove o último AND referente à última condição do WHERE...
		
	//FIM DO WHERE

	//Junta o nome da tabela e a condição WHERE à query (caso existam operadores...)
	if ($existemOperadoresChild) {
		$strQuery .= " FROM child " . $strWhere; 
		
		//Adiciona os nomes dos filtros à mensagem, retirando o texto "WHERE " da váriavel contendo a clausa WHERE...
		$mensagem .= ". Foram aplicados os seguintes filtros aos atributos: <strong>" . str_replace("WHERE ", "", $strWhere) . "</strong>";
	}
	else {
		$strQuery .= " FROM child";
	}
		
	$res = mysqli_query($connection, $strQuery);	//Executa a query

	if($atributos[0] == "" && count($atributos) == 1)
		$atributos[0] = "id";

	///
	
	$strWhereAux = "";
	$strSubItens = "";
	
	foreach ($subitems as $subitem) {
	
		$strSubItens .= "<strong>" . $subitem . "</strong>, ";
		
		//
		//Primeiro verificamos se existem operadores no request para um determinado subitem (ex: altura_op) e caso exista, criamos a respectiva
		//condição no WHERE. A instrução str_replace(" ","_", $subitem) é necessária para os subitems com espaços no nome (ex: "tipo de fio"), pois no request
		//os espaços são convertidos para "_" tornando a váriavel "tipo_de_fio", pelo que necessitamos substituir os "_" por " " para obter o nome correcto...
		//
		if (isset($_REQUEST[str_replace(" ","_", $subitem) . "_op"])) {
			$strWhereAux .= " AND v.value " . $_REQUEST[str_replace(" ","_", $subitem) . "_op"] . " '" . $_REQUEST[str_replace(" ","_", $subitem)] . "'";
			$strWhereAux .= " [" . $subitem . "] ";
		}
	}

	///	
	
	$strSubItens = rtrim($strSubItens, ", ");
	$mensagem .= ". <br>Para cada criança são mostrados os subitems " . $strSubItens . " do item <strong>" . $_SESSION["item_nome"] . "</strong>";
	
	//Verifica se existem condições na string contendo a clausula WHERE dos subitems de modo a indicar se há ou não filtros ativos.
	if (substr($strWhereAux,4) != "")
		$mensagem .= ". Aos subitems são aplicados os seguintes filtros: <strong>" . substr($strWhereAux,4) . "</strong>.";
	else
		$mensagem .= ". Aos subitems não foram aplicados filtros.";
	
	
	//Apresenta a mensagem que explica a pesquisa atual e formata-a com a TAG HTML <em></em>
	echo "<em>". $mensagem . "</em>";
	
	//Escreve a mensagem no ficheiro excel (sem as TAGS HTML <strong></strong> e <br>)
	$msgExcel = str_replace("<strong>","", $mensagem);
	$msgExcel = str_replace("</strong>","", $msgExcel);
	$msgExcel = str_replace("<br>","", $msgExcel);
	$sheet->setCellValue('A1', $msgExcel);
	$sheet->mergeCells('A1:F1');
	
	$cell = $sheet->getCell('A1');
	$cell->getStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
	
	//Enable text wrapping in the cell
	$cell->getStyle()->getAlignment()->setWrapText(true);
	
	//Auto adjust the size of column A to fit the text
	$sheet->getColumnDimension('A')->setAutoSize(true);
	
	echo "<br><br><span style='align: right;'><a href='tabela.xlsx'>Download Tabela (xlsx)</a></span>";
	
	//Tabela para apresentar os dados das crianças
	echo "<table class='mytable' style='text-align: left;' border='1' cellpadding='2' cellspacing='2'>";
	echo "<tr>";
	
	//Cria os cabeçalhos da tabela usando o array $atributos para saber os atributos seleciondos na pesquisa
	$excelCol = 1;
	$excelRow = 3;
	foreach ($atributos as $atributo) {
		echo "<th>" . $atributo . "</th>";
		
		$sheet->setCellValueByColumnAndRow($excelCol++, $excelRow, $atributo);
	}
	
	echo "</tr>";
	    
	//Para cada criança encontrada na pesquisa, vamos apresentar os atributos seleccionados e depois pesquisar os subitems respectivos...
	while ($row = mysqli_fetch_assoc($res)) {
		
		echo "<tr>";
		$excelCol = 1;
		$excelRow++;
		foreach ($atributos as $atributo) {
			if($atributo != "") {
				echo "<td>" . $row[$atributo] . "</td>";
				$sheet->setCellValueByColumnAndRow($excelCol++, $excelRow, $row[$atributo]);
			}
		}
		echo "</tr>";
		
		//Este bloco de código é para testar se existe algum subitem para a criança atual, caso exista, mostramos a tabela dos subitems
		$count = 0;
		foreach ($subitems as $subitem) {			
			$strQuerySubitem = "SELECT s.name, v.value FROM subitem as s, value as v WHERE s.id = v.subitem_id
			AND s.name = '" . $subitem . "' AND s.item_id = '" . $_SESSION["item_id"] . "' AND v.child_id = '" . $row["id"] . "'";
			
			$result = mysqli_query($connection, $strQuerySubitem);
			$numrows = mysqli_num_rows($result);
			
			if ($numrows > 0) {
				$count = 1;
				break;
			}
		}
		
		if ($count == 1) {
		
		//
		// DADOS REFERENTES AOS SUBITEMS
		//
		echo "<tr>";
		echo "<td colspan='". count($atributos) ."'>";
		
		echo "<table class='mytable' style='text-align: left; width: 500px;' border='1' cellpadding='2' cellspacing='2'>";
		echo "<tr><td colspan='2'>Item: <strong>" . $_SESSION["item_nome"] . "</strong></td>";
		echo "<tr><th>subitem</th><th>valor</th></tr>";

		$excelRow++;
		$sheet->setCellValueByColumnAndRow(2, $excelRow++, "Item: " . $_SESSION["item_nome"]);
		$sheet->setCellValueByColumnAndRow(2, $excelRow, "Subitem");
		$sheet->setCellValueByColumnAndRow(3, $excelRow++, "Valor");
		
		foreach ($subitems as $subitem) {
		
			$strQuerySubitem = "SELECT s.name, v.value FROM subitem as s, value as v WHERE s.id = v.subitem_id
			AND s.name = '" . $subitem . "' AND s.item_id = '" . $_SESSION["item_id"] . "' AND v.child_id = '" . $row["id"] . "'";
			
			$strWhereSubitems = "";
			
			if (isset($_REQUEST[str_replace(" ","_", $subitem) . "_op"])) {
			$strWhereSubitems .= " AND v.value " . $_REQUEST[str_replace(" ","_", $subitem) . "_op"] . " '" . $_REQUEST[str_replace(" ","_", $subitem)] . "'";
			}
			
			$strQuerySubitem .= $strWhereSubitems . " order by s.name, v.value";
			
			$resSubitem = $connection->query($strQuerySubitem);	//Executa a query			
			
			while ($item = $resSubitem->fetch_assoc()) {	
				echo "<tr>";
				echo "<td>" . $item["name"] . "</td>" ;
				echo "<td>" . $item["value"] . "</td>" ;
				echo "</tr>";
				
				$sheet->setCellValueByColumnAndRow(2, $excelRow, $item["name"]);
				$sheet->setCellValueByColumnAndRow(3, $excelRow++, $item["value"]);
			}
		
		} //Fim do Foreach
		
		$excelRow--;
		
		echo "</table>";
		
		echo "</td>";
		echo "</tr>";
		//
		// FIM DADOS REFERENTES AOS SUBITEMS
		//
		
		}	//Fim do if($count == 1)
		
		echo "<tr><td colspan='". count($atributos) ."' style='background-color:lightgrey;'></td></tr>";
			
	}		
	
	echo "</table>";
	
	$writer = new Xlsx($spreadsheet);
	$writer->save("tabela.xlsx");
	echo "<a href='tabela.xlsx'>Download Tabela (xlsx)</a>";
}

function generate_HTML_Select($type,$name): string {

	$str = "";
	
	switch ($type) {
		case "int": case "double";
		$str = "<select style='width: 300px;' id='" . $name . "' name='" . $name . "'>
  			<option value='>'>></option>
  			<option value='>='>>=</option>
  			<option value='='>=</option>
  			<option value='<'><</option>
  			<option value='<='><=</option>
  			<option value='!='>!=</option>  			
			</select>";
		break;
		case "text":
		$str = "<select style='width: 300px;' id='" . $name . "' name='" . $name . "'>
  			<option value='='>=</option>
  			<option value='!='>!=</option>  	
  			<option value='LIKE'>LIKE</option>   					
			</select>";
		break; 
		case "enum": case "boolean":
		$str = "<select style='width: 300px;' id='" . $name . "' name='" . $name . "'>
  			<option value='='>=</option>
  			<option value='!='>!=</option>  			
			</select>";
		break;				 
	}

	return $str;
}

function generate_HTML_Select_Enum($connection, $name): string {
	$str = "<select style='width: 300px;' id='" . $name . "' name='" . $name . "' required>";
	$str .= "<option value='' selected></option>";
	
	$strQuery="SELECT * FROM subitem_allowed_value as sa, subitem as s WHERE sa.subitem_id = s.id and s.item_id = " . $_SESSION["item_id"] . " and s.value_type='enum' and 
	s.name = '" . $name . "' and s.state = 'active' and sa.state = 'active' order by sa.value";
	
	$res = $connection->query($strQuery);
	
	while($row = $res->fetch_assoc()){
		$value = $row["value"];
		$str .= "<option value='$value'>$value</option>";		
	}
	
	$str .= "</select>";
	
	return $str;
}
?>