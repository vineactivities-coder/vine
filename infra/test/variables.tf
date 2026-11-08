variable "region" {
  description = "AWS region for the test environment"
  type        = string
  default     = "us-east-1"
}

variable "route53_zone_id" {
  description = "Route 53 hosted zone ID of vine-activities.com"
  type        = string
}

variable "domain_name" {
  description = "Fully qualified domain served by the test environment"
  type        = string
  default     = "test.vine-activities.com"
}

variable "instance_type" {
  description = "EC2 instance type (ARM). t4g.small = 2GB RAM, enough for composer + vite build"
  type        = string
  default     = "t4g.small"
}

variable "github_repo" {
  description = "GitHub repository (owner/name) that is deployed"
  type        = string
  default     = "Mina-Nabil/vine"
}

variable "deploy_branch" {
  description = "Branch that is deployed to this environment"
  type        = string
  default     = "test"
}

variable "create_github_oidc_provider" {
  description = "Set to false if the account already has the token.actions.githubusercontent.com OIDC provider"
  type        = bool
  default     = true
}

variable "letsencrypt_email" {
  description = "Email for Let's Encrypt expiry notices"
  type        = string
}

variable "google_client_id" {
  description = "GOOGLE_CLIENT_ID used by the app for Google sign-in (optional)"
  type        = string
  default     = ""
}

variable "sentry_dsn" {
  description = "Optional Sentry DSN"
  type        = string
  default     = ""
}
