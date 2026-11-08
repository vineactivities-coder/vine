output "url" {
  value = "https://${var.domain_name}"
}

output "public_ip" {
  value = aws_eip.web.public_ip
}

output "instance_id" {
  description = "Set as GitHub repo variable VINE_TEST_INSTANCE_ID"
  value       = aws_instance.web.id
}

output "github_deploy_role_arn" {
  description = "Set as GitHub repo variable AWS_DEPLOY_ROLE_ARN"
  value       = aws_iam_role.github_deploy.arn
}

output "deploy_key_public" {
  description = "Add to GitHub repo > Settings > Deploy keys (read-only)"
  value       = tls_private_key.deploy.public_key_openssh
}

output "uploads_bucket" {
  value = aws_s3_bucket.uploads.bucket
}
