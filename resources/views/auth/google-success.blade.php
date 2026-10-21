<!DOCTYPE html>
<html>

<head>
    <title>Authentication Successful</title>
</head>

<body>

<script>
    window.opener.postMessage(
        {
            type: 'GOOGLE_AUTH_SUCCESS',
            code: "{{ $code }}"
        },
        'http://localhost:3000'
    );

    window.close();
</script>

<p>Login successful. You can close this window.</p>

</body>

</html>