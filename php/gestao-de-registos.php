<?php
require_once("custom/php/common.php");
/*Botao para voltar para trás*/
VoltarAtras();
$connection = init_page("manage_records");

/*Se o estado não está def. ou seja quando abre a pagina entra no bloco else e só entra
neste bloco depois de o estado estar defenido e ser validar*/

if (isset($_REQUEST["estado"]))
{
    if ($_REQUEST["estado"] == "validar")
    {
        if (FormularioErrosRegistos())
        {
            echo "<p></p><hr>";
            echo "<h3>Dados de registo - Validação</h3><br>";
            echo "<p>Deseja mesmo inserir os seguintes dados?</p>";
            echo "<p> Nome completo: " .$_REQUEST["nome_crianca"] . "</p>";
            echo "<p> Data de nascimento: ". $_REQUEST["crianca_nascimento"] . "</p>";
            echo "<p> Nome completo do encarregado de educação: " . $_REQUEST["nome_enc"] . "</p>";
            echo "<p> Telefone do encarregado de educação: " . $_REQUEST["telf_enc"] . "</p>";
            echo "<p> Endereço de e-mail do tutor: " . $_REQUEST["email_enc"] . "</p>";
            echo '<form>

            <input type="hidden" name="nome_crianca" value="' . $_REQUEST["nome_crianca"] . '">
            <input type="hidden" name="crianca_nascimento" value="' . $_REQUEST["crianca_nascimento"] . '">
            <input type="hidden" name="nome_enc" value="' . $_REQUEST["nome_enc"] . '">
            <input type="hidden" name="telf_enc" value="' . $_REQUEST["telf_enc"] . '">
            <input type="hidden" name="email_enc" value="' . $_REQUEST["email_enc"] . '">
            <input type="hidden" name="estado" value="inserir">
            <input type="submit" value="Submit">
          </form>';
        }
    }
    
    /*Insercao na base de dados*/
    elseif($_REQUEST["estado"] == "inserir")
    {
        
        echo "<p></p><hr>";
        echo "<h3> Dados de registo - Inserção";
        echo "<p></p><hr>";
        $_SESSION["nome_crianca"]=$_REQUEST["nome_crianca"];
        $_SESSION["crianca_nascimento"]=$_REQUEST["crianca_nascimento"];
        $_SESSION["nome_enc"]=$_REQUEST["nome_enc"];
        $_SESSION["telf_enc"]=$_REQUEST["telf_enc"];
        $_SESSION["email_enc"]=$_REQUEST["email_enc"];
        function MAXID($connection){
            $sqlmaxid="SELECT MAX(child.id) FROM `child`;";
            $sqlmaxidresultado = mysqli_query($connection, $sqlmaxid);
            if (mysqli_num_rows($sqlmaxidresultado) > 0)
            {
                $MAXID_array = mysqli_fetch_row($sqlmaxidresultado);
            }
            return $MAXID_array[0];
        }

        $_REQUEST['nome_crianca']=mysqli_real_escape_string($connection,$_REQUEST['nome_crianca']);
        $_REQUEST['crianca_nascimento']=mysqli_real_escape_string($connection,$_REQUEST['crianca_nascimento']);
        $_REQUEST['nome_enc']=mysqli_real_escape_string($connection,$_REQUEST['nome_enc']);
        $_REQUEST['telf_enc']=mysqli_real_escape_string($connection,$_REQUEST['telf_enc']);
        $_REQUEST['email_enc']=mysqli_real_escape_string($connection,$_REQUEST['email_enc']);

        $colunaidnew = (int) MAXID($connection) + 1;
        if(FormularioErrosRegistos()){
            $sqlinserir = "INSERT INTO `child` (`id`, `name`, `birth_date`, `tutor_name`, `tutor_phone`, `tutor_email`) 
        VALUES ('$colunaidnew', '$_REQUEST[nome_crianca]', '$_REQUEST[crianca_nascimento]', '$_REQUEST[nome_enc]', 
        '$_REQUEST[telf_enc]', '$_REQUEST[email_enc]')";

        $resultado = mysqli_query($connection, $sqlinserir);
        if($resultado)
        {
            echo "Inseriu os dados com sucesso<p></p>";
            echo "<a href=/sgbd/gestao-de-registos>Inserir dados de outra criança</a>";
        }
        else
        {
            echo "Erro de inserção";
        }
        }
        

    }
}
/*Caso DEFAULT*/
else
{
    
    GestaoRegistosTabela($connection,$current_page);
    echo "<p></p><hr>";
    echo '<h3>Dados de registo - Introdução</h3>';
    echo "<p></p><hr>";
    echo "<h4>Introduza os dados pessoais básicos da criança: <h4>";
    echo "<p></p>";
    Formulario();


}


function Formulario()
{
    // $name_err=$crianca_nascimento_err=$nome_enc_err=$telf_enc_err=$email_enc_err="";
//onsubmit="return FormValidacaoReg(this)"
    echo '
<form>
        <label for="nome_crianca">Nome completo:</label><br>
        <input type="text" id="nome_crianca" name="nome_crianca">

        <label for="crianca_nascimento">Data de nascimento:</label><br>
        <input placeholder="YYYY-MM-DD" maxlength="10" input type="text" id="crianca_nascimento" name="crianca_nascimento"
        
        <label for="nome_enc">Nome completo do encarregado de educação:</label><br>
        <input type="text" id="nome_enc" name="nome_enc"
        
        <label for="telf_enc">Telefone do encarregado de educação:</label><br>
        <input placeholder="INSIRA UM NUMERO COM 9 DIGITOS" maxlength="9"input type="text" id="telf_enc" name="telf_enc"
        
        <label for="email_enc">Endereço de e-mail do tutor:</label><br>
        <input type="text" id="email_enc" name="email_enc">

        <input type="hidden" name="estado" value="validar">

        <input type="submit" value="Submit">
</form>';
}

function GestaoRegistosTabela($connection,$current_page)
{
    //fazer as primeiras colunas menos os registos
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
            <th>registos</th>
        </tr>
            </thead><tbody>';

        while ($row = mysqli_fetch_assoc($result))
        {
            //mostrar item.id,item.name,value.date,value.value,subitem.name,value.producer
            //juntar tabelas item,subitem e value
            //ordenar primeiro pelo nome,data,producer,subitem.name,value.value
            $sql2 = "SELECT DISTINCT item.id, item.name AS 
            nome, value.date, value.value, subitem.name, value.producer 
            FROM item
            JOIN subitem ON subitem.item_id = item.id 
            JOIN value ON value.subitem_id = subitem.id 
            WHERE value.child_id = '{$row['id']}'  ORDER BY `nome`,`date`,producer,`subitem`.`name`,`value`.`value` ASC";

            $resultadoquerya = mysqli_query($connection, $sql2);

            $registosConcatenados = "";
            $prev="";
            $prev2="";
            $prev3="";
            while ($resultadoqueryreg = mysqli_fetch_assoc($resultadoquerya))
            {
                // Concatena os registros
                $nome=mb_strtoupper($resultadoqueryreg["nome"]);
                if($resultadoqueryreg["nome"]==$prev && $resultadoqueryreg['date']==$prev2 && $resultadoqueryreg['producer']==$prev3){
                    $registosConcatenados .="<strong>{$resultadoqueryreg['name']}
                    </strong>({$resultadoqueryreg['value']});";
                }
                elseif($resultadoqueryreg["nome"]==$prev){
                    $prev=$resultadoqueryreg["nome"];
                    $prev2=$resultadoqueryreg["date"];
                    $prev3=$resultadoqueryreg["producer"];
                    $registosConcatenados .= "<br>
                    <a href=" . $current_page . '?estado=editar&item=' . $row["id"] . ">
                    [editar]
                    </a> <a href=" . $current_page . '?estado=apagar&item=' . $row["id"] . ">[apagar]   
                    </a>-<strong>{$resultadoqueryreg['date']}</strong> ({$resultadoqueryreg['producer']}) - <strong>{$resultadoqueryreg['name']}
                    </strong>({$resultadoqueryreg['value']});";
                }
                else{
                    $prev=$resultadoqueryreg["nome"];
                    $prev2=$resultadoqueryreg["date"];
                    $prev3=$resultadoqueryreg["producer"];
                    $registosConcatenados .= "<br>$nome:<br>
                    <a href=" . $current_page . '?estado=editar&item=' . $row["id"] . ">
                    [editar]
                    </a> <a href=" . $current_page . '?estado=apagar&item=' . $row["id"] . ">[apagar]   
                    </a>-<strong>{$resultadoqueryreg['date']}</strong> ({$resultadoqueryreg['producer']}) - <strong>{$resultadoqueryreg['name']}
                    </strong>({$resultadoqueryreg['value']});";
                }
                
            }
            
            echo "<tr>
                    <td>{$row['name']}</td>
                    <td> {$row['birth_date']}</td>
                    <td> {$row['tutor_name']}</td>
                    <td>{$row['tutor_phone']}</td>
                    <td>{$row['tutor_email']}</td>
                    <td>{$registosConcatenados}</td>
                  </tr>";
        }
        echo '</tbody></table>';
    }
    else
    {
        echo "Não há crianças";
    }
    mysqli_close($connection);
}
function FormularioErrosRegistos()
{
    if (ErrosCrianca())
    {
        echo $_SESSION["nome_crianca_err"];
        echo "<p></p>";
    }
    if (ErrosCriancaNascimento())
    {
        echo $_SESSION["crianca_nascimento_err"];
        echo "<p></p>";
    }
    if (ErrosEncEducacao())
    {
        echo $_SESSION["nome_enc_err"];
        echo "<p></p>";
    }
    if (ErrosTelfEnc())
    {
        echo $_SESSION["telf_enc_err"];
        echo "<p></p>";
    }
    if (ErrosEmail())
    {
        echo $_SESSION["email_enc_err"];
        echo "<p></p>";
    } 
    elseif(!ErrosCrianca() && !ErrosCriancaNascimento() && !ErrosEncEducacao() && !ErrosTelfEnc() && !ErrosEmail())
    {
        return true;
    }

}
/*true para quando dá erros*/
function ErrosCrianca()
{
    if (empty($_REQUEST["nome_crianca"]))
    {
        $_SESSION["nome_crianca_err"] = "Introduza um nome";
        return true;
    }
    /*caso em que o nome tem apenas letras podendo conter espaços*/
    elseif (!preg_match("/^[\p{L}\s]+$/u", $_REQUEST["nome_crianca"]))
    {
        $_SESSION["nome_crianca_err"] = "Introduza um nome válido";
        return true;
    } 
    /*valido*/
}
function isBissexto($ano) {
    if($ano%4==0){
        if($ano%100==0){
            if($ano%400==0){
                return true;
            }
            else{
            }
        }
        else{
            return true;
        }
    }
    else{
    }
}
function ErrosCriancaNascimento(){
    if (empty($_REQUEST["crianca_nascimento"]))
    {
        $_SESSION["crianca_nascimento_err"] = "Introduza uma data de nascimento";
        return true;
    }
    /*YYYY-MM-DD*/
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $_REQUEST["crianca_nascimento"]))
    {
        $_SESSION["crianca_nascimento_err"] = "Introduza uma data de nascimento válida";
        return true;
    }
    /*transforma 3 variaveis e divide por - porque a data é como fosse uma string desde
    genero YYYY-MM-DD */
    /*explode origina uma array de substrings [0],[1],[2], sendo associadas  */
    /*intval mostra valor de uma variavel*/
    $temp= explode('-', $_REQUEST["crianca_nascimento"]);
    $ano=intval($temp[0]);
    $mes=intval($temp[1]);
    $dia=intval($temp[2]);
    $dataatual = date("Y-m-d");
    $temp2= explode('-', $dataatual);
    $anoAtual=intval($temp2[0]);
    $mesAtual=intval($temp2[1]);
    $diaAtual=intval($temp2[2]);
    
    if($ano>=2005 && $ano<=2200 || $ano<=$anoAtual)
    {
        //2024==2024
        if($ano==$anoAtual){
            //02<=01
            if($mes<=$mesAtual){
                if($dia<=$diaAtual){

                }
                else{
                    $_SESSION["crianca_nascimento_err"] = "Introduza uma data de nascimento válida";
                    return true;
                }

            }
            else{
                $_SESSION["crianca_nascimento_err"] = "Introduza uma data de nascimento válida";
                return true;
            }
        }
        if($mes>=1 && $mes<=12)
        {
            if(($dia >= 1 && $dia <= 31) && ($mes == 1 || $mes == 3 || $mes == 5 || $mes == 7 || $mes == 8 || $mes == 10 || $mes == 12))
            {
                return false;
            }
            if(($dia >= 1 && $dia <= 30) && ($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11))
            {
                return false;
            }
            if(($dia >= 1 && $dia <= 28) && ($mes == 2))
            {
                return false;
            }
            if(($dia == 29 && $mes == 2)&&isBissexto($ano))
            {
                return false;
            }
        else
        {
            $_SESSION["crianca_nascimento_err"] = "Introduza uma data de nascimento válida";
            return true;
        }

        }
        else
        {
            $_SESSION["crianca_nascimento_err"] = "Introduza uma data de nascimento válida";
            return true;
        }
    }
   
    else{
        $_SESSION["crianca_nascimento_err"] = "Introduza uma data de nascimento válida";
        return true;  
    }
}

function ErrosEncEducacao()
{
    if (empty($_REQUEST["nome_enc"])) 
    {
        $_SESSION["nome_enc_err"] = "Introduza um encarregado de educação";
        return true;
    } 
    elseif (!preg_match("/^[\p{L}\s]+$/u", $_REQUEST["nome_enc"])) 
    {
        $_SESSION["nome_enc_err"] = "Introduza um encarregado de educação válido";
        return true;
    } 
}

function ErrosTelfEnc()
{
    if (isset($_REQUEST["telf_enc"])) 
    {
        if (empty($_REQUEST["telf_enc"])) 
        {
            $_SESSION["telf_enc_err"] = "Introduza um número de telefone";
            return true;
        } 
        elseif (!preg_match("/^\d{9}$/", $_REQUEST["telf_enc"]))
        {
            $_SESSION["telf_enc_err"] = "Introduza um número de telefone válido";
            return true;

        }

    }
}
function ErrosEmail()
{
    if (empty($_REQUEST["email_enc"]))
    {
        $_SESSION["email_enc"] = NULL;
        return false;
    }
    elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $_REQUEST["email_enc"])) {
        $_SESSION["email_enc_err"] = "Introduza um email válido";
        return true;
    }
}

?>
<script src="/sgbd/custom/js/script.js"></script>