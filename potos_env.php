APP_NAME="SGLP"
APP_ENV=local
APP_KEY=base64:6CTto+roIGhs2QflEC93cX/xm04562XaWgT8c3WDm9Y=
APP_DEBUG=t©rue
APP_URL=https://www.sglp.ga

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=8889
DB_DATABASE=potos_db
DB_USERNAME=potos_db
DB_PASSWORD=passe@2025

# ✅ NOUVELLES VARIABLES MYSQL POUR GROS VOLUMES
# Timeouts en secondes (600 = 10 minutes)
DB_TIMEOUT=600
MYSQL_WAIT_TIMEOUT=600
MYSQL_INTERACTIVE_TIMEOUT=600
MYSQL_NET_READ_TIMEOUT=600
MYSQL_NET_WRITE_TIMEOUT=600

# Taille maximum des paquets (pour gros volumes JSON)
MYSQL_MAX_ALLOWED_PACKET=128M

# SSL (optionnel - laissez vide si pas utilisé)
MYSQL_ATTR_SSL_CA=

# ✅ CONFIGURATION CACHE (pour la Progress Bar)
CACHE_DRIVER=file
# Ou si vous avez Redis disponible :
# CACHE_DRIVER=redis
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=720
CSRF_LIFETIME=480

SESSION_COOKIE=pngdi_session_long
SESSION_EXPIRE_ON_CLOSE=false

SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mail.potos.market
MAIL_PORT=465
MAIL_USERNAME=inscription@potos.market
MAIL_PASSWORD=passe@2025
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@sglp.ga"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

UPLOAD_MAX_FILESIZE=2512M
POST_MAX_SIZE=2024M
MAX_EXECUTION_TIME=98000
MEMORY_LIMIT=5048M