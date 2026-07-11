#!/bin/bash

#
# Script de déploiement générique pour applications PHP/Symfony
# --------------------------------------------------------------
# Objectif: cloner ou mettre à jour un dépôt Git dans un dossier cible,
# installer les dépendances Composer, compiler les assets (optionnel),
# vider le cache, puis ajuster les permissions.
#
# Utilisation (exemples):
#   ./deploy.sh \
#       --repo git@github.com:org/projet.git \
#       --dir /var/www/org/projet \
#       --branch main \
#       --owner deploy:www-data \
#       --env prod
#
#   ./deploy.sh -r git@github.com:org/projet.git -d /var/www/projet -b main
#
# Paramètres:
#   -r, --repo <url>           URL du dépôt Git (obligatoire)
#   -d, --dir <path>           Dossier de déploiement absolu (obligatoire)
#   -b, --branch <name>        Branche à déployer (défaut: main)
#   -o, --owner <user:group>   Propriétaire:group pour chown (défaut: deploy:www-data)
#   -e, --env <env>            Environnement Symfony (défaut: prod)
#       --php <path>           Binaire PHP (défaut: php)
#       --composer <path>      Binaire Composer (défaut: composer)
#       --composer-flags <str> Flags supplémentaires passés à "composer install"
#       --composer-auth <json> Contenu COMPOSER_AUTH (ex: token GitHub pour dépôt privé)
#       --no-composer          Ne pas exécuter composer install
#       --no-assets            Ne pas exécuter la compilation des assets
#       --no-cache-clear       Ne pas vider le cache Symfony
#       --allowed-repo <url>   Restreindre au dépôt exact (sécurité optionnelle)
#       --ssh-key-file <path>  Chemin d'une clé privée SSH à utiliser pour git (optionnel)
#       --ssh-key-content <s>  Contenu de la clé privée SSH (multiligne). Le script créera un fichier temporaire sécurisé
#   -h, --help                 Affiche cette aide
#
# Exigences:
#   - bash, git, PHP, Composer disponibles sur la machine cible
#   - Accès au dépôt (SSH ou HTTPS) configuré
#
# Notes:
#   - Le script est idempotent: il clone si nécessaire, sinon fait un fetch/reset
#   - Les opérations sensibles (nettoyage) sont protégées par des garde-fous
#

set -Eeuo pipefail
umask 0002

# Valeurs par défaut
REPO_URL=""
DEPLOY_DIR=""
BRANCH="main"
OWNER_GROUP="deploy:deploy"
SYMFONY_ENV="prod"
PHP_BIN="php"
COMPOSER_BIN="composer"
COMPOSER_FLAGS="--no-dev --optimize-autoloader --prefer-dist --no-progress --no-interaction"
COMPOSER_AUTH_JSON=""
RUN_COMPOSER=1
RUN_ASSETS=1
RUN_CACHE_CLEAR=1
ALLOWED_REPO=""
FORCE_CLEAN=0
# SSH
SSH_KEY_FILE=""
SSH_KEY_CONTENT=""

usage() {
    sed -n '1,80p' "$0" | sed -n '2,80p' | sed '/^set -Eeuo pipefail/q'
    echo
    echo "Exemples rapides :"
    echo "  $0 -r git@github.com:org/projet.git -d /var/www/projet -b main -o www-data:www-data"
    echo "  $0 --repo https://github.com/org/projet.git --dir /srv/projet --no-assets"
}

# Parsing des arguments (long + courts)
if [[ $# -eq 0 ]]; then
    usage
    exit 1
fi

while [[ $# -gt 0 ]]; do
    case "$1" in
        -r|--repo)
            REPO_URL="${2:-}"; shift 2 ;;
        -d|--dir)
            DEPLOY_DIR="${2:-}"; shift 2 ;;
        -b|--branch)
            BRANCH="${2:-}"; shift 2 ;;
        -o|--owner)
            OWNER_GROUP="${2:-}"; shift 2 ;;
        -e|--env)
            SYMFONY_ENV="${2:-}"; shift 2 ;;
        --php)
            PHP_BIN="${2:-}"; shift 2 ;;
        --composer)
            COMPOSER_BIN="${2:-}"; shift 2 ;;
        --composer-flags)
            COMPOSER_FLAGS="${2:-}"; shift 2 ;;
        --composer-auth)
            COMPOSER_AUTH_JSON="${2:-}"; shift 2 ;;
        --no-composer)
            RUN_COMPOSER=0; shift ;;
        --no-assets)
            RUN_ASSETS=0; shift ;;
        --no-cache-clear)
            RUN_CACHE_CLEAR=0; shift ;;
        --allowed-repo)
            ALLOWED_REPO="${2:-}"; shift 2 ;;
        --ssh-key-file)
            SSH_KEY_FILE="${2:-}"; shift 2 ;;
        --ssh-key-content)
            SSH_KEY_CONTENT="${2:-}"; shift 2 ;;
        -h|--help)
            usage; exit 0 ;;
        *)
            echo "❌ Option inconnue: $1" >&2
            echo
            usage
            exit 1 ;;
    esac
done

# Validation des paramètres requis
if [[ -z "$REPO_URL" || -z "$DEPLOY_DIR" ]]; then
    echo "❌ Paramètres manquants: --repo et --dir sont obligatoires" >&2
    echo
    usage
    exit 1
fi

# Sécurité optionnelle: dépôt autorisé
if [[ -n "$ALLOWED_REPO" && "$REPO_URL" != "$ALLOWED_REPO" ]]; then
    echo "❌ Dépôt non autorisé. Autorisé: $ALLOWED_REPO" >&2
    exit 1
fi

# Garde-fou sur DEPLOY_DIR
if [[ "$DEPLOY_DIR" == "/" || -z "$DEPLOY_DIR" ]]; then
    echo "❌ Dossier de déploiement invalide: $DEPLOY_DIR" >&2
    exit 1
fi
if [[ ! "$DEPLOY_DIR" =~ ^/ ]]; then
    echo "❌ --dir doit être un chemin absolu" >&2
    exit 1
fi

OWNER_USER="${OWNER_GROUP%%:*}"
OWNER_GR="${OWNER_GROUP##*:}"

apply_permissions() {
    echo "🔐 Configuration des permissions..."

    if [ -d "$DEPLOY_DIR/var" ]; then
        if [ -w "$DEPLOY_DIR/var" ]; then
            chmod -R g+rwX "$DEPLOY_DIR/var" 2>/dev/null || true
            chmod g+s "$DEPLOY_DIR/var" 2>/dev/null || true
            chgrp -R "$OWNER_GR" "$DEPLOY_DIR/var" 2>/dev/null || true

            if command -v setfacl >/dev/null 2>&1; then
                setfacl -R -m u:"$OWNER_USER":rwx -m g:"$OWNER_GR":rwx "$DEPLOY_DIR/var" 2>/dev/null || true
                setfacl -d -m u:"$OWNER_USER":rwx -m g:"$OWNER_GR":rwx "$DEPLOY_DIR/var" 2>/dev/null || true
            fi
        else
            echo "ℹ️  var/ non modifié (droits insuffisants)"
        fi
    fi
}

echo "🚀 Début du déploiement sur $DEPLOY_DIR (branche: $BRANCH)"

mkdir -p "$DEPLOY_DIR"

# Configuration dynamique de la clé SSH si fournie
TMP_SSH_KEY=""
if [[ -n "$SSH_KEY_CONTENT" ]]; then
    # Crée un fichier temporaire pour la clé
    TMP_SSH_KEY=$(mktemp -p "${TMPDIR:-/tmp}" deploy_ssh_key.XXXXXX)
    # Forcer permissions restreintes
    PREV_UMASK=$(umask)
    umask 077
    printf "%s\n" "$SSH_KEY_CONTENT" > "$TMP_SSH_KEY"
    umask "$PREV_UMASK"
    chmod 600 "$TMP_SSH_KEY" 2>/dev/null || true
    SSH_KEY_FILE="$TMP_SSH_KEY"
    # Nettoyage à la fin
    trap 'if [[ -n "$TMP_SSH_KEY" && -f "$TMP_SSH_KEY" ]]; then rm -f "$TMP_SSH_KEY"; fi' EXIT
fi

if [[ -n "$SSH_KEY_FILE" ]]; then
    # Assure permissions correctes
    chmod 600 "$SSH_KEY_FILE" 2>/dev/null || true
    export GIT_SSH_COMMAND="ssh -i '$SSH_KEY_FILE' -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new"
fi

if [ ! -d "$DEPLOY_DIR/.git" ]; then
    echo "📥 Premier déploiement - Clone du repository..."

    # Si le dossier existe mais n'est pas un repo git
    if [ -d "$DEPLOY_DIR" ] && [ -n "$(ls -A "$DEPLOY_DIR" 2>/dev/null || true)" ]; then
        echo "❌ Le dossier $DEPLOY_DIR n'est pas vide et n'est pas un repo Git."
        exit 1
    fi

    git clone --branch "$BRANCH" --depth 1 "$REPO_URL" "$DEPLOY_DIR"
    cd "$DEPLOY_DIR"

    setfacl -R -m u:deploy:rwx -m g:www-data:rwx .
    setfacl -R -d -m u:deploy:rwx -m g:www-data:rwx .

else
    echo "🔄 Mise à jour du code..."
    cd "$DEPLOY_DIR"
    git fetch origin
    git reset --hard origin/$BRANCH
fi

if [[ $RUN_COMPOSER -eq 1 ]]; then
    echo "📦 Installation des dépendances Composer (mode $SYMFONY_ENV)..."
    if ! command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
        echo "❌ Composer est introuvable (binaire: $COMPOSER_BIN)." >&2
        exit 1
    fi

    export APP_ENV="$SYMFONY_ENV"
    if [[ -n "$COMPOSER_AUTH_JSON" ]]; then
        export COMPOSER_AUTH="$COMPOSER_AUTH_JSON"
    fi
    COMPOSER_FUND=0 "$COMPOSER_BIN" install $COMPOSER_FLAGS
    if [[ "$SYMFONY_ENV" == "prod" ]]; then
        echo "🔧 Compilation des variables d'environnement (.env.local.php)..."
        "$COMPOSER_BIN" dump-env "$SYMFONY_ENV" || echo "⚠️  dump-env a échoué/ignoré"
    fi
else
    echo "⏭️  Étape Composer ignorée (--no-composer)"
fi

if [[ $RUN_ASSETS -eq 1 ]]; then
    echo "🎨 Installation et compilation des assets..."
    if [ -x "bin/console" ]; then
        "$PHP_BIN" bin/console importmap:install --env="$SYMFONY_ENV" || echo "ℹ️  importmap:install indisponible/échoué, ignoré"
        "$PHP_BIN" bin/console asset:compile --env="$SYMFONY_ENV" || echo "ℹ️  asset:compile indisponible/échoué, ignoré"
    else
        echo "ℹ️  bin/console introuvable; étape assets ignorée"
    fi
else
    echo "⏭️  Étape assets ignorée (--no-assets)"
fi

if [[ $RUN_CACHE_CLEAR -eq 1 ]]; then
    echo "🧹 Nettoyage du cache Symfony..."
    if [ -x "bin/console" ]; then
        "$PHP_BIN" bin/console cache:clear --env="$SYMFONY_ENV" --no-debug || echo "⚠️  cache:clear a échoué/ignoré"
    else
        echo "ℹ️  bin/console introuvable; étape cache ignorée"
    fi
else
    echo "⏭️  Étape cache ignorée (--no-cache-clear)"
fi

echo "🔄 Rechargement de PHP-FPM (flush OPcache)..."
if RELOAD_OUTPUT=$(sudo systemctl reload php8.5-fpm 2>&1); then
    echo "✅ PHP-FPM rechargé"
else
    echo "⚠️ Reload PHP-FPM échoué (sudo requis?)" >&2
    echo "$RELOAD_OUTPUT" >&2
fi

#apply_permissions

echo "📌 Révision déployée: $(git rev-parse --short HEAD 2>/dev/null || echo 'inconnue')"

echo "✅ Déploiement terminé avec succès !"
