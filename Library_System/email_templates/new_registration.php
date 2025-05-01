<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Student Registration</h2>
        <p>Dear <?= $admin_name ?>,</p>
        <p>A new student has registered and requires approval:</p>
        
        <table>
            <tr><th>Student ID</th><td><?= $student_id ?></td></tr>
            <tr><th>Name</th><td><?= $student_name ?></td></tr>
            <tr><th>Email</th><td><?= $student_email ?></td></tr>
            <tr><th>Program</th><td><?= $program ?></td></tr>
        </table>
        
        <p><a href="<?= $approval_url ?>">Review and Approve Registration</a></p>
    </div>
</body>
</html>