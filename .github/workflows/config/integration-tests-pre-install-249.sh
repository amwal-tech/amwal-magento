#!/bin/bash

# Run the shared pre-install steps
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/integration-tests-pre-install.sh"

# ---------------------------------------------------------------------------
# Magento 2.4.9+ requires OpenSearch instead of the removed elasticsearch7.
# Patch every install-config-mysql.php the action may have placed on disk.
# ---------------------------------------------------------------------------
echo "Patching install-config-mysql.php for OpenSearch (Magento 2.4.9+)"

patch_install_config() {
    local config_file="$1"
    if [ ! -f "$config_file" ]; then
        return
    fi

    if grep -q "elasticsearch7" "$config_file"; then
        echo "  Patching: $config_file"
        sed -i \
            -e "s/'search-engine' => 'elasticsearch7'/'search-engine' => 'opensearch'/" \
            -e "s/'elasticsearch-host'/'opensearch-host'/" \
            -e "s/'elasticsearch-port'/'opensearch-port'/" \
            "$config_file"
        echo "  Done."
    else
        echo "  Already up to date: $config_file"
    fi
}

# Patch all known locations the action might use
patch_install_config "dev/tests/integration/etc/install-config-mysql.php"
patch_install_config "dev/tests/integration/etc/install-config-mysql.php.dist"

# Also patch any install-config-mysql.php found under .dev-tools (belt-and-suspenders)
find . -path "*/.dev-tools/*/install-config-mysql.php" -exec bash -c 'patch_install_config "$1"' _ {} \;

# ---------------------------------------------------------------------------
# Patch any shell scripts in the action that hardcode elasticsearch7 in
# setup:install commands (e.g., entrypoint.sh from the GitHub Action).
# ---------------------------------------------------------------------------
echo "Patching action scripts that reference elasticsearch7..."

# Find and patch any script that contains elasticsearch7 in setup:install context
find / -name "*.sh" -readable 2>/dev/null | while read -r script; do
    if grep -q "elasticsearch7" "$script" 2>/dev/null; then
        echo "  Patching script: $script"
        sed -i \
            -e 's/--search-engine=elasticsearch7/--search-engine=opensearch/g' \
            -e 's/--search-engine elasticsearch7/--search-engine opensearch/g' \
            -e 's/--elasticsearch-host/--opensearch-host/g' \
            -e 's/--elasticsearch-port/--opensearch-port/g' \
            "$script" 2>/dev/null || true
    fi
done

# Also patch PHP files that define install config arrays
find / -name "install-config-mysql*.php" -readable 2>/dev/null | while read -r config; do
    if grep -q "elasticsearch7" "$config" 2>/dev/null; then
        echo "  Patching config: $config"
        sed -i \
            -e "s/'search-engine' => 'elasticsearch7'/'search-engine' => 'opensearch'/g" \
            -e "s/\"search-engine\" => \"elasticsearch7\"/\"search-engine\" => \"opensearch\"/g" \
            -e "s/'elasticsearch-host'/'opensearch-host'/g" \
            -e "s/'elasticsearch-port'/'opensearch-port'/g" \
            "$config" 2>/dev/null || true
    fi
done

echo "OpenSearch patching complete."

