<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body {
            background-color: #f6f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f6f9fc;
            padding-bottom: 40px;
        }

        .main {
            background-color: #ffffff;
            margin: 40px auto;
            width: 100%;
            max-width: 600px;
            border-radius: 12px;
            border-spacing: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .header {
            background-color: #556ee6;
            padding: 30px;
            text-align: center;
        }

        .content {
            padding: 40px;
            text-align: left;
            color: #495057;
            line-height: 1.6;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #adb5bd;
        }

        .button {
            background-color: #556ee6;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
            margin: 20px 0;
        }

        h1 {
            color: #343a40;
            font-size: 22px;
            margin-top: 0;
        }

        p {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <table class="main">
        <tr>
            <td class="header" style="text-align: center; padding: 30px; background-color: #556ee6;">
                <!-- wrapper del cerchio -->
                <div style="
        display: inline-block;
        width: 60px;
        height: 60px;
        background-color: #ffffff;
        border-radius: 50%;
        text-align: center;
        line-height: 60px;">
                    <img src="{{ asset('assets/images/logo3.png') }}" alt="Logo"
                         style="height:40px; vertical-align:middle;">
                </div>
            </td>
        </tr>
        <tr>
            <td class="content">
                <h1>Password Reset Request</h1>
                <p>Hello <strong>{{ $name }}</strong>,</p>
                <p>We received a request to reset the password for your Glucose Analysis account.</p>
                <p>Click the button below to choose a new password. This link is valid for a limited time only.</p>

                <div style="text-align: center;">
                    <a href="{{ $link }}" class="button">Reset Password</a>
                </div>

                <p>If you did not request this change, you can safely ignore this email; your password will remain
                    unchanged.</p>
            </td>
        </tr>
    </table>
    <div class="footer">
        <p>© {{ date('Year') }} Glucose Analysis. All rights reserved.<br>
            Professional Health Monitoring System.</p>
    </div>
</div>
</body>
</html>
