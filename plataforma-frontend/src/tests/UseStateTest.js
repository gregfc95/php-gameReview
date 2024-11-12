import React, {useEffect, useState}  from "react";
//UseEffect para traer los juegos
//UseState para guardar los juegos
//Ejemplo con un contador

function countInitial() {
    console.log('Run function');
    return 4;
}

function UseStateTest() {
    //useState(0), renders everytime we run our function increment or decrement
    //Using the function version run only the first time that the component is rendered
    const [count, setCount] = useState(() => {
        countInitial();
    });
    const [theme, setTheme] = useState(() => {
        return 'Blue';
    });

    function increment() {
        setCount(count + 1);
        //Using the function arrow version, the previous value is passed automatically into the function after the arrow
        setCount(prevCount => prevCount + 1);
        setTheme('Red');

    }
    function decrement() {
        setCount(prevCount => prevCount - 1);
    }
    
    return (

        <>
          <button onClick={increment}>+</button>
          <span>{count}</span>
          <span>{theme}</span>  
          <button onClick={decrement}>-</button>
        </>
    )


}

export default UseStateTest;