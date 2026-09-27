<!DOCTYPE html>
<html>

<head>
    <title>Authentication Failed</title>
</head>

<body>

<script>
    window.opener.postMessage(
        {
            type: 'GOOGLE_AUTH_ERROR'
        },
        'http://localhost:3000'
    );

    window.close();
</script>

<p>Google authentication failed.</p>

</body>

</html>