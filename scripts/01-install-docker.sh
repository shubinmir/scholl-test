#!/usr/bin/env bash
# Install Docker CE and Docker Compose on Ubuntu/Debian with idempotency and user group setup

set -euo pipefail

if [[ $EUID -ne 0 ]]; then
    echo "Error: This script must be run as root or with sudo" >&2
    exit 1
fi

echo "=== Checking if Docker is already installed ==="
if command -v docker &> /dev/null; then
    echo "Docker is already installed. Skipping installation."
    docker --version
    exit 0
fi

echo "=== Detecting OS ==="
if [[ ! -f /etc/os-release ]]; then
    echo "Error: /etc/os-release not found. Unsupported OS." >&2
    exit 1
fi

source /etc/os-release
DISTRO_ID="${ID,,}"
VERSION_CODENAME="${VERSION_CODENAME:-}"

if [[ "$DISTRO_ID" != "ubuntu" && "$DISTRO_ID" != "debian" ]]; then
    echo "Error: This script supports only Ubuntu and Debian." >&2
    exit 1
fi

echo "Detected: $PRETTY_NAME (ID: $DISTRO_ID, Codename: $VERSION_CODENAME)"

echo "=== Updating package index ==="
apt-get update

echo "=== Removing conflicting packages ==="
apt-get remove -y docker.io docker-doc docker-compose podman-docker containerd runc 2>/dev/null || true

echo "=== Installing prerequisites ==="
apt-get install -y ca-certificates curl gnupg lsb-release

echo "=== Adding Docker GPG key ==="
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/${DISTRO_ID}/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg

echo "=== Adding Docker repository ==="
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/${DISTRO_ID} ${VERSION_CODENAME} stable" | \
    tee /etc/apt/sources.list.d/docker.list > /dev/null
apt-get update

echo "=== Installing Docker packages ==="
apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

echo "=== Configuring user access ==="
TARGET_USER="${SUDO_USER:-${USER}}"
if [[ -z "$TARGET_USER" ]]; then
    echo "Error: Could not determine user (SUDO_USER and USER are both empty)" >&2
    exit 1
fi

getent group docker > /dev/null || groupadd docker
usermod -aG docker "$TARGET_USER"
echo "Added user '$TARGET_USER' to docker group"

echo "=== Enabling and starting Docker service ==="
systemctl enable docker
systemctl start docker

echo ""
echo "=== Installation complete ==="
docker --version
docker compose version
echo ""
echo "Important: User '$TARGET_USER' must log out and log back in (or run 'newgrp docker') for group membership to take effect."