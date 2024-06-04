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

$searchName = '';
if (isset($_POST['search_name'])) {
    $searchName = $_POST['search_name'];
    $stmt = $conn->prepare("SELECT user.nombre, fichaje.hora_dia, fichaje.estado FROM fichaje INNER JOIN user ON fichaje.iduser = user.iduser WHERE user.nombre LIKE :nombre");
    $stmt->execute([':nombre' => '%' . $searchName . '%']);
} else {
    $stmt = $conn->prepare("SELECT user.nombre, fichaje.hora_dia, fichaje.estado FROM fichaje INNER JOIN user ON fichaje.iduser = user.iduser");
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
    <link rel="stylesheet" href="assets/index.css">
    <title>Fichador 3000</title>
</head>

<body>
    <div class="container mt-5">
        <a href="index" class="text-decoration-none text-dark">
            <h1 class="text-center mb-4">Fichador 3000</h1>
        </a>

        <?php if ($alertMessage) echo $alertMessage; ?>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                
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

                
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-center">Buscador</h5>
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="search_name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="search_name" name="search_name" value="<?php echo htmlspecialchars($searchName); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </form>
                    </div>
                </div>

                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-center">Fichajes</h5>
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
</body>

</html>
