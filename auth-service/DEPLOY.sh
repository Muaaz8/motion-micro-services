# ─── Deploy Auth Service to AWS ECS ─────────────────────────────────────────
# Run these commands from your project root (where Dockerfile lives)
# Replace the values in CAPS with your own

# ── Variables ────────────────────────────────────────────────────────────────

AWS_REGION=ap-southeast-1          # change to your region
AWS_ACCOUNT_ID=123456789012        # your 12-digit AWS account ID
REPO_NAME=motion-auth-service
IMAGE_TAG=latest

ECR_URI=$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$REPO_NAME

# ─────────────────────────────────────────────────────────────────────────────
# STEP 1 — Create the ECR repository (only once)
# ─────────────────────────────────────────────────────────────────────────────

aws ecr create-repository \
    --repository-name $REPO_NAME \
    --region $AWS_REGION

# ─────────────────────────────────────────────────────────────────────────────
# STEP 2 — Authenticate Docker to ECR
# ─────────────────────────────────────────────────────────────────────────────

aws ecr get-login-password --region $AWS_REGION \
    | docker login --username AWS --password-stdin \
    $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com

# ─────────────────────────────────────────────────────────────────────────────
# STEP 3 — Build the Docker image
# (run from the root of your auth-service project)
# ─────────────────────────────────────────────────────────────────────────────

docker build -t $REPO_NAME .

# ─────────────────────────────────────────────────────────────────────────────
# STEP 4 — Tag and push to ECR
# ─────────────────────────────────────────────────────────────────────────────

docker tag $REPO_NAME:latest $ECR_URI:$IMAGE_TAG
docker push $ECR_URI:$IMAGE_TAG

# ─────────────────────────────────────────────────────────────────────────────
# STEP 5 — Copy the Image URI for ECS
# ─────────────────────────────────────────────────────────────────────────────

echo "Paste this into ECS Image URI field:"
echo "$ECR_URI:$IMAGE_TAG"

# ─────────────────────────────────────────────────────────────────────────────
# STEP 6 — Environment variables in ECS
# ─────────────────────────────────────────────────────────────────────────────
# In the ECS task definition, under "Environment variables", add:
#
# APP_ENV          = production
# APP_KEY          = base64:your-app-key-here   (php artisan key:generate --show)
# APP_URL          = https://your-domain.com
#
# DB_CONNECTION    = mysql
# DB_HOST          = your-rds-endpoint.rds.amazonaws.com
# DB_PORT          = 3306
# DB_DATABASE      = motion_auth
# DB_USERNAME      = your-db-user
# DB_PASSWORD      = your-db-password
#
# JWT_ALGO         = HS256
# JWT_SECRET       = your-jwt-secret
# JWT_TTL          = 60
# JWT_REFRESH_TTL  = 20160
#
# INTERNAL_SERVICE_SECRET = your-internal-secret
#
# LOG_CHANNEL      = stderr        <-- important: logs go to CloudWatch
# CACHE_DRIVER     = database      <-- or use ElastiCache Redis
# SESSION_DRIVER   = database
# QUEUE_CONNECTION = database

# ─────────────────────────────────────────────────────────────────────────────
# STEP 7 — After ECS task is running, run migrations
# (one-time, via ECS Exec or a separate migration task)
# ─────────────────────────────────────────────────────────────────────────────

# Option A: ECS Exec (easiest)
aws ecs execute-command \
    --cluster your-cluster-name \
    --task your-task-id \
    --container motion-auth-service \
    --command "php artisan migrate --force && php artisan db:seed --class=RolesAndPermissionsSeeder --force" \
    --interactive

# Option B: Run as a one-off ECS task with the same image
# (better for CI/CD pipelines)
