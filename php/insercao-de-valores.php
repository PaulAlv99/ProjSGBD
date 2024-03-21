<?php

require_once('custom/php/common.php');
VoltarAtras();
global $link;
$link = init_page('insert_values');
if ($link == NULL)
{
    echo
         "Não tem autorização para aceder a esta página";
}
else
{
    if( isset($_REQUEST['estado']) == false )
    {
        echo
             "<h3>Inserção de valores - criança - procurar<h3>";
        echo
             '<form>
             <label>
             Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela<label>
             <br>
             <label
             for="nome">Nome</label> <input type="text" id="nome" name="nome"><br>
             <label
             for="datanascimento">Data de nascimento - (no formato AAAA-MM-DD)</label> <input type="text" id="datanascimento" name="datanascimento"><br>
             <button
             type="submit">Submeter</button>
             <input
             type="hidden" name="estado" value="escolher_crianca">
             </form>';
        if (isset($_REQUEST["nome"]) && isset($_REQUEST["datanascimento"]))
        {
            $_SESSION['datanascimento'] = $_REQUEST['datanascimento'];
            $_SESSION['nome'] = $_REQUEST['nome'];
        }

    }
    else if ( $_REQUEST['estado']=='escolher_crianca' )
    {
        if( isset($_REQUEST["nome"]) || isset($_REQUEST["datanascimento"]))
        {
           $nome = $_REQUEST["nome"];
           $data = $_REQUEST["datanascimento"];
           if( empty($data) && !(empty($nome)) )
           {
               if( preg_match("/^[a-zA-Z\s]+$/", $_REQUEST["nome"]))
               {
                   echo '<h3> Inserção de valores - criança - escolher <h3>';
                   $query1 = "SELECT name , birth_date, id FROM child WHERE name LIKE '$nome%'";
                   $resultadoquery1 = mysqli_query($link,$query1);
                   $resultadoqueryteste = mysqli_query($link, $query1);
                   $colunateste = mysqli_fetch_assoc($resultadoqueryteste);
                   if ($colunateste == NULL)
                   {
                        echo "Nao existem criancas com o respetivo nome";
                   }
                   while ($colunasquery1 = $resultadoquery1->fetch_assoc() )
                   {
                        echo "<a href='insercao-de-valores?estado=escolher_item&crianca=$colunasquery1[id]'>";
                        echo "[$colunasquery1[name]]</a>&nbsp ($colunasquery1[birth_date])";
                        echo "<br>";
                   }
               }
               else
               {
                    $_SESSION["nome_erro"] = "Introduza um nome valido";
                    echo $_SESSION["nome_erro"];
               }
           }
           if( !(empty($data)) && empty($nome))
           {
               if( preg_match("/^\d{4}-\d{2}-\d{2}$/", $_REQUEST["datanascimento"]) )
               {
                   echo '<h3> Inserção de valores - criança - escolher <h3>';
                   $query2 = "SELECT name , birth_date, id FROM child WHERE birth_date LIKE '$data'" ;
                   $resultadoquery2 = mysqli_query($link,$query2);
                   $resultadoqueryteste = mysqli_query($link, $query2);
                   $colunateste = mysqli_fetch_assoc($resultadoqueryteste);
                   if ($colunateste == NULL)
                   {
                        echo "Nao existem criancas com a respetiva data de nascimento";
                   }
                   while ($colunasquery2 = $resultadoquery2->fetch_assoc() )
                   {
                        echo "<a href='insercao-de-valores?estado=escolher_item&crianca=$colunasquery2[id] '>";
                        echo "[$colunasquery2[name]]</a>&nbsp ($colunasquery2[birth_date])";
                        echo "<br>";
                   }
               }
               else
               {
                   $_SESSION["datanascimento_erro"] = "<h3><strong>Introduza uma data de nascimento valida</strong><h3>";
                   echo $_SESSION["datanascimento_erro"];
               }
           }
           if (!(empty($data)) && !(empty($nome)) )
           {
               if(
                   preg_match("/^[a-zA-Z\s]+$/", $_REQUEST["nome"]) &&
                   preg_match("/^\d{4}-\d{2}-\d{2}$/", $_REQUEST["datanascimento"]) )
               {
                   echo '<h3> Inserção de valores - criança - escolher <h3>';
                   $query3 = "SELECT name , birth_date, id FROM child WHERE name LIKE '$nome%' AND birth_date LIKE '$data'";
                   $resultadoquery3 = mysqli_query($link, $query3);
                   $resultadoqueryteste= mysqli_query($link, $query3);
                   $colunateste = mysqli_fetch_assoc($resultadoqueryteste);
                   if($colunateste==NULL)
                   {
                        echo "Nao existem criancas com o respetivo nome e data de nascimento";
                   }
                   while ( $colunasquery3 = mysqli_fetch_assoc($resultadoquery3) )
                   {
                        echo "<a href='insercao-de-valores?estado=escolher_item&crianca=$colunasquery3[id]'>";
                        echo "[$colunasquery3[name]]</a> ($colunasquery3[birth_date])";
                        echo "<br>";
                   }
               }
               else
               {
                   $_SESSION["datanascimento_nome_erro"] = "Introduza uma data de nascimento e um nome valido";
                   echo $_SESSION["datanascimento_nome_erro"];
               }
           }
           if ((empty($data)) && (empty($nome)))
           {
               echo '<h3> Inserção de valores - criança - escolher <h3>';
               $query4 = "SELECT name , birth_date, id FROM child";
               $resultadoquery4 = mysqli_query($link, $query4);
               while ($colunasquery4 = $resultadoquery4->fetch_assoc())
               {
                    echo "<a href='insercao-de-valores?estado=escolher_item&crianca=$colunasquery4[id] '>";
                    echo "[$colunasquery4[name]]</a>&nbsp ($colunasquery4[birth_date])";
                    echo "<br>";
               }
           }
        }
    }
    else if ($_REQUEST['estado'] == 'escolher_item' )
    {
        $child_id = $_REQUEST['crianca'];
        $_SESSION['crianca'] = $child_id;
        echo "<h3>Inserção de valores - escolher item<h3>";
        $querytypeitem = 'SELECT name ,id FROM item_type';
        $resultadoquerytype = mysqli_query($link, $querytypeitem);
        echo '<ul>';
        while ($linhasquerytype = mysqli_fetch_assoc($resultadoquerytype))
        {
            $queryitem = "SELECT name ,item_type_id ,id ,state FROM item WHERE state ='active' AND item_type_id ='$linhasquerytype[id]'";
            $resultadoqueryitem = mysqli_query($link, $queryitem);
            $resultadoqueryitemverifica = mysqli_query($link, $queryitem);
            $linhasqueryitemverifica = mysqli_fetch_assoc($resultadoqueryitemverifica);
            if($linhasqueryitemverifica != NULL)
            {
                if($linhasquerytype['name']=='dado_de_crianca')
                {
                    echo "<li>dado de crianca:<ul>";
                    echo "<br>";
                }
                else
                {
                    echo "<li>$linhasquerytype[name]:<ul>";
                    echo "<br>";
                }
                while ($linhasqueryitem = mysqli_fetch_assoc($resultadoqueryitem))
                {

                    echo "<li><a href='insercao-de-valores?estado=introducao&item=$linhasqueryitem[id]'>";
                    echo "[$linhasqueryitem[name]]</a></li>";
                    echo "<br>";
                }
                echo '</li></ul>';
                echo "<br>";
            }
            else
            {
                echo "<li>$linhasquerytype[name]:</li>
                     <br>";
                echo "Nao tem subitens ";
            }
        }
        echo '</ul>';

    }
    else if ($_REQUEST['estado'] == 'introducao')
    {
        $item_id = $_REQUEST['item'];
        $_SESSION['item']= $_REQUEST['item'];
        $queryitemname = "SELECT name,id,item_type_id FROM item WHERE id='$item_id'";
        $resultadoqueryname = mysqli_query($link, $queryitemname);
        $linhasquerynameid = mysqli_fetch_assoc($resultadoqueryname);
        $item_name = $linhasquerynameid['name'];
        $item_type_id = $linhasquerynameid['item_type_id'];
        if( isset($item_name) && isset($item_type_id) )
        {
            echo "<h3>Inserção de valores - $item_name :<h3>";
            $querysubitens = "SELECT id,name,item_id,value_type,form_field_name,unit_type_id,mandatory, form_field_type,form_field_order,state
                             FROM subitem WHERE state='active' AND item_id ='$item_id' ORDER BY form_field_order";
            $resultadoquerysubitens = mysqli_query($link, $querysubitens);
            $resultadoquerysubitensteste = mysqli_query($link, $querysubitens);
            $linhasquerysubitemteste = mysqli_fetch_assoc($resultadoquerysubitensteste);
            if ($linhasquerysubitemteste == NULL)
            {
                echo "<h5>Não existem subitens<h5>
                     <br>";
            }
            else
            {
                echo "<red>*Obrigatorio</red><br><br>";
                echo '<style>
                    red{
                       color: red;
                      }
                  </style>';
                echo '<form>';
                while (($linhasquerysubitem = mysqli_fetch_assoc($resultadoquerysubitens)))
                {
                    $querynomeundiades = "SELECT id , name FROM subitem_unit_type WHERE id ='$linhasquerysubitem[unit_type_id]'";
                    $resultadoquerynameunidades = mysqli_query($link, $querynomeundiades);
                    $linhasquerysnameunidade = mysqli_fetch_assoc($resultadoquerynameunidades);

                    if( ($linhasquerysubitem['value_type']) == 'text')
                    {
                        echo "$linhasquerysubitem[name]: ";
                        if($linhasquerysubitem['mandatory'] == '1')
                        {
                            echo "<red>*</red>";
                        }
                        echo "<br>";
                        $tipodevalor = $linhasquerysubitem['form_field_type'];
                        switch ($tipodevalor)
                        {
                              case 'text':
                                  if($linhasquerysnameunidade!=NULL)
                                  {
                                      echo "<input type='text'
                                           id=''$linhasquerysubitem[id]'texttext'
                                           name='$linhasquerysubitem[id]texttext'
                                           style='width: 1000px;' >";
                                      echo "$linhasquerysnameunidade[name]";
                                      echo "<br>";
                                      echo "<br>";
                                  }
                                  else
                                  {
                                      echo "<input type='text'
                                           id=''$linhasquerysubitem[id]'texttext'
                                           name='$linhasquerysubitem[id]texttext'
                                           >";
                                      echo "<br>";
                                      echo "<br>";
                                  }
                              break;
                              case 'textbox':
                                  if($linhasquerysnameunidade!=NULL)
                                  {
                                       echo "<input type='textbox'
                                       id=''$linhasquerysubitem[id]'texttextbox'
                                       name='$linhasquerysubitem[id]texttextbox'
                                       style='width: 1000px;'>";
                                       echo "$linhasquerysnameunidade[name]";
                                       echo "<br>";
                                       echo "<br>";
                                  }
                                  else
                                  {
                                       echo "<input type='textbox'
                                       id=''$linhasquerysubitem[id]'texttext'
                                       name='$linhasquerysubitem[id]texttextbox'
                                       '>";
                                       echo "<br>";
                                       echo "<br>";
                                  }
                              break;
                        }
                    }
                    if( ($linhasquerysubitem['value_type']) == 'bool')
                    {
                        echo "$linhasquerysubitem[name]: ";
                        if ($linhasquerysubitem['mandatory'] == '1') {
                            echo "<red>*</red>";
                        }
                        if ($linhasquerysnameunidade != NULL)
                        {
                            echo "$linhasquerysnameunidade[name]";
                            echo "<br>";
                        }
                        $queryvalorespermitidos = "SELECT id ,value, subitem_id, state FROM subitem_allowed_value
                                                   WHERE subitem_id = '$linhasquerysubitem[id]'";
                        $resultadovalorespermitidos = mysqli_query($link, $queryvalorespermitidos);
                        echo "<input type='radio'";
                        while ($linhasvalorespermitidos = mysqli_fetch_assoc($resultadovalorespermitidos))
                        {
                            echo "id='$linhasvalorespermitidos[id]' name='$linhasvalorespermitidos[id]'
                                 value='$linhasvalorespermitidos[value]'>
                                 <label for='$linhasvalorespermitidos[value]'>
                                 $linhasvalorespermitidos[value]</label><br>";
                            echo "<br>";
                        }
                    }
                    if( ($linhasquerysubitem['value_type']) == 'double')
                    {
                        if ($linhasquerysnameunidade != NULL){
                            echo "$linhasquerysubitem[name]: ";
                            if ($linhasquerysubitem['mandatory'] == '1')
                            {
                                echo "<red>*</red>";
                            }
                            echo "<br>";
                            echo "<input type='text'
                                 id='$linhasquerysubitem[id]doubletext'
                                 name='$linhasquerysubitem[id]doubletext'
                                 style='width: 1000px;'>";
                            echo "$linhasquerysnameunidade[name]";
                            echo "<br>";
                            echo "<br>";
                        }
                        else
                        {
                            echo "$linhasquerysubitem[name]: ";
                            if ($linhasquerysubitem['mandatory'] == '1')
                            {
                                echo "<red>*</red>";
                            }
                            echo "<br>";
                            echo "<input type='text'
                                 id='$linhasquerysubitem[id]doubletext'
                                 name='$linhasquerysubitem[id]doubletext'
                                 >";
                            echo "<br>";
                            echo "<br>";
                        }
                    }
                    if(($linhasquerysubitem['value_type']) == 'int')
                    {
                        if($linhasquerysnameunidade!=NULL)
                        {
                            echo "$linhasquerysubitem[name]: ";
                            if ($linhasquerysubitem['mandatory'] == '1')
                            {
                                echo "<red>*</red>";
                            }
                            echo "<br>";
                            echo "<input type='text'
                                 id='$linhasquerysubitem[id]inttext'
                                 name='$linhasquerysubitem[id]inttext'
                                 style='width: 1000px;'>
                                 $linhasquerysnameunidade[name]";
                            echo "<br>";
                            echo "<br>";
                        }
                        else
                        {
                            echo "$linhasquerysubitem[name]: ";
                            if ($linhasquerysubitem['mandatory'] == '1')
                            {
                                echo "<red>*</red>";
                            }
                            echo "<br>";
                            echo "<input type='text'
                                 id='$linhasquerysubitem[id]inttext'
                                 name='$linhasquerysubitem[id]inttext'
                                 >";
                            echo "<br>";
                            echo "<br>";
                        }
                    }
                    if (($linhasquerysubitem['value_type']) == 'enum')
                    {
                        echo "$linhasquerysubitem[name]: ";
                        if ($linhasquerysubitem['mandatory'] == '1')
                        {
                            echo "<red>*</red>";
                        }
                        $tipodevalor = $linhasquerysubitem['form_field_type'];
                        switch ($tipodevalor) {
                            case 'radio':
                                 if ($linhasquerysnameunidade != NULL)
                                 {
                                    echo "$linhasquerysnameunidade[name]";
                                 }
                                 $queryvalorespermitidosenumradio = "SELECT id ,value, subitem_id, state FROM subitem_allowed_value
                                                                    WHERE subitem_id = '$linhasquerysubitem[id]'";
                                 $resultadoqueryvalorespermitidosenumradio = mysqli_query($link, $queryvalorespermitidosenumradio);
                                 echo "<br>";

                                 while ($linhasvalorespermitidosenumradio = mysqli_fetch_assoc($resultadoqueryvalorespermitidosenumradio))
                                 {
                                     echo "<input type='radio' id='$linhasquerysubitem[id]' name='$linhasquerysubitem[id]'
                                          value='$linhasvalorespermitidosenumradio[value]'><label
                                          for='$linhasvalorespermitidosenumradio[value]'>
                                          $linhasvalorespermitidosenumradio[value]</label>
                                          <br>";
                                 }
                                 echo "<br>";
                            break;
                            case 'selectbox':
                                 if ($linhasquerysnameunidade != NULL)
                                 {
                                    echo "$linhasquerysnameunidade[name]";
                                 }
                                 $queryvalorespermitidosenumselect = "SELECT id ,value,subitem_id, state FROM subitem_allowed_value
                                                                     WHERE subitem_id = '$linhasquerysubitem[id]'";
                                 $resultadoqueryvalorespermitidosenumselect = mysqli_query($link, $queryvalorespermitidosenumselect);
                                 echo "<br>";
                                 echo "<select name='$linhasquerysubitem[id]'
                                       id='$linhasquerysubitem[id]'>";
                                 while ($linhasvalorespermitidosenumselect = mysqli_fetch_assoc($resultadoqueryvalorespermitidosenumselect))
                                 {
                                     echo "<option value='$linhasvalorespermitidosenumselect[value]'>
                                          $linhasvalorespermitidosenumselect[value]</option>";
                                 }
                                 echo "</select>";
                                 echo "<br>";
                                 echo "<br>";
                            break;
                            case 'checkbox':
                                if ($linhasquerysnameunidade != NULL)
                                {
                                    echo "$linhasquerysnameunidade[name]";
                                    echo "<br>";
                                }
                                $queryvalorespermitidosenumcheckbox = "SELECT id ,value, subitem_id, state FROM subitem_allowed_value
                                                                      WHERE subitem_id = '$linhasquerysubitem[id]'";
                                $resultadoqueryvalorespermitidosenumcheckbox = mysqli_query($link, $queryvalorespermitidosenumcheckbox);
                                echo "<br>";
                                while ($linhasvalorespermitidosenumcheckbox = mysqli_fetch_assoc($resultadoqueryvalorespermitidosenumcheckbox))
                                {
                                    echo "<input type='checkbox'
                                         id='$linhasvalorespermitidosenumcheckbox[id] '
                                         name='$linhasvalorespermitidosenumcheckbox[id]'
                                         value='$linhasvalorespermitidosenumcheckbox[value]'><label
                                         for='$linhasvalorespermitidosenumcheckbox[value]'>
                                         $linhasvalorespermitidosenumcheckbox[value]</label><br>";
                                }
                                echo "<br>";
                            break;
                        }
                    }
                }
            }
            echo '<button
                      type="submit">Submeter</button>
                      <input
                      type="hidden" name="estado" value="validar">';
            echo '</form>';
        }

    }

    else if ($_REQUEST['estado'] == 'validar')
    {
        $item_id_dasecao = $_SESSION['item'];
        $queryitemname = "SELECT name FROM item WHERE id='$item_id_dasecao'";
        $resultadoqueryname = mysqli_query($link, $queryitemname);
        $linhasquerynameid = mysqli_fetch_assoc($resultadoqueryname);
        $item_name = $linhasquerynameid['name'];
        echo "<h3>Inserção de valores-$item_name-validar<h3>";
        echo "Estamos prestes a incerir os dados abaixo na base de dados.";
        $querysubitenselecionados = "SELECT id,name,item_id,value_type,form_field_name,unit_type_id,form_field_type,form_field_order,state
                             FROM subitem WHERE state='active' AND item_id ='$item_id_dasecao' ORDER BY form_field_order";
        $resultadosubitenselecionados = mysqli_query($link, $querysubitenselecionados);
        while($colunasdossubitensselecionados= mysqli_fetch_assoc($resultadosubitenselecionados))
        {
            if (($colunasdossubitensselecionados['value_type']) == 'text') {
                echo "$colunasdossubitensselecionados[name]: ";
                $tipodevalor = $colunasdossubitensselecionados['form_field_type'];
                switch ($tipodevalor)
                {
                    case 'text':
                        echo $_REQUEST["$colunasdossubitensselecionados[id]texttext"];
                        echo "<br>";
                    break;

                    case 'textbox':
                         echo $_REQUEST["$colunasdossubitensselecionados[id]texttexbox"];
                         echo "<br>";
                    break;
                }
            }
            if (($colunasdossubitensselecionados['value_type']) == 'bool')
            {
                echo "$colunasdossubitensselecionados[name]: ";
                $queryvalorpermitidoselecionado = "SELECT id ,value, subitem_id, state FROM subitem_allowed_value
                                                   WHERE id = '$_REQUEST[id]'  ";
                $resultadovalorespermitidoselecionado = mysqli_query($link, $queryvalorpermitidoselecionado);
                $valorpermitidoselecionado = mysqli_fetch_assoc($resultadovalorespermitidoselecionado);
                echo $valorpermitidoselecionado['name'];
            }
            if(($colunasdossubitensselecionados['value_type']) == 'int')
            {
                echo "$colunasdossubitensselecionados[name]: ";
                echo $_REQUEST["$colunasdossubitensselecionados[id]inttext"];
                echo "<br>";
            }
            if (($colunasdossubitensselecionados['value_type']) == 'double')
            {
                echo "$colunasdossubitensselecionados[name]: ";
                echo $_REQUEST["$colunasdossubitensselecionados[id]doubletext"];
                echo "<br>";
            }
            if (($colunasdossubitensselecionados['value_type']) == 'enum') {
                echo "$colunasdossubitensselecionados[name]: ";
                $tipodevalor = $colunasdossubitensselecionados['form_field_type'];
                switch ($tipodevalor) {
                    case 'radio':
                        echo "$colunasdossubitensselecionados[name]: ";
                        $queryvalorpermitidoselecionado = "SELECT id ,value, subitem_id, state FROM subitem_allowed_value
                                                   WHERE name = '$_REQUEST[value]'  ";
                        $resultadovalorespermitidoselecionado = mysqli_query($link, $queryvalorpermitidoselecionado);
                        $valorpermitidoselecionado = mysqli_fetch_assoc($resultadovalorespermitidoselecionado);
                        echo $valorpermitidoselecionado['name'];
                    break;

                    case 'selectbox':
                        echo $_REQUEST["$colunasdossubitensselecionados[id]texttexbox"];
                        echo "<br>";
                    break;

                    case 'checkbox':
                        echo $_REQUEST["$colunasdossubitensselecionados[id]texttexbox"];
                        echo "<br>";
                    break;
                }
            }
        }
        echo "Confirma que os dados estao corretos e pretende submeter os mesmos ?<br>";
        echo '<button
                type="submit">Submeter</button>
             <input
                type="hidden" name="estado" value="inserir">';
    }

    else if ($_REQUEST['estado'] == 'inserir')
    {
        $idselecionado = $_SESSION['id'];
        $criancainicial = $_REQUEST['crianca'];
        //executar a query
        //queryinserirdados = "INSERT INTO `value` (`id`, `child_id`, `subitem_id`, `value`, `date`, `time`, `producer`)
        //                     VALUES (NULL, $criancainicial,$idselecionado,$REQUEST[valores],'','','')";
        //$resultadoinserir = mysqli_query($link, queryinserirdados);
        echo "<h3>Inserção de valores-$idselecionado-validar<h3>";
        echo "Inseriu os dados de um novo tipo de unidade com sucesso! Clique em continuar para anvançar";
        echo "<form action='http://localhost/sgbd/insercao-de-valores/?estado=escolher_item&crianca='$criancainicial''>
             <input type='submit' value='Escolher'>";
        echo "<br>";
        echo "<form action='http://localhost/sgbd/insercao-de-valores/'>
             <input type='submit' value='Voltar'>";
    }
}
?>
<script src="/sgbd/custom/js/script.js"></script>