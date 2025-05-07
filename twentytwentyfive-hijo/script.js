
document.addEventListener('DOMContentLoaded', function () {
    // Cambio de tema claro/oscuro
    const themeToggleBtn = document.getElementById('themeToggle');
    const body = document.body;

    themeToggleBtn.addEventListener('click', function () {
        if (body.classList.contains('dark-mode')) {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            body.classList.remove('light-mode');
            body.classList.add('dark-mode');
            themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
    });

    // Slider de experiencia
    const experienciaSlider = document.getElementById('experiencia');
    const experienciaValor = document.getElementById('experienciaValor');

    experienciaSlider.addEventListener('input', function () {
        experienciaValor.textContent = this.value + ' años';
    });

    // Vista previa de logo
    const logoInput = document.getElementById('logoEquipo');
    const logoPreview = document.getElementById('logoPreview');

    logoInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                logoPreview.src = e.target.result;
                logoPreview.style.display = 'block';
            }

            reader.readAsDataURL(this.files[0]);
        }
    });

    // Selección de plataformas
    const platformButtons = document.querySelectorAll('.platform-btn');
    const plataformasInput = document.getElementById('plataformasSeleccionadas');
    const plataformasSeleccionadas = new Set();

    platformButtons.forEach(button => {
        button.addEventListener('click', function () {
            const platform = this.dataset.platform;

            if (this.classList.contains('active')) {
                this.classList.remove('active');
                plataformasSeleccionadas.delete(platform);
            } else {
                this.classList.add('active');
                plataformasSeleccionadas.add(platform);
            }

            plataformasInput.value = Array.from(plataformasSeleccionadas).join(',');
        });
    });

    // Vista previa de portafolio
    const portfolioInput = document.getElementById('portfolioDemo');
    const portfolioPreview = document.getElementById('portfolioPreview');

    portfolioInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];

            // Crear elemento de información de archivo
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-info';
            fileInfo.innerHTML = `
                <p><strong>${file.name}</strong></p>
                <p>Tamaño: ${(file.size / (1024 * 1024)).toFixed(2)} MB</p>
                <p>Tipo: ${file.type || 'application/zip'}</p>
            `;

            // Limpiar vista previa anterior
            portfolioPreview.innerHTML = '';
            portfolioPreview.appendChild(fileInfo);
        }
    });

    // Contador de tiempo restante
    const countDownDate = new Date("June 15, 2025 18:00:00").getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = countDownDate - now;

        const dias = Math.floor(distance / (1000 * 60 * 60 * 24));
        const horas = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutos = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("dias").innerText = dias;
        document.getElementById("horas").innerText = horas;
        document.getElementById("minutos").innerText = minutos;
        document.getElementById("segundos").innerText = segundos;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();

    // Validación de formulario
    const form = document.getElementById('registroForm');
    const nombreEquipoInput = document.getElementById('nombreEquipo');
    const nombreLiderInput = document.getElementById('nombreLider');
    const emailContactoInput = document.getElementById('emailContacto');
    const descripcionJuegoInput = document.getElementById('descripcionJuego');

    // Función para validar email
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    // Validación en tiempo real para nombre de equipo
    nombreEquipoInput.addEventListener('input', function () {
        const errorElement = document.getElementById('nombreEquipo-error');
        const successIcon = this.nextElementSibling;
        const errorIcon = successIcon.nextElementSibling;

        if (this.value.trim() === '' || this.value.length > 30) {
            this.classList.add('error');
            errorElement.style.display = 'block';
            successIcon.style.display = 'none';
            errorIcon.style.display = 'inline';
        } else {
            this.classList.remove('error');
            errorElement.style.display = 'none';
            successIcon.style.display = 'inline';
            errorIcon.style.display = 'none';
        }
    });

    // Validación en tiempo real para nombre del líder
    nombreLiderInput.addEventListener('input', function () {
        const errorElement = document.getElementById('nombreLider-error');
        const successIcon = this.nextElementSibling;
        const errorIcon = successIcon.nextElementSibling;

        if (this.value.trim() === '') {
            this.classList.add('error');
            errorElement.style.display = 'block';
            successIcon.style.display = 'none';
            errorIcon.style.display = 'inline';
        } else {
            this.classList.remove('error');
            errorElement.style.display = 'none';
            successIcon.style.display = 'inline';
            errorIcon.style.display = 'none';
        }
    });

    // Validación en tiempo real para email
    emailContactoInput.addEventListener('input', function () {
        const errorElement = document.getElementById('emailContacto-error');
        const successIcon = this.nextElementSibling;
        const errorIcon = successIcon.nextElementSibling;

        if (!isValidEmail(this.value)) {
            this.classList.add('error');
            errorElement.style.display = 'block';
            successIcon.style.display = 'none';
            errorIcon.style.display = 'inline';
        } else {
            this.classList.remove('error');
            errorElement.style.display = 'none';
            successIcon.style.display = 'inline';
            errorIcon.style.display = 'none';
        }
    });

    // Función para enviar solicitud AJAX
    function sendAjaxRequest(url, formData, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                callback(response);
            } else {
                showToast('Error en la conexión con el servidor');
            }
        };
        xhr.onerror = function() {
            showToast('Error en la conexión con el servidor');
        };
        xhr.send(formData);
    }

    // Envío del formulario
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Verificar si se ha seleccionado al menos una categoría
        const categoriasSeleccionadas = document.querySelectorAll('input[name="categorias[]"]:checked');
        if (categoriasSeleccionadas.length === 0) {
            document.getElementById('categories').scrollIntoView();
            showToast('Debes seleccionar al menos una categoría de juego');
            return;
        }

        // Verificar si se ha seleccionado al menos una plataforma
        if (plataformasInput.value === '') {
            document.getElementById('technical').scrollIntoView();
            showToast('Debes seleccionar al menos una plataforma objetivo');
            return;
        }

        // Mostrar loader
        document.getElementById('submitLoader').style.display = 'block';
        document.getElementById('submitBtn').disabled = true;

        // Crear FormData para envío de archivos
        const formData = new FormData(form);
        formData.append('accion', 'registrar_equipo');
        
        // Enviar al servidor
        sendAjaxRequest('ajax_handler.php', formData, function(response) {
            // Ocultar loader
            document.getElementById('submitLoader').style.display = 'none';
            document.getElementById('submitBtn').disabled = false;
            
            if (response.exito) {
                // Mostrar mensaje de éxito
                showToast(response.mensaje);
                
                // Actualizar contador de participantes
                if (response.datos && response.datos.total_equipos) {
                    document.getElementById('totalParticipantes').innerText = response.datos.total_equipos;
                }
                
                // Resetear formulario
                form.reset();
                logoPreview.style.display = 'none';
                portfolioPreview.innerHTML = '';
                
                // Desactivar todos los botones de plataforma
                platformButtons.forEach(button => {
                    button.classList.remove('active');
                });
                plataformasSeleccionadas.clear();
                plataformasInput.value = '';
            } else {
                // Mostrar mensaje de error
                showToast(response.mensaje);
            }
        });
    });

    // Cargar registros desde el servidor
    function loadRegistrations() {
        const tableBody = document.querySelector('#registrosTable tbody');
        
        // Mostrar indicador de carga
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Cargando registros...</td></tr>';
        
        const formData = new FormData();
        formData.append('accion', 'obtener_equipos');
        
        sendAjaxRequest('ajax_handler.php', formData, function(response) {
            if (response.exito) {
                // Actualizar tabla con HTML recibido
                tableBody.innerHTML = response.datos.html_tabla;
                
                // Actualizar contador si está disponible
                if (response.datos.total_equipos) {
                    document.getElementById('totalParticipantes').innerText = response.datos.total_equipos;
                }
                
                // Asignar eventos a botones
                addEventListenersToButtons();
            } else {
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Error al cargar los registros</td></tr>';
                showToast(response.mensaje);
            }
        });
    }

    // Añadir event listeners a los botones de la tabla
    function addEventListenersToButtons() {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const equipoId = parseInt(this.dataset.id);
                deleteRegistration(equipoId);
            });
        });
        
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const equipoId = parseInt(this.dataset.id);
                viewRegistration(equipoId);
            });
        });
    }

    // Eliminar registro
    function deleteRegistration(equipoId) {
        if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
            const formData = new FormData();
            formData.append('accion', 'eliminar_equipo');
            formData.append('equipo_id', equipoId);
            
            sendAjaxRequest('ajax_handler.php', formData, function(response) {
                if (response.exito) {
                    // Mostrar mensaje de éxito
                    showToast(response.mensaje);
                    
                    // Actualizar tabla
                    loadRegistrations();
                    
                    // Actualizar contador si está disponible
                    if (response.datos && response.datos.total_equipos) {
                        document.getElementById('totalParticipantes').innerText = response.datos.total_equipos;
                    }
                } else {
                    showToast(response.mensaje);
                }
            });
        }
    }

    // Ver detalles de registro
    function viewRegistration(equipoId) {
        const formData = new FormData();
        formData.append('accion', 'ver_equipo');
        formData.append('equipo_id', equipoId);
        
        sendAjaxRequest('ajax_handler.php', formData, function(response) {
            if (response.exito && response.datos && response.datos.equipo) {
                const equipo = response.datos.equipo;
                
                // Aquí puedes crear un modal con los detalles del equipo
                // Por ejemplo:
                showDetailModal(equipo);
            } else {
                showToast(response.mensaje || 'Error al cargar los detalles del equipo');
            }
        });
    }
    
    // Función para mostrar modal con detalles del equipo
    function showDetailModal(equipo) {
        // Crear modal (puedes adaptar esto según tu diseño)
        const modal = document.createElement('div');
        modal.className = 'detail-modal';
        
        // Mapeo de valores de tamaño de equipo a texto
        const tamanoEquipoMap = {
            'solo': '1 persona',
            'pequeno': '2-3 personas',
            'mediano': '4-6 personas',
            'grande': '7+ personas'
        };
        
        // Categorías como badges
        const categoriasBadges = equipo.categorias.map(cat =>
            `<span class="badge">${cat}</span>`
        ).join(' ');
        
        // Plataformas como badges
        const plataformasBadges = equipo.plataformas.map(plat =>
            `<span class="badge">${plat}</span>`
        ).join(' ');
        
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>${equipo.nombre_equipo}</h2>
                <div class="modal-body">
                    <div class="modal-row">
                        <strong>Líder del equipo:</strong> ${equipo.nombre_lider}
                    </div>
                    <div class="modal-row">
                        <strong>Tamaño del equipo:</strong> ${tamanoEquipoMap[equipo.tamano_equipo]}
                    </div>
                    <div class="modal-row">
                        <strong>Email de contacto:</strong> ${equipo.email_contacto}
                    </div>
                    <div class="modal-row">
                        <strong>Experiencia:</strong> ${equipo.experiencia} años
                    </div>
                    <div class="modal-row">
                        <strong>Categorías:</strong> ${categoriasBadges}
                    </div>
                    <div class="modal-row">
                        <strong>Plataformas:</strong> ${plataformasBadges}
                    </div>
                    <div class="modal-row">
                        <strong>Motor:</strong> ${equipo.motor}
                    </div>
                    <div class="modal-row">
                        <strong>Descripción del juego:</strong>
                        <p>${equipo.descripcion_juego}</p>
                    </div>
                    <div class="modal-row">
                        <strong>Comentarios técnicos:</strong>
                        <p>${equipo.comentarios_tecnicos || 'No se proporcionaron comentarios técnicos'}</p>
                    </div>
                    <div class="modal-row">
                        <strong>Logo:</strong>
                        <img src="${equipo.ruta_logo}" alt="Logo del equipo" style="max-width: 150px;">
                    </div>
                    <div class="modal-row">
                        <strong>Portfolio:</strong>
                        <a href="${equipo.ruta_portfolio}" target="_blank">Descargar portfolio</a>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Cerrar modal al hacer clic en la X
        modal.querySelector('.close-modal').addEventListener('click', function() {
            document.body.removeChild(modal);
        });
        
        // Cerrar modal al hacer clic fuera del contenido
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }

    // Actualizar contador de participantes
    function updateParticipantCounter() {
        const formData = new FormData();
        formData.append('accion', 'contar_equipos');
        
        sendAjaxRequest('ajax_handler.php', formData, function(response) {
            if (response.exito && response.datos && response.datos.total_equipos) {
                document.getElementById('totalParticipantes').innerText = response.datos.total_equipos;
            }
        });
    }

    // Cargar contador inicial
    updateParticipantCounter();

    // Mostrar/ocultar overlay de registros
    document.getElementById('participantesCounter').addEventListener('click', function () {
        document.getElementById('registrosOverlay').style.display = 'flex';
        loadRegistrations();
    });

    document.getElementById('closeRegistrosBtn').addEventListener('click', function () {
        document.getElementById('registrosOverlay').style.display = 'none';
    });

    // Función para mostrar toast
    function showToast(message) {
        const toast = document.getElementById('toastNotification');
        toast.textContent = message;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // Navegación suave al hacer clic en enlaces de navegación
    document.querySelectorAll('nav a').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            window.scrollTo({
                top: targetElement.offsetTop - 100,
                behavior: 'smooth'
            });
        });
    });
});