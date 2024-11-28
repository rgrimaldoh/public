document.addEventListener('DOMContentLoaded', function () {
    const continentesSelect = document.getElementById('continentes');
    const paisesSelect = document.getElementById('paises');
    const galeria = document.getElementById('galeria'); // Contenedor para las imágenes de ciudades

    if (!continentesSelect || !paisesSelect || !galeria) return;

    // Obtener continentes
    fetch('/wp-json/custom/v1/continentes')
        .then((response) => response.json())
        .then((data) => {
            if (!Array.isArray(data) || data.length === 0) {
                continentesSelect.innerHTML = '<option>No hay continentes disponibles</option>';
                return;
            }

            // Rellena el combo de continentes
            continentesSelect.innerHTML = '<option value="">Seleccione un continente</option>';
            data.forEach((continente) => {
                const option = document.createElement('option');
                option.value = continente.id;
                option.textContent = continente.nombre;
                continentesSelect.appendChild(option);
            });
        })
        .catch((error) => console.error('Error al cargar continentes:', error));

    // Cambiar continente y cargar países
    continentesSelect.addEventListener('change', function () {
        const continenteId = this.value;

        // Limpia los países y deshabilita el combo
        paisesSelect.innerHTML = '<option value="">Seleccione un país</option>';
        paisesSelect.disabled = true;

        // Limpia la galería
        galeria.innerHTML = '';

        if (continenteId) {
            fetch(`/wp-json/custom/v1/paises/${continenteId}`)
                .then((response) => response.json())
                .then((data) => {
                    if (!Array.isArray(data) || data.length === 0) {
                        paisesSelect.innerHTML = '<option value="">No hay países disponibles</option>';
                        return;
                    }

                    // Limpia los países antes de agregar nuevos
                    paisesSelect.innerHTML = '<option value="">Seleccione un país</option>';

                    // Agregar opciones únicas al combo de países
                    data.forEach((pais) => {
                        if (!paisesSelect.querySelector(`option[value="${pais.id}"]`)) {
                            const option = document.createElement('option');
                            option.value = pais.id;
                            option.textContent = pais.nombre;
                            paisesSelect.appendChild(option);
                        }
                    });

                    paisesSelect.disabled = false;
                })
                .catch((err) => console.error('Error al cargar países:', err));
        }
    });

    // Evento para cargar ciudades
    paisesSelect.addEventListener('change', function () {
        const paisId = this.value;
    
        // Limpia la galería antes de cargar nuevas ciudades
        galeria.innerHTML = '';
    
        if (paisId) {
            fetch(`/wp-json/custom/v1/ciudades/${paisId}`)
                .then((response) => response.json())
                .then((data) => {
                    if (!Array.isArray(data) || data.length === 0) {
                        galeria.innerHTML = '<p>No hay ciudades disponibles.</p>';
                        return;
                    }
                    // Crear contenedor para las ciudades con CSS Grid
                    //galeria.style.display = 'grid';
                    //galeria.style.gridTemplateColumns = 'repeat(6, 1fr)'; // 6 columnas iguales
                    //galeria.style.gap = '20px'; // Espaciado entre las tarjetas

                    // Iterar sobre las ciudades y agregar al contenedor
                    data.forEach((ciudad) => {
                        // Crear fila solo si no existe
                        if (!galeria.querySelector(`.row`)) {
                            const fila = document.createElement('div');
                            fila.className = 'row'; // Clase para la fila
                            galeria.appendChild(fila); // Agregar la fila al contenedor
                        }
                        if (!galeria.querySelector(`[data-id="${ciudad.id}"]`)) { // Evitar duplicados
                            const tarjeta = document.createElement('div');
                            tarjeta.className = 'tarjeta'; // Clase para aplicar los estilos
                            tarjeta.dataset.id = ciudad.id; // Asigna el ID de la ciudad
                    
                            const enlace = document.createElement('a');
                            enlace.href = ciudad.archivo || '#'; // Usa el campo archivo o un fallback
                            enlace.download = ciudad.nombre || 'archivo';
                            enlace.target = '_blank'; // Abrir en nueva pestaña si no descarga automáticamente
                    
                            const img = document.createElement('img');
                            img.src = ciudad.imagen || '/wp-content/uploads/2024/11/pdf-file.png'; // Imagen genérica
                            img.alt = ciudad.nombre;
                    
                            const nombreCiudad = document.createElement('p');
                            nombreCiudad.textContent = ciudad.nombre || 'Sin nombre';
                    
                            const nombreArchivo = document.createElement('p');
                            nombreArchivo.textContent = ciudad.archivo
                                ? ciudad.archivo.split('/').pop()
                                : 'Archivo no disponible';
                    
                            enlace.appendChild(img);
                            tarjeta.appendChild(enlace);
                            tarjeta.appendChild(nombreCiudad);
                            tarjeta.appendChild(nombreArchivo);
                            galeria.appendChild(tarjeta); // Agregar directamente al grid
                        }
                    });

                })
                .catch((err) => {
                    console.error('Error al cargar ciudades:', err);
                    galeria.innerHTML = '<p>Error al cargar las ciudades. Intente nuevamente.</p>';
                });
        }
    });
    
});
