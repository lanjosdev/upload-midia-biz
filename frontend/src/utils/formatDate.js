// import { format } from "date-fns";

export function formatFullToHoursMinutes(dateString) {
    if(!dateString) {
        return 'N/A';
    }

    const result = dateString.substring(0, 16);

    // console.log(result); // Saída: "11/12/2024 17:20"
    return result;
}

export function formatMinDateCalender(date, notTime=false) {
    const adicional = 1;

    // Obtém os componentes individuais da data 
    const year = date.getFullYear(); 
    const month = String(date.getMonth() + 1).padStart(2, '0'); 
    const day = String(date.getDate()).padStart(2, '0'); 

    if(notTime) return `${year}-${month}-${day}`;
    
    const hoursCurrent = date.getHours();
    const hours = String((hoursCurrent == 23 ? -1 : hoursCurrent) + adicional).padStart(2, '0'); 
    const minutes = String(date.getMinutes()).padStart(2, '0'); 
    // Constrói a string no formato desejado 
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

export function formatDateAmerican(dateString, notTime=false) {
    if(!dateString) {
        return '';
    }

    const [datePart, timePart] = dateString.split(' ');
    const [day, month, year] = datePart.split('/');

    if(notTime) return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;


    const [hours, minutes] = timePart.split(':');
    
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}T${hours}:${minutes}`;
}

export function formatDateAmericanMinDate(dateString) {
    if(!dateString) {
        return 'Erro';
    }

    const adicional = 1;
    
    const [datePart, timePart] = dateString.split(' ');
    const [day, month, year] = datePart.split('/');
    const [hours, minutes] = timePart.split(':');
    let hoursWithAdicional = parseInt(hours) + adicional;
    let hoursFormated = hoursWithAdicional.toString().padStart(2, '0');

    const result = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}T${hoursFormated}:${minutes}`;
    
    return result;
}