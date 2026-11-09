<?php

// A fail-closed guard for the existing test-only SSM deployment. Never prints secrets.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}
function requireTest(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "Test deployment refused: {$message}\n");
        exit(1);
    }
}
requireTest(realpath(getcwd()) === '/var/www/vine', 'unexpected application directory');
$nginx = shell_exec('nginx -T 2>&1') ?? '';
requireTest(str_contains($nginx, 'syntax is ok') && str_contains($nginx, 'test is successful'), 'nginx configuration cannot be verified');
requireTest((bool) preg_match('/server_name\s+test\.vine-activities\.com\s*;/', $nginx), 'test virtual host is missing');
requireTest((bool) preg_match('~root\s+/var/www/vine/public\s*;~', $nginx), 'test document root does not match');
requireTest(! preg_match('/server_name\s+[^;]*(?<!test\.)(?<![\w.-])(?:www\.)?vine-activities\.com(?:\s|;)/', $nginx), 'a production virtual host is present');
$origin = trim(shell_exec('git remote get-url origin') ?? '');
// Mina-Nabil/vine redirects to this same repository (GitHub repository ID 868017755).
requireTest((bool) preg_match('~github\.com[:/](?:vineactivities-coder|Mina-Nabil)/vine(?:\.git)?$~i', $origin), 'unexpected repository');
$wrapper = @file_get_contents('/usr/local/bin/vine-deploy') ?: '';
requireTest(str_contains($wrapper, 'git fetch --depth 1 origin test'), 'server deploy wrapper must fetch the test branch');
requireTest(str_contains($wrapper, '/vine-test/deploy_key'), 'server deploy key must be scoped to test');
require 'vendor/autoload.php';
$envFile = $argv[1] ?? '.env';
requireTest(is_readable($envFile), 'environment file is missing');
$values = Dotenv\Dotenv::parse(file_get_contents($envFile));
requireTest(($values['APP_ENV'] ?? '') === 'staging', 'APP_ENV must be staging');
requireTest(rtrim($values['APP_URL'] ?? '', '/') === 'https://test.vine-activities.com', 'APP_URL must be the authorized test host');
requireTest(($values['DB_CONNECTION'] ?? '') === 'mysql', 'unexpected database driver');
requireTest(in_array($values['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true), 'database must be local to the test instance');
requireTest(($values['DB_DATABASE'] ?? '') === 'vine' && ($values['DB_USERNAME'] ?? '') === 'vine', 'unexpected test database or database user');
requireTest(in_array($values['MAIL_MAILER'] ?? '', ['log', 'array'], true), 'test mail must stay local');
$pdo = new PDO('mysql:host='.$values['DB_HOST'].';port='.($values['DB_PORT'] ?? '3306').';dbname=vine', $values['DB_USERNAME'], $values['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
requireTest($pdo->query('SELECT DATABASE()')->fetchColumn() === 'vine', 'connected database differs from test configuration');
echo "Verified test deployment: host=test.vine-activities.com app=/var/www/vine database=local/vine environment=staging mail=local\n";
