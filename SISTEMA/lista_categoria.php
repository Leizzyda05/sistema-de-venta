<?php
// 1. CONEXIÓN A LA BASE DE DATOS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = 'localhost';
$username = 'root';
$password = ''; 
$database = 'sistema_de_venta'; 

$conexion = @mysqli_connect($servername, $username, $password, $database);
mysqli_set_charset($conexion, "utf8"); 

if (!$conexion) {
    die("Error de conexión con la base de datos.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include "include/scripts.php"; ?>
    <title>Lista de Categorías</title>
    
    <style>
        /* Estilos para la ventana de alerta flotante */
        .custom_alert_floating {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #046a5b; /* Verde éxito */
            color: white;
            padding: 16px 25px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            opacity: 1;
            transform: translateY(0);
            transition: right 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
                
        .custom_alert_floating i {
            font-size: 20px;
        }
    </style>
</head>
<body>
    <?php include "include/header.php"; ?>

    <?php if (isset($_GET['status'])): ?>
        <?php 
            // Definimos el mensaje y el color de fondo según el resultado
            $mensaje_delete = "";
            $bg_color = "#046a5b"; // Verde éxito por defecto
            $icono = "fas fa-check-circle";

            if ($_GET['status'] == 'deleted') {
                $mensaje_delete = "¡Categoría eliminada correctamente!";
            } elseif ($_GET['status'] == 'error_has_products') {
                $mensaje_delete = "No se puede eliminar, contiene productos asignados.";
                $bg_color = "#e74c3c"; // Rojo error
                $icono = "fas fa-exclamation-triangle";
            }
        ?>

        <?php if (!empty($mensaje_delete)): ?>
            <style>
                .custom-toast {
                    position: fixed;
                    bottom: 20px;  
                    right: -400px; /* Se mantiene oculto a la derecha */
                    background-color: <?php echo $bg_color; ?>; /* Color dinámico (verde o rojo) */
                    color: #ffffff;
                    padding: 16px 25px;
                    border-radius: 6px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    font-family: 'Open Sans', sans-serif;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    z-index: 9999;
                    transition: right 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                }

                .custom-toast.show {
                    right: 20px; /* Desplazamiento hacia la posición visible */
                }

                .custom-toast i {
                    font-size: 20px;
                }

                .custom-toast .toast-close {
                    margin-left: 15px;
                    cursor: pointer;
                    opacity: 0.7;
                    transition: opacity 0.2s;
                }

                .custom-toast .toast-close:hover {
                    opacity: 1;
                }
            </style>

            <div id="toastDeleteMessage" class="custom-toast">
                <i class="<?php echo $icono; ?>"></i>
                <span><?php echo $mensaje_delete; ?></span>
                <i class="fas fa-times toast-close" onclick="closeToastDelete()"></i>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Ahora sí busca el ID correcto 'toastDeleteMessage'
                    var toast = document.getElementById("toastDeleteMessage");
                    
                    if (toast) {
                        // Aparece deslizándose a los 200 milisegundos
                        setTimeout(function() {
                            toast.classList.add("show");
                        }, 200);

                        // Se desvanece automáticamente después de 4 segundos
                        setTimeout(function() {
                            closeToastDelete();
                        }, 4200);
                    }
                });

                function closeToastDelete() {
                    var toast = document.getElementById("toastDeleteMessage");
                    if(toast) {
                        toast.classList.remove("show");
                        // Remueve el elemento del HTML una vez termine la transición
                        setTimeout(function() {
                            toast.remove();
                        }, 500);
                    }
                }
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
    
    <style>
        .custom-toast {
            position: fixed;
            bottom: 20px;  /* <--- CAMBIADO: Antes decía 'top: 20px', ahora se ancla abajo */
            right: -400px; /* Se mantiene oculto a la derecha */
            background-color: #046a5b; 
            color: #ffffff;
            padding: 16px 25px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: 'Open Sans', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            transition: right 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .custom-toast.show {
            right: 20px; /* Se desliza a su posición visible abajo a la derecha */
        }

        .custom-toast i {
            font-size: 20px;
        }

        .custom-toast .toast-close {
            margin-left: 15px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .custom-toast .toast-close:hover {
            opacity: 1;
        }
    </style>

    <div id="toastMessage" class="custom-toast">
        <i class="fas fa-check-circle"></i>
        <span>¡Categoría actualizada exitosamente!</span>
        <i class="fas fa-times toast-close" onclick="closeToast()"></i>
    </div>

     <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toast = document.getElementById("toastMessage");
            
            // Aparece a los 200 milisegundos de cargar la página
            setTimeout(function() {
                toast.classList.add("show");
            }, 200);

            // Se desvanece automáticamente después de 4 segundos
            setTimeout(function() {
                closeToast();
            }, 4200);
        });

        function closeToast() {
            var toast = document.getElementById("toastMessage");
            if(toast) {
                toast.classList.remove("show");
                // Remueve el elemento del HTML una vez termine la transición
                setTimeout(function() {
                    toast.remove();
                }, 500);
            }
        }
    </script>

<?php endif; ?>
    
    <?php if (isset($_GET['msg']) && ($_GET['msg'] == 'success' || $_GET['msg'] == 'register_success')): ?>
        <?php 
            // Definimos el mensaje correcto según el parámetro recibido
            $mensaje = ($_GET['msg'] == 'register_success') ? "¡Categoría guardada exitosamente!" : "¡Categoría actualizada exitosamente!";
        ?>
        
        <style>
            .custom-toast {
                position: fixed;
                bottom: 20px;  /* Anclado abajo */
                right: -400px; /* Inicia oculto a la derecha */
                background-color: #046a5b; /* Verde éxito */
                color: #ffffff;
                padding: 16px 25px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                font-family: 'Open Sans', sans-serif;
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 9999;
                transition: right 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }

            .custom-toast.show {
                right: 20px; /* Se despliza a la posición visible */
            }

            .custom-toast i {
                font-size: 20px;
            }

            .custom-toast .toast-close {
                margin-left: 15px;
                cursor: pointer;
                opacity: 0.7;
                transition: opacity 0.2s;
            }

            .custom-toast .toast-close:hover {
                opacity: 1;
            }
        </style>

        <div id="toastMessage" class="custom-toast">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $mensaje; ?></span>
            <i class="fas fa-times toast-close" onclick="closeToast()"></i>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var toast = document.getElementById("toastMessage");
                
                // Efecto de deslizamiento desde la derecha
                setTimeout(function() {
                    if(toast) toast.classList.add("show");
                }, 200);

                // Desaparece automáticamente después de 4 segundos
                setTimeout(function() {
                    closeToast();
                }, 4200);
            });

            function closeToast() {
                var toast = document.getElementById("toastMessage");
                if(toast) {
                    toast.classList.remove("show"); // Se desliza hacia la derecha para ocultarse
                    setTimeout(function() {
                        toast.remove(); // Se elimina del HTML
                    }, 500);
                }
            }
        </script>
    <?php endif; ?>

    <section id="container">
        <div class="data_table"> 
            
            <div class="header_table">
                <h1><i class="fas fa-tags"></i> Lista de Categorías</h1>
                <a href="registro_categoria.php" class="btn_new">Crear Categoría</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Categoría</th>
                        <th>Fecha de Registro</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // -------------------------------------------------------------
                    // CONSULTA PARA TRAER TODAS LAS CATEGORÍAS
                    // -------------------------------------------------------------
                    $query = mysqli_query($conexion, "SELECT id_categoria, nombre_categoria, date_add 
                                                      FROM categorias 
                                                      ORDER BY nombre_categoria ASC");                                             
                    
                    if ($query) {
                        $result = mysqli_num_rows($query);
                        if ($result > 0) {
                            
                            $contador = 1; 

                            while ($data = mysqli_fetch_array($query)) {
                                $fecha = date('d-m-Y g:i a', strtotime($data['date_add']));
                    ?>
                                <tr>
                                    <td><?php echo $contador; ?></td>
                                    <td><?php echo $data['nombre_categoria']; ?></td>
                                    <td><?php echo $fecha; ?></td>
                                    <td class="text-center">
                                        <a class="link_edit" href="editar_categoria.php?id=<?php echo $data['id_categoria']; ?>"><i class="fas fa-edit"></i> Editar</a>
                                        <button type="button" class="link_edit btn_delete" style="border: none; cursor: pointer;" onclick="confirmarEliminacion(<?php echo $data['id_categoria']; ?>)">Eliminar</button>
                                    </td>
                                </tr>
                    <?php
                                $contador++; 
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No se encontraron categorías registradas.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center' style='color:red;'>Error: " . mysqli_error($conexion) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro de eliminar esta categoría?',
                text: "¡Esta acción no se puede deshacer!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00a65a', 
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                position: 'center'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'eliminar_categoria.php?id=' + id;
                }
            });
        }
    </script>

    <?php include "include/footer.php"; ?>
</body>
</html>