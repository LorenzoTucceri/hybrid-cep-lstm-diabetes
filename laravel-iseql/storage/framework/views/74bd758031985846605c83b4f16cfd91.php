<!-- resources/views/emails/invitoIscrizione.blade.php -->
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Generale */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f9f9f9;
        }
        table {
            width: 100%;
            border-spacing: 0;
        }
        td {
            vertical-align: top;
        }

        /* Contenuto principale */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Stili del logo */
        .email-logo {
            max-width: 150px;
            height: auto;
            width: 100%;
        }

        /* Stili dei paragrafi */
        p {
            font-size: 14px;
            line-height: 1.5;
        }

        a {
            text-decoration: none;
            color: #007BFF;
        }

        /* Media queries per dispositivi mobili */
        @media screen and (max-width: 600px) {
            .email-container {
                padding: 15px;
            }

            .email-logo {
                max-width: 120px;
            }

            p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<table>
    <tr>
        <td align="left">
            <div class="email-container">
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: left;">
                            <p>Ciao <?php echo e($clienteName); ?> <?php echo e($clienteSurname); ?>,</p>
                        </td>
                    </tr>
                </table>

                <p>Sono <?php echo e($userName); ?> <?php echo e($userSurname); ?>,<br>
                    ti invito ad iscriverti all'app tramite il seguente link:<br>
                    <a href="<?php echo e($link); ?>" target="_blank"><?php echo e($link); ?></a><br>
                    per poter accedere nella tua sezione.</p>

                <p>Buona giornata.</p>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
<?php /**PATH /Users/lorenzotucceri/ISEQL-project/resources/views/emails/invitoIscrizione.blade.php ENDPATH**/ ?>