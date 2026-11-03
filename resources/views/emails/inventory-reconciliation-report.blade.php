<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Reconciliation Alert</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5; color: #333;">
    <h2>Inventory Reconciliation Alert - FoneX Store</h2>
    <p>Found {{ count($mismatches) }} condition(s) with stock discrepancies:</p>
    <ul>
    @foreach($mismatches as $m)
        <li>
            <strong>#{{ $m['id'] }} {{ $m['product'] }} ({{ $m['grade'] }}, {{ $m['tracked'] ? 'Tracked' : 'Untracked' }}):</strong>
            Stock={{ $m['stock'] }}, Units={{ $m['units'] }}, Movements={{ $m['movements'] }} [{{ $m['discrepancy'] }}]
        </li>
    @endforeach
    </ul>
    <p>Please inspect the inventory audit logs in the admin portal and take appropriate corrective action.</p>
</body>
</html>
