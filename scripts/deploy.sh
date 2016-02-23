#!/bin/sh

VERSIONED=true;

while getopts ":s:b:l:r:v:V" o; do
    case "${o}" in
        s)
            STAGE=${OPTARG}
            ;;
        b)
            BRANCH=${OPTARG}
            ;;
        l)
            LEVEL=${OPTARG}
            ;;
        r)
            REMOTE=${OPTARG}
            ;;
        v)
            VERSION=${OPTARG}
            ;;
        V)
            VERSIONED=false
            ;;
    esac
done

if [ -z "${STAGE}" ]
then
    echo "Fatal Error: Missing Stage"
    exit 1
fi

if [ "${STAGE}" != "testing" ] && [ "${STAGE}" != "staging" ]
then
    echo "Fatal Error: Invalid Stage"
    exit 1
fi

if [ -z "${BRANCH}" ]
then
    BRANCH="master"
fi

if [ -z "${LEVEL}" ]
then
    LEVEL="patch"
fi

if [ "${LEVEL}" != "mayor" ] && [ "${LEVEL}" != "minor" ] && [ "${LEVEL}" != "patch" ]
then
    echo "Fatal Error: Invalid Level"
    exit 1
fi

if [ -z "${REMOTE}" ]
then
    REMOTE="origin"
fi

if [ "$VERSIONED" != false ] && [ "${STAGE}" != "staging" ]
then
    VERSIONED="";

    if [ -n "${VERSION}" ]
    then
        VERSIONED="-v $VERSION"
    fi

    ./scripts/versioned.sh -s $STAGE -b $BRANCH -l $LEVEL -r $REMOTE $VERSIONED
fi

GIT_STATUS="$(git status 2> /dev/null)"
if [[ ! ${GIT_STATUS}} =~ "working directory clean" ]]
then
    echo "Fatal Error: Your current branch has changes"
    exit 1
fi

git fetch -p
git fetch -p ${REMOTE} +refs/tags/*:refs/tags/*

if [ -n "${VERSION}" ]
then
    git checkout ${BRANCH}
    git rebase ${REMOTE}/${BRANCH}

    NEXT=v${VERSION}
    EXISTS=$(git tag --list ${NEXT});

    if [ -z "${EXISTS}" ]
    then
        git tag ${NEXT}
    fi
    git tag -f ${STAGE} ${NEXT}

else
    case "${STAGE}" in
        testing)
            git checkout ${BRANCH}
            git rebase ${REMOTE}/${BRANCH}

            HEAD=$(git describe --match=v*.*.* --tags HEAD);
            CURRENT=$(git describe --match=v*.*.* --tags ${STAGE});
            MAYOR=$(echo ${CURRENT} | cut -d '.' -f 1 | cut -d 'v' -f 2)
            MINOR=$(echo ${CURRENT} | cut -d '.' -f 2)
            PATCH=$(echo ${CURRENT} | cut -d '.' -f 3)

            case "${LEVEL}" in
                mayor)
                    ((MAYOR++))
                    MINOR=0
                    PATCH=0
                    ;;
                minor)
                    ((MINOR++))
                    PATCH=0
                    ;;
                patch)
                    ((PATCH++))
                    ;;
            esac

            NEXT=v${MAYOR}.${MINOR}.${PATCH}

            if [ "${HEAD}" == "${CURRENT}" ]
            then
                git tag -d ${CURRENT}
            fi
            git tag ${NEXT}
            git tag -f ${STAGE} ${NEXT}
            ;;
        staging)
            git tag -f ${STAGE} testing
            ;;
    esac
fi

git push --tags --force
