export function primeiraPalavra(strg) {
    let stringVerificada = strg || 'empty';
    let firstWord = stringVerificada.split(' ')[0];// separar str por espaços

    //console.log(firtWord);
    return firstWord;
}

export function onlyNumbers(str) {
    return str.replace(/[^0-9]/g, '');
}

export function formatToIdCode(numero, tamanho=3) {
    const num = numero || 0;

    return num.toString().padStart(tamanho, '0');
}