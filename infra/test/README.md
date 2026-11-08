# Vine test environment

One `t4g.small` EC2 (nginx, php-fpm 8.3, MariaDB) + Elastic IP + S3 uploads bucket,
served at https://test.vine-activities.com. No ALB, NAT or RDS (~US$13/month).
Pushes to the `test` branch deploy via GitHub Actions -> SSM (no SSH port open).

## Create
```bash
cd infra/test
cp terraform.tfvars.example terraform.tfvars   # set route53_zone_id + letsencrypt_email
terraform init
terraform apply
```

Then, once:
1. GitHub repo > Settings > Deploy keys: add `terraform output -raw deploy_key_public` (read-only).
2. GitHub repo > Settings > Variables > Actions, add:
   - `AWS_DEPLOY_ROLE_ARN` = `terraform output -raw github_deploy_role_arn`
   - `VINE_TEST_INSTANCE_ID` = `terraform output -raw instance_id`
   - `AWS_REGION` (only if not us-east-1)
3. `git push origin test` -> the workflow does the first deploy (~3-5 min after the instance boots).

Provisioning log on the server: `/var/log/vine-provision.log` (open a shell with
`aws ssm start-session --target <instance_id>`). If the cert step timed out, re-run
`sudo certbot --nginx -d test.vine-activities.com`.

## Notes
- Terraform state is local and contains secrets (DB password, deploy key); do not commit it.
- Test uses file sessions/cache and sync queue; mail is logged only.
- Stop paying: `terraform destroy`.
