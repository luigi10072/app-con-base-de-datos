// File: script.js
let token = localStorage.getItem('authToken');
let datos = []; // Almacenará los registros de Pokémon obtenidos de la API
let paginaActual = 1;
let registrosPorPagina = 10;
let textoFiltro = '';
let campoOrden = 'IDpoke'; // Campo de ordenación por defecto para la API (corresponde a 'numero' en el frontend)
let ordenAscendente = true;

// Configuración del diálogo para añadir/editar registro
$("#recordDialog").dialog({
    autoOpen: false,
    modal: true,
    width: 400,
    height: 500, // Ajustar altura al eliminar el campo de doble tipo
    buttons: {
        "Guardar": function () {
            guardarRegistro();
        },
        "Cancelar": function () {
            $(this).dialog("close");
        }
    },
    close: function () {
        // Limpiar campos al cerrar el diálogo (sin isDualType)
        $('#dialogIDpoke').val('').prop('readonly', false);
        $('#dialogPokename').val('');
        $('#dialogHP').val('');
        $('#dialogAttack').val('');
        $('#dialogDefense').val('');
        $('#dialogSpattack').val('');
        $('#dialogSpdefense').val('');
        $('#dialogSpeed').val('');
    }
});

// --- Logica de Login/Logout ---
function mostrarContenidoAutenticado() {
    $('#loginForm').hide();
    $('#content').show();
    cargarDatosDesdeAPI(); // Cargar datos si está autenticado
}

function mostrarFormularioLogin() {
    $('#content').hide();
    $('#loginForm').show();
    $('#loginMessage').text('');
    $('#username').val('');
    $('#password').val('');
}

// Lógica para el login
$('#loginForm').on('submit', function (e) {
    e.preventDefault();
    const username = $('#username').val();
    const password = $('#password').val();

    $.ajax({
        url: 'api/login.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ username: username, password: password }),
        success: function (response) {
            token = response.token;
            localStorage.setItem('authToken', token);
            mostrarContenidoAutenticado();
        },
        error: function (xhr) {
            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error de red o del servidor';
            $('#loginMessage').text(errorMsg);
        }
    });
});

// Lógica para el logout
$('#logoutBtn').on('click', function () {
    token = null;
    localStorage.removeItem('authToken');
    mostrarFormularioLogin();
});

// --- Carga de datos desde la API ---
function cargarDatosDesdeAPI() {
    if (!token) {
        mostrarFormularioLogin();
        return;
    }

    $.ajax({
        url: `api/data.php?search=${textoFiltro}&limit=${registrosPorPagina}&offset=${(paginaActual - 1) * registrosPorPagina}&sort=${campoOrden}&order=${ordenAscendente ? 'asc' : 'desc'}`,
        method: 'GET',
        headers: { 'Authorization': 'Bearer ' + token },
        success: function (response) {
            if (response.records && Array.isArray(response.records)) {
                datos = response.records;
                $('#totalRegistros').text(`Total de registros: ${response.totalRecords}`);
                renderizarTabla();
                generarPaginacion(response.totalRecords);
            } else {
                console.error("Formato de respuesta inesperado:", response);
                datos = [];
                $('#totalRegistros').text('Total de registros: 0');
                renderizarTabla();
                generarPaginacion(0);
            }
        },
        error: function (xhr) {
            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al obtener datos de Pokémon';
            alert(errorMsg);
            console.error("Error al cargar datos:", xhr.responseText);
            datos = [];
            $('#totalRegistros').text('Total de registros: 0');
            renderizarTabla();
            generarPaginacion(0);
            if (xhr.status === 401) { // Redirigir al login si el token no es válido
                mostrarFormularioLogin();
            }
        }
    });
}

// --- Renderizado de la tabla ---
function renderizarTabla() {
    const tbody = $('#tabla tbody');
    tbody.empty();

    for (const fila of datos) {
        const tr = $('<tr></tr>');
        tr.append(`<td>${fila.numero}</td>`);
        tr.append(`<td>${fila.especie}</td>`);
        tr.append(`<td>${fila.HP}</td>`);
        tr.append(`<td>${fila.attack}</td>`);
        tr.append(`<td>${fila.defense}</td>`);
        tr.append(`<td>${fila.spattack}</td>`);
        tr.append(`<td>${fila.spdefense}</td>`);
        tr.append(`<td>${fila.speed}</td>`);
        // Eliminado: tr.append(`<td>${fila.isDualType ? 'Sí' : 'No'}</td>`); 
        tr.append(`<td>
            <button class="edit-btn ui-button ui-corner-all ui-widget" data-id="${fila.numero}">Editar</button>
            <button class="delete-btn ui-button ui-corner-all ui-widget" data-id="${fila.numero}">Eliminar</button>
        </td>`);
        tbody.append(tr);
    }
}

// --- Paginación ---
function generarPaginacion(totalRecordsAPI) {
    const totalPaginas = Math.ceil(totalRecordsAPI / registrosPorPagina);
    const contenedor = $('#paginacion');
    contenedor.empty();

    if (totalPaginas <= 1) {
        return;
    }

    for (let i = 1; i <= totalPaginas; i++) {
        const boton = $(`<button class="ui-button ui-corner-all ui-widget">${i}</button>`);
        if (i === paginaActual) {
            boton.addClass('ui-state-active');
        }
        boton.on('click', function () {
            paginaActual = i;
            cargarDatosDesdeAPI();
        });
        contenedor.append(boton);
    }
}

// --- Lógica para guardar/actualizar registro ---
function guardarRegistro() {
    const isEdit = $('#dialogIDpoke').val() !== '';
    const IDpoke = $('#dialogIDpoke').val();
    const pokename = $('#dialogPokename').val();
    const HP = $('#dialogHP').val();
    const attack = $('#dialogAttack').val();
    const defense = $('#dialogDefense').val();
    const spattack = $('#dialogSpattack').val();
    const spdefense = $('#dialogSpdefense').val();
    const speed = $('#dialogSpeed').val();
    // Eliminado: const isDualType = $('#dialogIsDualType').is(':checked') ? 1 : 0; 

    if (!pokename || HP === '' || attack === '' || defense === '' || spattack === '' || spdefense === '' || speed === '') {
        alert("Por favor, rellene todos los campos numéricos y el nombre.");
        return;
    }

    const dataToSend = {
        pokename: pokename,
        HP: parseInt(HP),
        attack: parseInt(attack),
        defense: parseInt(defense),
        spattack: parseInt(spattack),
        spdefense: parseInt(spdefense),
        speed: parseInt(speed)
        // Eliminado: isDualType: isDualType
    };

    let url = 'api/records/add.php';
    let method = 'POST';

    if (isEdit) {
        url = 'api/records/edit.php';
        dataToSend.IDpoke = parseInt(IDpoke);
    }

    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(dataToSend),
        headers: { 'Authorization': 'Bearer ' + token },
        success: function (response) {
            alert(response.message);
            $("#recordDialog").dialog("close");
            cargarDatosDesdeAPI();
        },
        error: function (xhr) {
            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error de red o del servidor';
            console.error("Error al guardar el registro:", xhr.responseText);
            alert("Error al guardar el registro: " + errorMsg);
        }
    });
}

// --- Lógica de borrado de registro ---
$('#tabla tbody').on('click', '.delete-btn', function () {
    const idToDelete = $(this).data('id');
    if (confirm(`¿Estás seguro de que quieres eliminar el Pokémon con ID ${idToDelete}?`)) {
        $.ajax({
            url: 'api/records/delete.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ IDpoke: idToDelete }),
            headers: { 'Authorization': 'Bearer ' + token },
            success: function (response) {
                alert(response.message);
                cargarDatosDesdeAPI();
            },
            error: function (xhr) {
                const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al eliminar el registro';
                console.error("Error al eliminar el registro:", xhr.responseText);
                alert("Error al eliminar el registro: " + errorMsg);
            }
        });
    }
});

// --- Lógica para abrir diálogo de añadir/editar ---
function abrirDialogo(mode, record = null) {
    // Resetear campos al abrir (sin isDualType)
    $('#dialogIDpoke').val('').prop('readonly', false);
    $('#dialogPokename').val('');
    $('#dialogHP').val('');
    $('#dialogAttack').val('');
    $('#dialogDefense').val('');
    $('#dialogSpattack').val('');
    $('#dialogSpdefense').val('');
    $('#dialogSpeed').val('');

    if (mode === 'add') {
        $("#recordDialog").dialog("option", "title", "Añadir Nuevo Pokémon");
    } else if (mode === 'edit' && record) {
        $("#recordDialog").dialog("option", "title", "Editar Pokémon");
        $('#dialogIDpoke').val(record.numero).prop('readonly', true);
        $('#dialogPokename').val(record.especie);
        $('#dialogHP').val(record.HP);
        $('#dialogAttack').val(record.attack);
        $('#dialogDefense').val(record.defense);
        $('#dialogSpattack').val(record.spattack);
        $('#dialogSpdefense').val(record.spdefense);
        $('#dialogSpeed').val(record.speed);
        // Eliminado: $('#dialogIsDualType').prop('checked', record.isDualType === 1);
    }
    $("#recordDialog").dialog("open");
}

// --- Event Listeners ---
$(document).ready(function () {
    if (token) {
        mostrarContenidoAutenticado();
    } else {
        mostrarFormularioLogin();
    }

    // Evento de búsqueda (ahora solo busca por nombre de especie)
    $('#buscar').on('input', function () {
        textoFiltro = $(this).val();
        paginaActual = 1;
        cargarDatosDesdeAPI();
    });

    // Evento de cambio de registros por página
    $('#registrosPagina').selectmenu({
        width: 90
    }).on('selectmenuchange', function () {
        registrosPorPagina = parseInt($(this).val(), 10);
        paginaActual = 1;
        cargarDatosDesdeAPI();
    });

    // Evento de ordenamiento de tabla
    $('#tabla thead th').on('click', function () {
        const campo = $(this).data('campo');
        if (!campo) return; // Si no hay campo definido, salir

        let apiSortField = campo;
        switch(campo) {
            case 'numero': apiSortField = 'IDpoke'; break;
            case 'especie': apiSortField = 'pokename'; break;
            case 'HP': apiSortField = 'HP'; break;
            case 'attack': apiSortField = 'attack'; break;
            case 'defense': apiSortField = 'defense'; break;
            case 'spattack': apiSortField = 'spattack'; break;
            case 'spdefense': apiSortField = 'spdefense'; break;
            case 'speed': apiSortField = 'speed'; break;
            // Eliminado: case 'isDualType': apiSortField = 'is_dual_type'; break;
            default: apiSortField = 'IDpoke';
        }

        if (campoOrden === apiSortField) {
            ordenAscendente = !ordenAscendente;
        } else {
            campoOrden = apiSortField;
            ordenAscendente = true;
        }
        cargarDatosDesdeAPI();
    });

    // Evento para botón Añadir
    $('#addRecordBtn').on('click', function () {
        abrirDialogo('add');
    });

    // Evento para botones Editar (delegado)
    $('#tabla tbody').on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        $.ajax({
            url: `api/data.php?search=${id}&limit=1&offset=0&sort=IDpoke&order=asc`,
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function (response) {
                if (response.records.length > 0) {
                    abrirDialogo('edit', response.records[0]);
                } else {
                    alert("Registro no encontrado.");
                }
            },
            error: function (xhr) {
                const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al obtener registro para editar';
                alert(errorMsg);
                console.error("Error al obtener registro para editar:", xhr.responseText);
            }
        });
    });
});