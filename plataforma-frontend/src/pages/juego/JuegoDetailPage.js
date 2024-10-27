import React from "react";
import { useParams } from "react-router-dom";

/*
    *JuegoDetailPage: component renders the detail of a single game
*/

function JuegoDetailPage() {
    const { id } = useParams();
    return <div>Detalles del juego ID: {id}</div>
};

export default JuegoDetailPage