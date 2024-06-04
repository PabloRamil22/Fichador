<?php
include("conexion.php");

$user = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['dni'], $_POST['pass'])) {
        $dni = $_POST['dni'];
        $password = $_POST['pass'];

        $stmt = $conn->prepare("SELECT * FROM user WHERE dni = :dni AND pass = :pass");
        $stmt->execute([':dni' => $dni, ':pass' => $password]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "<div class='alert alert-danger'>DNI o contraseña incorrectos.</div>";
        }
    } elseif (isset($_POST['iduser'], $_POST['estado'])) {
        $stmt = $conn->prepare("SELECT * FROM fichaje WHERE iduser = :iduser AND estado = :estado AND DATE(hora_dia) = CURDATE()");
        $stmt->execute([':iduser' => $_POST['iduser'], ':estado' => $_POST['estado']]);
    
        if ($stmt->rowCount() > 0) {
            echo "<div class='alert alert-warning'>Ya has registrado un fichaje de " . $_POST['estado'] . " hoy.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO fichaje (hora_dia, iduser, estado) VALUES (NOW(), :iduser, :estado)");
            $stmt->execute([':iduser' => $_POST['iduser'], ':estado' => $_POST['estado']]);
    
            echo "<div class='alert alert-success'>Fichaje registrado correctamente.</div>";
        }
    }
}

$stmt = $conn->prepare("SELECT user.nombre, fichaje.hora_dia, fichaje.estado FROM fichaje INNER JOIN user ON fichaje.iduser = user.iduser");
$stmt->execute();
$fichajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/index.css">
    <title>Fichaje</title>
</head>

<body>
    <div class="container">
        <h1 class="text-center my-4">Fichaje</h1>

        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <form action="" method="post" class="mt-5">
                    <div class="form-group">
                        <label for="dni">DNI</label>
                        <input type="text" class="form-control" id="dni" name="dni">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="pass">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Validar</button>
                </form>

                <?php if ($user) : ?>
                    <div class="d-flex justify-content-between mt-5">
                        <form action="" method="post">
                            <input type="hidden" name="iduser" value="<?php echo $user['iduser']; ?>">
                            <input type="hidden" name="estado" value="Entrada">
                            <button type="submit" class="btn btn-success">Entrar</button>
                        </form>
                        <form action="" method="post">
                            <input type="hidden" name="iduser" value="<?php echo $user['iduser']; ?>">
                            <input type="hidden" name="estado" value="Salida">
                            <button type="submit" class="btn btn-danger">Salir</button>
                        </form>
                    </div>
                <?php endif; ?>

                <table class="table table-striped mt-5">
                    <thead>
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
                                <td><?php echo $fichaje['nombre']; ?></td>
                                <td><?php echo $fichaje['hora_dia']; ?></td>
                                <td><?php echo $fichaje['estado']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
