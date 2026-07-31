<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>403 - Access Denied</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #f1f5f9;
            font-family: Arial, Helvetica, sans-serif;
        }

        .error-card {
            width: 100%;
            max-width: 520px;
            padding: 45px;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            text-align: center;
        }

        .error-code {
            margin: 0;
            color: #dc2626;
            font-size: 72px;
            font-weight: 900;
        }

        h1 {
            margin: 10px 0;
            color: #0f172a;
        }

        p {
            color: #64748b;
            line-height: 1.6;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            padding: 12px 22px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        .back-btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>

    <div class="error-card">

        <div class="error-code">
            403
        </div>

        <h1>Access Denied</h1>

        <p>
            You do not have permission to access this CRM section.
            Please contact the administrator if you need access.
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="back-btn">
                Back to Login
            </button>
        </form>
    </div>

</body>

</html>