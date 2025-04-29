export function formatDecimal(num) {
    const numFormatWithDecimal = (Math.trunc(num * 100) / 100).toFixed(2);
    // console.log(numFormatWithDecimal);
  
    const [int, decimal] = numFormatWithDecimal.split(".");
    // console.log(int, decimal);
  
    
    return decimal > 0 ? numFormatWithDecimal : int;
  }