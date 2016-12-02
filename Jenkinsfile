node('master') {


    currentBuild.result = "SUCCESS"

    try {

        stage 'Checkout'

            checkout scm

        stage 'Test'

        sh 'docker run --rm -v ${PWD}:/data  -w /data --env-file ${PWD}/params.properties olx-inc/composer:5.5 scripts/jenkins/test-unit.sh'

        stage 'Build'

            env.NODE_ENV = "test"

            print "Environment will be : ${env.NODE_ENV}"
            writeFile file: 'params.properties', text: 'BUILD_DIR=/data\n'
            writeFile file: 'params.properties', text: 'BUILD_FILE=octopush-1-master.zip'

            sh 'docker run --rm -v ${PWD}:/data  -w /data --env-file ${PWD}/params.properties olx-inc/composer:5.5 scripts/jenkins/compile.sh'

            step([$class: 'ArtifactArchiver', artifacts: '*.zip', fingerprint: true])


       stage 'Deploy'

            echo 'Push Env'

       stage 'Cleanup'

            mail  body: 'project build successful',
                  from: 'release@olx.com',
                  subject: 'project build successful',
                  to: 'release@olx.com'

        }


    catch (err) {

        currentBuild.result = "FAILURE"

            mail body: "project build error is here: ${env.BUILD_URL}" ,
            from: 'release@olx.com',
            subject: 'project build failed',
            to: 'release@olx.com'

        throw err
    }

}
