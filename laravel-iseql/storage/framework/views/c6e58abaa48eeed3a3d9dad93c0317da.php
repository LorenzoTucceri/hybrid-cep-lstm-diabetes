<!-- resources/views/emails/invitoIscrizione.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* General styles */
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

        /* Main content */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Logo styles */
        .email-logo {
            max-width: 150px;
            height: auto;
            width: 100%;
        }

        /* Paragraph styles */
        p {
            font-size: 14px;
            line-height: 1.5;
        }

        a {
            text-decoration: none;
            color: #007BFF;
        }

        /* Media queries for mobile devices */
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
                            <p>Hello <?php echo e($clienteName); ?> <?php echo e($clienteSurname); ?>,</p>
                        </td>
                    </tr>
                </table>

                <p>My name is <?php echo e($userName); ?> <?php echo e($userSurname); ?>,<br>
                    I invite you to sign up for the app using the following link:<br>
                    <a href="<?php echo e($link); ?>" target="_blank"><?php echo e($link); ?></a><br>
                    to access your personal section.</p>

                <p>Have a great day.</p>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
<?php /**PATH /Users/lorenzotucceri/Progetti/ISEQL/laravel-iseql/resources/views/emails/invitoIscrizione.blade.php ENDPATH**/ ?>