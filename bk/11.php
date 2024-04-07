<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIFF App</title>
    <!-- Include Line SDK -->
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
</head>

<body>

    <script>
        // Initialize LIFF
        liff.init({
            liffId: '2002365478-n7pXx0jW'
        }).then(() => {
            // Check if Line Login is available
            if (!liff.isInClient()) {
                alert('Please open this app in the Line app.');
            } else {
                // Log in with Line Login
                liff.login();
            }
        });

        // Event listener for when the user logs in
        liff.ready.then(() => {
            if (liff.isLoggedIn()) {
                // Get the Line user profile
                liff.getProfile().then((profile) => {
                    // Access the Line ID
                    const lineUserId = profile.userId;
                    alert('Line ID: ' + lineUserId);
                });
            }
        });
    </script>

</body>

</html>