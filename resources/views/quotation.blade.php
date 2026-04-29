<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIRO — Travel Insurance Quotation</title>
    <link rel="stylesheet" href="/css/quotation.css">
</head>
<body>
<div class="card">

    <div id="login-section">
        <h1>AIRO Insurance</h1>
        <h2>Sign in to get a quote</h2>

        <div id="login-error" class="alert alert-error"></div>

        <form id="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn" id="login-btn">Login</button>
        </form>
    </div>

    <div id="quotation-section" class="hidden">
        <h1>AIRO Insurance</h1>
        <span class="logout-link" id="logout-link">Logout</span>

        <div id="quotation-error" class="alert alert-error"></div>

        <form id="quotation-form">
            <div class="form-group">
                <label for="age">Ages (comma-separated)</label>
                <input type="text" id="age" placeholder="e.g. 28,35">
                <span class="field-error" id="error-age"></span>
            </div>
            <div class="form-group">
                <label for="currency_id">Currency</label>
                <select id="currency_id">
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                    <option value="USD">USD</option>
                </select>
                <span class="field-error" id="error-currency_id"></span>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date">
                <span class="field-error" id="error-start_date"></span>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date">
                <span class="field-error" id="error-end_date"></span>
            </div>
            <button type="submit" class="btn" id="quote-btn">Get Quote</button>
        </form>

        <div id="result-box" class="result-box">
            <p>Total: <strong id="result-total"></strong></p>
        </div>
    </div>

</div>

<script src="/js/quotation.js"></script>
</body>
</html>
