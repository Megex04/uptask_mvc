(function () {
    // boton para mostrar el modal de Agregar tarea
    const nuevaTareaBtn = document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click', mostrarFormulario);

    function mostrarFormulario() {
        const modal = document.createElement('div');
        modal.classList.add('modal');
        modal.innerHTML = `
            <form class="formulario  nueva-tarea">
                <legend>Añade una nueva tarea</legend>
                <div class="campo">
                    <label>Tarea</label>
                    <input 
                    type="text"
                    name="tarea"
                    placeholder="Añadir Tarea al proyecto actual"
                    id="tarea"
                    >
                </div>
                <div class="opciones">
                    <input type="submit" class="submit-nueva-tarea" value="Añadir Tarea" />
                    <button type="button" class="cerrar-modal">Cancelar</button>
                </div>
            </form>
        `;

        setTimeout(() => {
            const formulario = document.querySelector('.formulario');
            formulario.classList.add('animar');
        }, 1000);

        modal.addEventListener('click', function (e) {
            e.preventDefault();

            if(e.target.classList.contains('cerrar-modal')) {
                const formulario = document.querySelector('.formulario');
                formulario.classList.add('cerrar');
                setTimeout(() => {
                    modal.remove();
                }, 500);
            }
            if(e.target.classList.contains('submit-nueva-tarea')) {
                submitFomularioNuevaTarea()
            }
        });

        document.querySelector('.dashboard').appendChild(modal);
    }
    function submitFomularioNuevaTarea() {
        const tarea = document.querySelector('#tarea').value.trim();

        if(tarea === '') {
            //mostrar alerta de error
            mostrarAlerta('Debe ingresar el nombre de una tarea', 'error', document.querySelector('.formulario legend'));
            return;
        }
        agregarTarea(tarea);
    }
    // muestra un mensaje en la interfaz
    function mostrarAlerta(mensaje, tipo, referencia) {
        // previene la creacion de varias alertas
        const alertaPrevia = document.querySelector('.alerta');
        if(alertaPrevia){
            alertaPrevia.remove();
        }

        const alerta = document.createElement('div');
        alerta.classList.add('alerta', tipo);
        alerta.textContent = mensaje;
        
        // inserta la alerta despues de legend
        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);

        setTimeout(() => {
            alerta.remove();
        }, 5000);
    }
    //TODO: IMPORTANTE: CONSULTAR EL SERVIDOR PARA AÑADIR UNA NUEVA TAREA AL PROYECTO ACTUAL
    async function agregarTarea(tarea) {
        // construir el request
        const datos = new FormData();
        datos.append('nombre', tarea);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = 'http://localhost:3000/api/tarea';
            const response = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const result = await response.json();
            // console.log(result);

            mostrarAlerta(result.mensaje, result.tipo, document.querySelector('.formulario legend'));
            if(result.tipo === 'exito') {
                const modal = document.querySelector('.modal');
                setTimeout(() => {
                    modal.remove();
                }, 4000);
            }
        } catch (error) {
            console.log(error);
        }
    }

    // UTIL
    function obtenerProyecto() {
        const proyectoParams = new URLSearchParams(window.location.search);
        const proyecto = Object.fromEntries(proyectoParams.entries());
        return proyecto.id;
    }

})();