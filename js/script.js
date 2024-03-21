function isBissexto(ano) {
    if (ano % 4 === 0 && (ano % 100 !== 0 || ano % 400 === 0)) {
        return true;
    } else {
        return false;
    }
}

function FormValidacaoReg() {
    var nome_crianca = document.getElementById("nome_crianca").value;
    var crianca_nascimento = document.getElementById("crianca_nascimento").value;
    var nome_enc = document.getElementById("nome_enc").value;
    var telf_enc = document.getElementById("telf_enc").value;
    var email_enc = document.getElementById("email_enc").value;

    
    var temp = crianca_nascimento.split('-');
    var ano = parseInt(temp[0], 10);
    var mes = parseInt(temp[1], 10);
    var dia = parseInt(temp[2], 10);
    var dataAtual = new Date();
    var anoAtual = dataAtual.getFullYear();
    var mesAtual = dataAtual.getMonth() + 1;
    var diaAtual = dataAtual.getDate();

    if (nome_crianca === "" || crianca_nascimento === "" || nome_enc === "" || telf_enc === "") {
        alert("Deve preencher todos os campos, exceto o email");
        return false;
    }
    if (!/^[\p{L}\s]+$/u.test(nome_crianca)) {
        alert("Introduza um nome válido para a criança");
        return false;
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(crianca_nascimento)) {
        alert("Introduza uma data de nascimento válida");
        return false;
    }
    if (!/^[\p{L}\s]+$/u.test(nome_enc)) {
        alert("Introduza um nome válido para o encarregado");
        return false;
    }
    if (!/^\d{9}$/.test(telf_enc)) {
        alert("Introduza um numero de telemóvel válido");
        return false;
    }
    if (email_enc !== "" && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email_enc)) {
        alert("Introduza um email válido");
        return false;
    }
    if (ano >= 2005 && ano <= anoAtual && mes >= 1 && mes <= 12) {
        if ((mes === 1 || mes === 3 || mes === 5 || mes === 7 || mes === 8 || mes === 10 || mes === 12) && dia >= 1 && dia <= 31) {
            if (ano < anoAtual || (ano === anoAtual && (mes < mesAtual || (mes === mesAtual && dia <= diaAtual)))) {
                return true;
            }
        } else if ((mes === 4 || mes === 6 || mes === 9 || mes === 11) && dia >= 1 && dia <= 30) {
            if (ano < anoAtual || (ano === anoAtual && (mes < mesAtual || (mes === mesAtual && dia <= diaAtual)))) {
                return true;
            }
        } else if ((mes === 2) && ((dia >= 1 && dia <= 28) || (dia === 29 && isBissexto(ano)))) {
            if (ano < anoAtual || (ano === anoAtual && (mes < mesAtual || (mes === mesAtual && dia <= diaAtual)))) {
                return true;
            }
        }
    }
    
    alert("Introduza uma data de nascimento válida");
    return false;
}

  function formValidacaoItems() {
    var nome_item = document.getElementById("nome_item").value;
    var item_tipo = document.querySelector('input[name="item_tipo"]:checked');
    var item_estado = document.querySelector('input[name="item_estado"]:checked');

    if (nome_item === "") {
        alert("Deve preencher todos os campos");
        return false;
    }

    if (!/^[\p{L}\s]+$/u.test(nome_item)) {
        alert("Introduza um nome válido para o item");
        return false;
    }

    if (!item_tipo) {
        alert("Deve preencher todos os campos");
        return false;
    }

    if (!item_estado) {
        alert("Deve preencher todos os campos");
        return false;
    }

    return true;
}
function validargestaounidades(){
    var nomeunidade = document.getElementById("nomeunidade").value;
    if (nomeunidade === "") {
       alert("Insira um nome !!!");
       return false;
    }
    if (!/^[ a-zA-Z\\ ]+$/.test(nomeunidade) && !/^[a-zA-Z]+[0-9]+/.test(nomeunidade) && !/^[a-zA-Z]+/.test(nomeunidade)) {
       alert("Introduza um nome de unidade vÃ¡lido!!!");
       return false;
    }
    return true;
    
}
