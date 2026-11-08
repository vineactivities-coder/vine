resource "aws_eip" "web" {
  domain = "vpc"
}

resource "aws_route53_record" "web" {
  zone_id = var.route53_zone_id
  name    = var.domain_name
  type    = "A"
  ttl     = 60
  records = [aws_eip.web.public_ip]
}
