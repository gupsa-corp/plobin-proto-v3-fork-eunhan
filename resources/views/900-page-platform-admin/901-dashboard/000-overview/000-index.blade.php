<!DOCTYPE html>
<html>
<head>
    <title>Platform Admin Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 20px; margin: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🚀 Platform Admin Dashboard - Test Page</h1>
    
    <div class="card">
        <h2>✅ Database Migrations Completed</h2>
        <p>Organization points tables have been created successfully!</p>
    </div>

    <div class="card">
        <h2>📊 Statistics (Test Data)</h2>
        <p><strong>Organizations:</strong> {{ $stats['organizations']['total'] ?? 0 }}</p>
        <p><strong>Users:</strong> {{ $stats['users']['total'] ?? 0 }}</p>
        <p><strong>Revenue:</strong> ₩{{ number_format($stats['revenue']['total'] ?? 0) }}</p>
        <p><strong>Points:</strong> {{ $stats['points']['total_issued'] ?? 0 }}P</p>
    </div>

    <div class="card">
        <h2>🔗 Available Routes</h2>
        <ul>
            <li><a href="/platform/admin/dashboard/overview">Dashboard Overview</a></li>
            <li><a href="/platform/admin/organizations/list">Organizations List</a></li>
            <li><a href="/platform/admin/organizations/points">Organization Points</a></li>
            <li><a href="/platform/admin/payments/history">Payment History</a></li>
            <li><a href="/platform/admin/users/list">Users List</a></li>
        </ul>
    </div>

    <div class="card">
        <h2>✨ Implementation Summary</h2>
        <ul>
            <li>✅ Database migrations run successfully</li>
            <li>✅ Organization points system implemented</li>
            <li>✅ Platform admin routes restructured</li>
            <li>✅ Payment management system added</li>
            <li>✅ New view structure created</li>
        </ul>
    </div>
</body>
</html>