#!/bin/sh

PROJECT=jarvis
BRANCH=$(echo $GIT_BRANCH | cut -d '/' -f 2)
SHA=$(git rev-parse --short ${GIT_COMMIT})
VERSION=1.0.${BUILD_ID}
SEMVER=${PROJECT}-${VERSION}
RELEASE_FILE=${SEMVER}-${BRANCH}-${SHA}.zip
APP_BUILD_DIR=${WORKSPACE}/build/olx/${PROJECT}


echo "Release_file: ${RELEASE_FILE} App_build_dir: ${APP_BUILD_DIR} Version: ${VERSION} Node: $(node --version)"

rm -rf node_modules \
&& npm install --production \
&& echo $VERSION > TAG \
&& rm -rf ${APP_BUILD_DIR} && mkdir -p ${APP_BUILD_DIR} \
&& zip -r ${APP_BUILD_DIR}/${RELEASE_FILE} . -x '*.git*' -x 'build/*' -x 'tmp/*' -x 'tests/*' -x 'grunt/*' -x 'Gruntfile.js' -x '*.docker*' -x 'Dockertfile' -x 'sonar-project.properties' -x 'README.md' -x 'scripts/*' -x 'apidoc.json' \
&& cat <<EOF > ${APP_BUILD_DIR}/ENV
COMPONENT=${PROJECT}
VERSION=${VERSION}
BRANCH=${BRANCH}
SHA=${GIT_COMMIT}
SEMVER=${SEMVER}
EOF
