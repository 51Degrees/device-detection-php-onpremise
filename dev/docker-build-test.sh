#!/usr/bin/env bash
#
# Local build-and-test helper using Docker.
#
# On Windows, run it from a WSL shell (the project asks for WSL because the
# extension is a Linux/macOS build). It compiles the FiftyOneDegreesHashEngine
# extension in a clean PHP image and runs the fork-safety reproduction for
# issue #38 against the bundled Lite data file, so no licensed data file or host
# toolchain is required.
#
# Usage:
#   dev/docker-build-test.sh [php-version]
# Example:
#   dev/docker-build-test.sh 8.3
#
set -euo pipefail

PHP_VERSION="${1:-8.3}"
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
IMAGE="php:${PHP_VERSION}-cli"
DATA_REL="on-premise/device-detection-cxx/device-detection-data/51Degrees-LiteV4.1.hash"
CSV_REL="on-premise/device-detection-cxx/device-detection-data/20000 User Agents.csv"

if [ ! -f "${REPO_ROOT}/${DATA_REL}" ]; then
    echo "Lite data file not found at ${DATA_REL}." >&2
    echo "Run: git submodule update --init --recursive" >&2
    exit 1
fi

echo "Building and testing with ${IMAGE} ..."

docker run --rm \
    -v "${REPO_ROOT}:/repo:ro" \
    -e DATA="/build/${DATA_REL}" \
    -e CSV="/build/${CSV_REL}" \
    "${IMAGE}" bash -euo pipefail -c '
        echo "==> Installing build tools"
        apt-get update -qq
        DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
            g++ make autoconf swig file >/dev/null
        docker-php-ext-install pcntl >/dev/null

        echo "==> Building the extension"
        cp -a /repo /build
        cd /build/on-premise
        phpize >/dev/null
        ./configure >/dev/null
        make -j"$(nproc)" >/dev/null
        echo "Built: $(ls /build/on-premise/modules/*.so)"

        echo "==> Running the fork-safety reproduction (issue #38)"
        cat > /tmp/ff.ini <<INI
FiftyOneDegreesHashEngine.data_file=${DATA}
extension=/build/on-premise/modules/FiftyOneDegreesHashEngine.so
INI
        FIFTYONE_DATA_FILE="${DATA}" \
        FIFTYONE_WRAPPER="/build/on-premise/FiftyOneDegreesHashEngine.php" \
        FIFTYONE_UA_CSV="${CSV}" \
            php -c /tmp/ff.ini /build/configuration-tests/testForkSafety.php
    '
