<?php
setlocale(LC_ALL, "pt_BR.utf-8");
global $current_page;
$current_page = get_site_url() . '/' . basename(get_permalink());
?>


<?php

// Inicia a página, verifica se um utilizador tem permissões para ver a página e tenta retornar uma conexão à base de dados
function init_page($capability)
{
    if (!is_user_logged_in())
        die("Não esta logado.");
    if (!current_user_can($capability))
        die("Não tem autorizacão para aceder a esta página.");
    return connect_to_database();
}

function connect_to_database()
{
    $connection = mysqli_connect("localhost", "root", "", "sgbd");
    if ($connection->connect_errno) {
        die("Connection failed: " . $connection->connect_error);
    } else {
        return $connection;
    }
}

// Gerar o botao para ativar/desativar o registo
function generate_change_state_button($connection, string $tableName, int $id): string{
    $queryText = "select * from " . $tableName ." where id=$id" ;

    //String containing the buttons
    $buttonsString = "";

    // Check if the first column of the table has the "state" column set
    $firstRowOfTable = $connection->query($queryText)->fetch_assoc();
    // link of the "edicao de dados" component
    $link = dirname($GLOBALS['current_page'], 1)."/edicao-de-dados";

    // Botões para desativar e ativar
    if(isset($firstRowOfTable['state'])){
        // case a coluna estado exista, vamos gerar o botão de ativar e desativar respetivamente
        if($firstRowOfTable['state'] == 'active'){
            $buttonsString .= "<a href=$link?estado=desativar&tableName=".$tableName."&id=".$id.">[desativar]</a>";
        }else{
            $buttonsString .= "<a href=$link?estado=ativar&tableName=".$tableName."&id=".$id.">[ativar]</a>";
        }
    }
    return $buttonsString;
}

// Gerar os botoes para apagar e editar para todas as componentes exceto a component "gestao de registos"
function generate_edit_data_buttons($connection, string $tableName, int $id): string{
    $queryText = "select * from " . $tableName ." where id=$id";

    //String containing the buttons
    $buttonsString = "";

    //Check if the first column of the table has the "state" column set
    $firstRowOfTable = $connection->query($queryText)->fetch_assoc();
    //link of the "edicao de dados" component
    $link = dirname($GLOBALS['current_page'], 1)."/edicao-de-dados";

    //Gerar o botão para apagar o registo
    $buttonsString .= "<a href=$link?estado=apagar&tableName=".$tableName."&id=".$id.">[apagar]</a>";

    //gerar o botao para editar o registo
    $buttonsString .= "<a href=$link?estado=editar&tableName=".$tableName."&id=".$id.">[editar]</a>";


    return $buttonsString;
}
?>

<?php
function VoltarAtras()
{
    echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atrás'>Voltar atrás</a>\");</script>
<noscript>
<a href='" . $_SERVER['HTTP_REFERER'] . "‘ class='backLink' title='Voltar atrás'>Voltar atrás</a>
</noscript><br>";
}
?>

<?php 
function get_enum_values($connection, $table, $column)
{
    $query = " SHOW COLUMNS FROM `$table` LIKE '$column' ";
    $result = mysqli_query($connection, $query);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    #extract the values
    #the values are enclosed in single quotes
    #and separated by commas
    $regex = "/'(.*?)'/";
    preg_match_all($regex, $row[1], $enum_array);
    $enum_fields = $enum_array[1];
    return ($enum_fields);
}
?>