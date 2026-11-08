resource "random_id" "app_key" {
  byte_length = 32
}

resource "random_password" "db" {
  length  = 24
  special = false
}

resource "tls_private_key" "deploy" {
  algorithm = "ED25519"
}

locals {
  env_file = <<-EOT
    APP_NAME=Vine
    APP_ENV=staging
    APP_KEY=base64:${random_id.app_key.b64_std}
    APP_DEBUG=true
    APP_URL=https://${var.domain_name}

    LOG_CHANNEL=stack
    LOG_STACK=single
    LOG_LEVEL=debug

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=vine
    DB_USERNAME=vine
    DB_PASSWORD=${random_password.db.result}

    SESSION_DRIVER=file
    SESSION_LIFETIME=120
    CACHE_STORE=file
    QUEUE_CONNECTION=sync
    BROADCAST_CONNECTION=log

    FILESYSTEM_DISK=s3
    AWS_DEFAULT_REGION=${var.region}
    AWS_BUCKET=${aws_s3_bucket.uploads.bucket}
    AWS_USE_PATH_STYLE_ENDPOINT=false

    MAIL_MAILER=log

    GOOGLE_CLIENT_ID=${var.google_client_id}
    SENTRY_LARAVEL_DSN=${var.sentry_dsn}
  EOT
}

resource "aws_ssm_parameter" "env" {
  name  = "/vine-test/env"
  type  = "SecureString"
  tier  = "Advanced" # .env may exceed the 4KB standard limit later
  value = local.env_file
}

resource "aws_ssm_parameter" "deploy_key" {
  name  = "/vine-test/deploy_key"
  type  = "SecureString"
  value = tls_private_key.deploy.private_key_openssh
}
