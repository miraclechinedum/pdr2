<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Your Account Credentials</title>
</head>

<body>
    <p>Hi {{ $name }},</p>
    <p>Your account has just been created. Here are your login details:</p>
    <ul>
        <li><strong>NIN:</strong> {{ $nin }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
    </ul>
    <p>Please log in at <a href="{{ url('/login') }}">{{ url('/login') }}</a> and change your password.</p>
    <p>Thank you!</p>
</body>

</html>