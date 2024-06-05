<?php
include("conexion.php");

$user = null;
$alertMessage = '';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['dni'], $_POST['pass'])) {
        $dni = $_POST['dni'];
        $password = $_POST['pass'];

        $stmt = $conn->prepare("SELECT * FROM user WHERE dni = :dni AND pass = :pass");
        $stmt->execute([':dni' => $dni, ':pass' => $password]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $alertMessage = "<div class='alert alert-danger mt-4'>DNI o contraseña incorrectos.</div>";
        }
    } elseif (isset($_POST['iduser'], $_POST['estado'])) {
        $stmt = $conn->prepare("SELECT * FROM fichaje WHERE iduser = :iduser AND estado = :estado AND DATE(hora_dia) = CURDATE()");
        $stmt->execute([':iduser' => $_POST['iduser'], ':estado' => $_POST['estado']]);

        if ($stmt->rowCount() > 0) {
            $alertMessage = "<div class='alert alert-warning mt-4'>Ya has registrado un fichaje de " . htmlspecialchars($_POST['estado']) . " hoy.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO fichaje (hora_dia, iduser, estado) VALUES (NOW(), :iduser, :estado)");
            $stmt->execute([':iduser' => $_POST['iduser'], ':estado' => $_POST['estado']]);

            $alertMessage = "<div class='alert alert-success mt-4'>Fichaje registrado correctamente.</div>";
        }
    }
}


//Buscador
$searchName = '';
if (isset($_POST['search_name'])) {
    $searchName = $_POST['search_name'];
    $stmt = $conn->prepare("
        SELECT user.nombre, fichaje.hora_dia, fichaje.estado 
        FROM fichaje 
        INNER JOIN user ON fichaje.iduser = user.iduser 
        WHERE user.nombre LIKE :nombre 
        AND fichaje.hora_dia >= NOW() - INTERVAL 18 HOUR
    ");
    $stmt->execute([':nombre' => '%' . $searchName . '%']);
} else {
    $stmt = $conn->prepare("
        SELECT user.nombre, fichaje.hora_dia, fichaje.estado 
        FROM fichaje 
        INNER JOIN user ON fichaje.iduser = user.iduser 
        WHERE fichaje.hora_dia >= NOW() - INTERVAL 18 HOUR
    ");
    $stmt->execute();
}
$fichajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/index.css">
    <title>Fichador 3000</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap');

        body {
            background-image: url('assets/img/fondo.jpg');
            background-size: cover;
            background-position: center;

        }

        h1 {
            color: #e49800;
            /* Un blanco suave */
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
            /* Sombra sutil */
            font-family: 'Roboto', sans-serif;
            /* Fuente moderna de Google Fonts */
            font-size: 3.5rem;
            /* Tamaño de fuente mayor */
            text-align: center;
            margin-bottom: 1.5rem;
            /* Espacio adicional debajo */
            border-bottom: 2px solid #343a40;
            /* Borde inferior de color gris oscuro */
            padding-bottom: 0.5rem;
            /* Espacio debajo del texto */
        }


        .table {
            margin-top: 20px;
        }

        label {
            width: 100px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background-color: gray;
            border-color: gray;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <a href="index" class="text-decoration-none">
            <h1 class="text-center mb-4">Fichador 3000</h1>
        </a>

        <?php if ($alertMessage) echo $alertMessage; ?>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Formulario de Registro -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-center">Registro</h5>
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="dni" class="form-label">DNI</label>
                                <input type="text" class="form-control" id="dni" name="dni" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="pass" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Validar</button>
                        </form>
                    </div>
                </div>

                <!-- Formulario de Registrar Fichaje -->
                <?php if ($user) : ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-center">Registrar Fichaje</h5>
                            <div class="d-flex justify-content-between">
                                <form action="" method="post">
                                    <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($user['iduser']); ?>">
                                    <input type="hidden" name="estado" value="Entrada">
                                    <button type="submit" class="btn btn-success">Registrar Entrada</button>
                                </form>
                                <form action="" method="post">
                                    <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($user['iduser']); ?>">
                                    <input type="hidden" name="estado" value="Salida">
                                    <button type="submit" class="btn btn-danger">Registrar Salida</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tabla de Fichajes -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-center">Fichajes</h5>
                        <!-- Formulario de Buscador dentro de la tabla -->
                        <form action="" method="post" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Buscar por nombre" name="search_name" value="<?php echo htmlspecialchars($searchName); ?>">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </form>
                        <table class="table table-striped table-hover mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Hora</th>
                                    <th scope="col">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fichajes as $index => $fichaje) : ?>
                                    <tr>
                                        <th scope="row"><?php echo $index + 1; ?></th>
                                        <td><?php echo htmlspecialchars($fichaje['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($fichaje['hora_dia']); ?></td>
                                        <td><?php echo htmlspecialchars($fichaje['estado']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-cVEBBL62nYIWdFZLkAnm7Bj/6WrWc5Eufl+R8q6rhzjtZYNwGx6vp1Wm8lWl9EPG" crossorigin="anonymous"></script>
</body>

</html>
