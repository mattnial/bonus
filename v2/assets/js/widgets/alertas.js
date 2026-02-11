/** WIDGET: SISTEMA DE ALERTAS (SWEETALERT2) **/
window.notificar = {
    // ÉXITO: Notificación pequeña en la esquina (Toast)
    exito: (mensaje) => {
        Swal.fire({
            icon: 'success',
            title: mensaje,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    },

    // ERROR: Ventana central roja
    error: (mensaje) => {
        Swal.fire({
            icon: 'error',
            title: 'Ocurrió un error',
            text: mensaje,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Cerrar'
        });
    },

    // CONFIRMACIÓN: Reemplaza al 'confirm()' nativo
    preguntar: (titulo, texto, accionConfirmada) => {
        Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33', // Rojo para peligro
            cancelButtonColor: '#3085d6', // Azul para cancelar
            confirmButtonText: 'Sí, ejecutar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                accionConfirmada();
            }
        });
    }
};