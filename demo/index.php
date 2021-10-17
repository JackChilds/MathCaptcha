<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Math Captcha Demo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.9/dist/sweetalert2.min.css" integrity="sha256-v43W/NzPbaavipHsTh1jdc2zWJ1YSTzJlBajaQBPSlw=" crossorigin="anonymous">
</head>
<body>

<div class="container">
    <div class="row mt-5">
        <div class="col">
            <form method="post">
                <p>Please solve this simple maths question so we can verify that you are not a robot.</p>

                <!-- MathCaptcha HTML Code -->
                <div class="input-group mb-2">
                    <!-- Just use the data-mathcaptcha attribute and set it to the reference of the answer input -->
                    <img class="img-thumbnail" data-mathcaptcha="#inputForImage">
                    <input type="number" class="form-control" placeholder="Answer" id="inputForImage" required>
                </div>

                <input type="submit" value="Submit Data" class="btn btn-info">
            </form>
        </div>
    </div>
</div>

<!-- This script will handle the generation of the captcha and keep things simple for you -->
<script src="src/captcha.js"></script>



<!-- SweetAlert2 - An excellent alternative to using horrible built in browser alerts -->
<!-- See: https://sweetalert2.github.io/  -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.9/dist/sweetalert2.min.js" integrity="sha256-IFUKDwtHigsdGMDzLr1pXMyvw9zoG25Tw8Sb4iYaetA=" crossorigin="anonymous"></script>

<?php

if (isset($_POST['captcha_answer']) && isset($_POST['captcha_uid']) && !empty($_POST['captcha_answer']) && !empty($_POST['captcha_uid'])) {
    require 'src/captcha.php';

    $result = captcha_validate($_POST['captcha_uid'], $_POST['captcha_answer']);
    if ($result === 1) {
        // the user is not a robot
        echo "<script>
            Swal.fire({
                icon: 'success',
                text: 'You answered correctly :)'
            });
        </script>";
    } else if ($result === 0) {
        // the user is likely a robot or just really bad at maths
        echo "<script>
            Swal.fire({
                icon: 'error',
                text: 'Your bad at maths :('
            });
        </script>";
    } else {
        // the id doesn't exist or may have expired/deleted
    }
}

?>

</body>
</html>
