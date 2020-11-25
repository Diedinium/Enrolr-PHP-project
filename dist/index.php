<?php
require __DIR__ . '/php/classes/_connect.php';
require __DIR__ . '/php/account/_auth.php';

$errorMessage;
$successMessage;

if (!empty($_SESSION['errorMessage'])) {
    $errorMessage = $_SESSION['errorMessage'];
    unset($_SESSION['errorMessage']);
}

if (!empty($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}

if ($account->getAuthenticated()) {
    header("Location: pages/todos.php");
    $connection->close();
    die;
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todr - Home</title>
    <script src="./main.js"></script>
    <link rel="stylesheet" href="./main.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top todr-navbar-top-accent shadow-sm">
        <div class="container">
            <a class="navbar-brand todr-brand-colour-text" href="#"><i class="fas fa-check-double mr-1"></i>Todr</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="#"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/about.php"><i class="fas fa-question"></i> About</a>
                    </li>
                </ul>
                <form class="form-inline my-2 my-lg-0" action="php/account/_auth.php" method="POST" id="logonForm">
                    <input class="form-control mr-sm-2 mb-sm-0 mb-2" type="email" name="email" required placeholder="Email" aria-label="Email">
                    <input class="form-control mr-sm-2" type="password" required name="password" placeholder="Password" aria-label="Password">
                    <button class="btn btn-primary my-2 my-sm-0" type="submit">Login</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="todr-navbar-spacer">
        <div class="container">
            <?php if (!empty($successMessage)) : ?>
                <div class="alert alert-success mt-3">
                    <?= $successMessage ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)) : ?>
                <div class="alert alert-danger mt-3">
                    <?= $errorMessage ?>
                </div>
            <?php endif; ?>
            <div class="jumbotron mt-3 text-center">
                <h1 class="display-4">Welcome to Todr!</h1>
                <p>A basic Todo lister demonstration site written using PHP.</p>
            </div>

            <div class="row">
                <div class="col-md-6 col-12 mb-3 mb-md-0">
                    <div class="card h-100 todr-brand-colour-border">
                        <h4 class="card-header todr-brand-colour-text">
                            Create an account
                        </h4>
                        <div class="card-body">
                            <p>To start making todo lists, create an account using the button below.</p>
                            <a href="pages/createAccount.php" class="btn todr-brand-colour-bg text-white">Create Account</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="card h-100 todr-brand-colour-border">
                        <h4 class="card-header todr-brand-colour-text">
                            Placeholder
                        </h4>
                        <div class="card-body">
                            <p>This is a placeholder untill I think of something useful to put here...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/pages/partials/common.php' ?>

    <footer>
        <hr />
        <div class="container mb-3">
            <span class="text-muted">&copy; Jake Hall</span>
            <span class="float-right text-muted"><?= date("Y") ?></span>
        </div>
    </footer>

    <script>
        $(function() {
            $('#logonForm').validate({
                onkeyup: false,
                onclick: false,
                onfocusout: false,
                showErrors: function(errorMap, errorList) {
                    this.defaultShowErrors();
                    displayErrorToast(errorMap, errorList);
                },
                errorPlacement: function(error, element) {}
            });

            $('input, select').focusout(function() {
                $(this).removeClass('error');
            });
        });
    </script>
</body>

</html>