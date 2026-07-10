<?php
// Supabase Database Connection Test
$host = 'aws-0-ap-northeast-1.pooler.supabase.com';
$port = '6543';
$dbname = 'postgres';
$username = 'postgres.kfflllferyfdmnsbfaaz';

// We leave the password empty so you can fill it in!
$password = 'Law_08199823';
echo "<h3>Testing Supabase Connection...</h3>";

if ($password === 'YOUR_SUPABASE_PASSWORD') {
    die("<p style='color:orange;'>Please open <b>supabase_test.php</b> and replace 'YOUR_SUPABASE_PASSWORD' with your actual Supabase password before running this!</p>");
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p style='color:green;'><b>✅ SUCCESS!</b> Successfully connected to Supabase PostgreSQL database!</p>";
    
    // Test if the users table exists yet
    $stmt = $pdo->query("SELECT count(*) FROM pg_tables WHERE schemaname = 'public' AND tablename = 'users'");
    if ($stmt->fetchColumn() > 0) {
        echo "<p style='color:blue;'>The 'users' table exists! You have successfully run the postgres_schema.sql file.</p>";
    } else {
        echo "<p style='color:red;'>⚠️ Connection works, but the 'users' table doesn't exist yet! Don't forget to run <b>postgres_schema.sql</b> inside your Supabase SQL Editor.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'><b>❌ CONNECTION FAILED:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
