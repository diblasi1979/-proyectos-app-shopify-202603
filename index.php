<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Integración con Shopify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <img src="media/img/logo.jpg" alt="Logo Corporativo" class="img-fluid mb-4" style="max-width: 200px;">
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">Formulario de Integración con Shopify</div>
                    <div class="card-body">
                        <form action="func/auth.php" method="POST">
                            <div class="mb-3">
                                <label for="usr_wms" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usr_wms" name="usr_wms" required>
                            </div>
                            <div class="mb-3">
                                <label for="psw_wms" class="form-label">Clave</label>
                                <input type="password" class="form-control" id="psw_wms" name="psw_wms" required>
                            </div>
                            <div class="mb-3">
                                <label for="url_store" class="form-label">URL</label>
                                <input type="text" class="form-control" id="url_store" name="url_store" required>
                            </div>
                            <div class="mb-3">
                                <label for="cuit_store" class="form-label">CUIT</label>
                                <input type="text" class="form-control" id="cuit" name="cuit_store" pattern="\d{11}" title="El CUIT debe tener 11 dígitos" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="text-center mt-4">
    <p>&copy; 2025 - Todos los derechos reservados | <a href="https://www.level5.com.ar" target="_blank">LEVEL5</a></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
