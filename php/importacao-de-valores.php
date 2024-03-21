
<script src="/sgbd/custom/js/script.js"></script>
<?php
require_once("custom/php/common.php");
VoltarAtras();
$connection = init_page("values_import");
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Reader\IReader;
if (isset($_REQUEST["estado"]))
{
    if ($_REQUEST["estado"] == "escolheritem")
    {
        listadositems($connection,$current_page);
    }
    elseif ($_REQUEST["estado"] == "introducao") {
                echo "<form>";
                echo '<input type="hidden" name="crianca" value="' . $_REQUEST["crianca"] . '">';
                echo '<input type="hidden" name="item_id" value="' . $_REQUEST["item"] . '">';
                echo 'Deverá copiar estas linhas para um ficheiro excel e introduzir os valores a importar, sendo que, no caso
                    dos subitens enum, deverá constar 0 quando esse valor permitido não se aplique à instância em causa e 1 quando esse valor se aplica<br>';
                echo '<input type="hidden" name="estado" value="insercao">';
                echo '<input type="submit" value="Carregar Ficheiro"></form>';
            }
    if ($_REQUEST["estado"] == "insercao") {
        $query_crianca_prosseguir = "SELECT child.name FROM `child` WHERE child.id=" . $_REQUEST['crianca'];
        $query_crianca_prosseguir_resultado = mysqli_query($connection, $query_crianca_prosseguir);
    
        if (mysqli_num_rows($query_crianca_prosseguir_resultado) > 0) {
            $transacao = mysqli_begin_transaction($connection);
    
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load("import_to_insert.xlsx");
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            $idADar=MAXID($connection)+1;

            $worksheetData = $reader->listWorksheetInfo("import_to_insert.xlsx");
            foreach ($worksheetData as $worksheet) {
                $totalRows = $worksheet['totalRows']-1;
                $totalColumns = $worksheet['totalColumns'];
                $lastColumnLetter=$worksheet['lastColumnLetter'];
            }
            
            $myArray = array();
            for ($i = 0; $i < $totalColumns; $i++) {
                for ($t = 0; $t < $totalRows; $t++) {
                    $value = $sheetData[$t+1][$i];
                    if($value==null){

                    }
                    else{
                    
                  
                        array_push($myArray, $value);
                }
                }
            }  
            $arraySize = count($myArray);
            print_r($myArray);
        }
        else {
            echo "Criança não encontrada!";
        }
            
        }
    }


else
{
    echo "<p></p><hr>";
    echo '<h3>Importação de Valores - Escolher Criança</h3>';
    echo "<p></p><hr>";
    GestaoRegistosTabela($connection,$current_page);
    
}
function MAXID($connection){
    $sqlmaxid="SELECT MAX(value.id) FROM `value`;";
    $sqlmaxidresultado = mysqli_query($connection, $sqlmaxid);
    if (mysqli_num_rows($sqlmaxidresultado) > 0)
    {
        $MAXID_array = mysqli_fetch_row($sqlmaxidresultado);
    }
    return $MAXID_array[0];
}
function GestaoRegistosTabela($connection,$current_page)
{
    
    $sql = "SELECT child.id, name, birth_date, tutor_name, tutor_phone, tutor_email 
            FROM child 
            ORDER BY `child`.`name` ASC";

    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) > 0)
    {
        echo '<table><thead>
        <tr>
            <th>Nome</th>
            <th>Data de nascimento</th>
            <th>Enc. de educação</th>
            <th>Telefone do Enc.</th>
            <th>e-mail</th>
        </tr>
            </thead><tbody>';
    while ($row = mysqli_fetch_array($result))
    {
        echo "<tr>
                    <td></a> <a href=" . $current_page . '?estado=escolheritem&crianca=' . $row["id"] . ">{$row['name']}</td>
                    <td> {$row['birth_date']}</td>
                    <td> {$row['tutor_name']}</td>
                    <td>{$row['tutor_phone']}</td>
                    <td>{$row['tutor_email']}</td>
                    </tr>";
    }
        echo "</tbody></table>";
    }
}

function listadositems($connection,$current_page)
{
    //seleciona crianca
    $querycriancaprosseguir="SELECT child.name FROM `child` where child.id=".$_REQUEST['crianca'];
    $querycriancaprosseguirResultado = mysqli_query($connection, $querycriancaprosseguir);
        if(mysqli_num_rows($querycriancaprosseguirResultado) > 0){
            $QueryItems = 'select distinct item_type.id, item_type.name from item_type right join item on item.item_type_id = item_type.id';

	$TodosItems = mysqli_query($connection,$QueryItems);
    if(mysqli_num_rows($TodosItems)>0){
        while($itemtipo = mysqli_fetch_array($TodosItems))
        {
            //listar os item
            $itemQuery = "select id, name from item where state = 'active' and item_type_id = " . $itemtipo['id'];
            $listItems = mysqli_query($connection,$itemQuery);
    
            echo'<li>'. $itemtipo['name'] .'<ul>';
            
            while($item = mysqli_fetch_array($listItems)){
                echo "<li><a href='". $current_page . "?estado=introducao&crianca=". $_REQUEST['crianca'] . "&item=" . $item['id'] . "'>[". $item['name'] ."]</a></li>";
            }
            
            echo'</li></ul>';
    
        }
    }
    else
    {
        echo "Nao existe items";

    }

        }
        else{
            echo "A crianca não existe";
        }
	
	
}






?>
