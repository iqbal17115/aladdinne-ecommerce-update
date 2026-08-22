#!/bin/bash

set -e

echo "======================================="
echo "🚀 Production Deployment Started"
echo "======================================="

DEPLOY_USER=$(whoami)
DEPLOY_TIME=$(date +"%Y-%m-%d %H:%M:%S")

# নিশ্চিত করো এটা git repo
if [ ! -d ".git" ]; then
  echo "❌ Not a Git repository."
  exit 1
fi

# Current branch
CURRENT_BRANCH=$(git branch --show-current)
echo "📌 Current branch: $CURRENT_BRANCH"

echo ""
echo "🔍 Checking repository status..."
echo "--------------------------------"
git status --short
echo "--------------------------------"

# ===============================
# FORCE CLEAN (BEST PRACTICE)
# ===============================
echo ""
echo "⚠️ Server will be synced with remote."
echo "⚠️ All local changes will be permanently deleted."

read -p "Do you want to continue? (yes/no): " confirm

if [[ "$confirm" != "yes" ]]; then
  echo "❌ Deployment cancelled."
  exit 1
fi

# ===============================
# Branch নির্বাচন
# ===============================
echo ""
read -p "Deploy from current branch ($CURRENT_BRANCH)? (yes/no): " choice

if [[ "$choice" == "yes" ]]; then
  TARGET_BRANCH=$CURRENT_BRANCH
else
  echo "🔄 Fetching branches..."
  git fetch --all --prune

  mapfile -t branches < <(git branch -r | grep -v HEAD | sed 's/origin\///' | xargs -n1)

  echo "📂 Available branches:"
  for i in "${!branches[@]}"; do
    echo "$((i+1)). ${branches[$i]}"
  done

  read -p "Enter branch number: " num

  if ! [[ "$num" =~ ^[0-9]+$ ]] || [ "$num" -lt 1 ] || [ "$num" -gt "${#branches[@]}" ]; then
    echo "❌ Invalid selection."
    exit 1
  fi

  TARGET_BRANCH="${branches[$((num-1))]}"

  git checkout $TARGET_BRANCH || git checkout -b $TARGET_BRANCH origin/$TARGET_BRANCH
fi

# ===============================
# ALWAYS SAFE SYNC (NO CONFLICT EVER)
# ===============================
echo ""
echo "⬇️ Syncing with remote repository..."

git fetch origin

if [ -z "$TARGET_BRANCH" ]; then
  echo "❌ Branch is empty."
  exit 1
fi

echo "👉 Deploying branch: $TARGET_BRANCH"

git reset --hard origin/$TARGET_BRANCH
git clean -fd

echo "✔ Code synced successfully (clean state)."

# ===============================
# Info
# ===============================
LAST_COMMIT_AUTHOR=$(git log -1 --pretty=format:'%an')
LAST_COMMIT_MSG=$(git log -1 --pretty=format:'%s')

# ===============================
# Laravel optimize
# ===============================
echo ""
echo "🧹 Running Laravel optimize clear..."
php artisan optimize:clear

# ===============================
# Permissions
# ===============================
echo "🔐 Setting permissions..."

chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chmod -R 775 public/fonts 2>/dev/null || true
chmod -R 775 lang 2>/dev/null || true

# ===============================
# Final Summary
# ===============================
echo ""
echo "======================================="
echo "✅ Deployment Summary"
echo "======================================="
echo "✔ Deployed by     : $DEPLOY_USER"
echo "✔ Time            : $DEPLOY_TIME"
echo "✔ Branch          : $TARGET_BRANCH"
echo "✔ Last Author     : $LAST_COMMIT_AUTHOR"
echo "✔ Last Commit     : $LAST_COMMIT_MSG"
echo "✔ Mode            : CLEAN DEPLOY (RESET)"
echo "======================================="

echo ""
echo "🎉 Deployment completed successfully!"
echo "📢 Live from '$TARGET_BRANCH' by $LAST_COMMIT_AUTHOR"
