data "aws_ssm_parameter" "ubuntu_arm64" {
  name = "/aws/service/canonical/ubuntu/server/24.04/stable/current/arm64/hvm/ebs-gp3/ami-id"
}

data "aws_caller_identity" "current" {}

data "aws_iam_policy_document" "ec2_assume" {
  statement {
    actions = ["sts:AssumeRole"]
    principals {
      type        = "Service"
      identifiers = ["ec2.amazonaws.com"]
    }
  }
}

resource "aws_iam_role" "web" {
  name_prefix        = "vine-test-web-"
  assume_role_policy = data.aws_iam_policy_document.ec2_assume.json
}

resource "aws_iam_role_policy_attachment" "ssm_core" {
  role       = aws_iam_role.web.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

data "aws_iam_policy_document" "web" {
  statement {
    sid       = "ReadAppConfig"
    actions   = ["ssm:GetParameter"]
    resources = [aws_ssm_parameter.env.arn, aws_ssm_parameter.deploy_key.arn]
  }

  statement {
    sid       = "DecryptParams"
    actions   = ["kms:Decrypt"]
    resources = ["*"]
    condition {
      test     = "StringEquals"
      variable = "kms:ViaService"
      values   = ["ssm.${var.region}.amazonaws.com"]
    }
  }

  statement {
    sid       = "UploadsBucket"
    actions   = ["s3:PutObject", "s3:GetObject", "s3:DeleteObject", "s3:ListBucket"]
    resources = [aws_s3_bucket.uploads.arn, "${aws_s3_bucket.uploads.arn}/*"]
  }
}

resource "aws_iam_role_policy" "web" {
  name   = "vine-test-web"
  role   = aws_iam_role.web.id
  policy = data.aws_iam_policy_document.web.json
}

resource "aws_iam_instance_profile" "web" {
  name_prefix = "vine-test-web-"
  role        = aws_iam_role.web.name
}

resource "aws_instance" "web" {
  ami                    = data.aws_ssm_parameter.ubuntu_arm64.value
  instance_type          = var.instance_type
  subnet_id              = sort(data.aws_subnets.default.ids)[0]
  vpc_security_group_ids = [aws_security_group.web.id]
  iam_instance_profile   = aws_iam_instance_profile.web.name

  # IMDSv2 only
  metadata_options {
    http_tokens = "required"
  }

  root_block_device {
    volume_type = "gp3"
    volume_size = 12
  }

  user_data = templatefile("${path.module}/scripts/user_data.sh.tpl", {
    domain            = var.domain_name
    region            = var.region
    repo              = var.github_repo
    branch            = var.deploy_branch
    db_password       = random_password.db.result
    letsencrypt_email = var.letsencrypt_email
  })

  tags = {
    Name = "vine-test"
  }

  lifecycle {
    # Config changes are applied by the deploy script; avoid surprise rebuilds
    ignore_changes = [ami, user_data]
  }
}

resource "aws_eip_association" "web" {
  instance_id   = aws_instance.web.id
  allocation_id = aws_eip.web.id
}
