let mapa;

let marcadoresUrgentes = [];
let marcadoresRevision = [];
let marcadoresPersonal = [];

function inicializarMapa() {

    const sanSalvador = {
        lat: 13.6929,
        lng: -89.2182
    };

    mapa = new google.maps.Map(
        document.getElementById("map"),
        {
            zoom: 12,
            center: sanSalvador,
            mapTypeControl: false,
            streetViewControl: false
        }
    );

    cargarMarcadores();
}

function cargarMarcadores() {

    // ==========================
    // INCIDENCIAS URGENTES
    // ==========================

    const urgente1 = crearMarcador(
        {
            lat: 13.6990,
            lng: -89.1910
        },
        "Luminaria dañada",
        "#E85D5D",
        "!"
    );

    const urgente2 = crearMarcador(
        {
            lat: 13.7045,
            lng: -89.2150
        },
        "Infraestructura dañada",
        "#E85D5D",
        "!"
    );

    marcadoresUrgentes.push(
        urgente1,
        urgente2
    );

    // ==========================
    // EN REVISION
    // ==========================

    const revision1 = crearMarcador(
        {
            lat: 13.6800,
            lng: -89.2200
        },
        "Basura acumulada",
        "#C4AF59",
        "~"
    );

    const revision2 = crearMarcador(
        {
            lat: 13.6880,
            lng: -89.2050
        },
        "Vegetación excesiva",
        "#C4AF59",
        "~"
    );

    marcadoresRevision.push(
        revision1,
        revision2
    );

    // ==========================
    // PERSONAL MUNICIPAL
    // ==========================

    const personal1 = crearMarcador(
        {
            lat: 13.6955,
            lng: -89.2250
        },
        "Ana Flores",
        "#2290BF",
        "A"
    );

    const personal2 = crearMarcador(
        {
            lat: 13.6875,
            lng: -89.1950
        },
        "Carlos Ruiz",
        "#2290BF",
        "C"
    );

    marcadoresPersonal.push(
        personal1,
        personal2
    );
}

function crearMarcador(
    posicion,
    titulo,
    color,
    texto
) {

    const marcador = new google.maps.Marker({

        position: posicion,

        map: mapa,

        title: titulo,

        icon: {

            path: google.maps.SymbolPath.CIRCLE,

            fillColor: color,

            fillOpacity: 1,

            strokeColor: "#FFFFFF",

            strokeWeight: 2,

            scale: 16

        }

    });

    const infoWindow =
        new google.maps.InfoWindow({

            content: `
                <div style="padding:5px;">
                    <strong>${titulo}</strong>
                </div>
            `
        });

    marcador.addListener(
        "click",
        () => {
            infoWindow.open(
                mapa,
                marcador
            );
        }
    );

    return marcador;
}

/* ==========================
   FILTROS
========================== */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        const btnUrgente =
            document.getElementById(
                "btnUrgente"
            );

        const btnRevision =
            document.getElementById(
                "btnRevision"
            );

        let urgenteActivo = true;
        let revisionActivo = true;

        btnUrgente.addEventListener(
            "click",
            () => {

                urgenteActivo =
                    !urgenteActivo;

                marcadoresUrgentes.forEach(
                    marcador => {
                        marcador.setVisible(
                            urgenteActivo
                        );
                    }
                );

                btnUrgente.classList.toggle(
                    "activo"
                );

            }
        );

        btnRevision.addEventListener(
            "click",
            () => {

                revisionActivo =
                    !revisionActivo;

                marcadoresRevision.forEach(
                    marcador => {
                        marcador.setVisible(
                            revisionActivo
                        );
                    }
                );

                btnRevision.classList.toggle(
                    "activo"
                );

            }
        );

    }
);