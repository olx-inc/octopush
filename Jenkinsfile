node('master') {


    currentBuild.result = "SUCCESS"

    try {

        stage 'Checkout'

            checkout scm

        stage 'Test'

            withDockerContainer(args: '-v ${PWD}:/data  -w /data -u root:root', image: 'olx-inc/composer:5.5') {
                sh 'scripts/jenkins/test-unit.sh'
            }
            
        stage 'Build'

            env.NODE_ENV = "test"

            print "Environment will be : ${env.NODE_ENV}"

            withDockerContainer(args: '-v ${PWD}:/data  -w /data -e BUILD_DIR=\'/data\' -e BUILD_FILE=\'octopush-1-master.zip\' -u root:root', image: 'olx-inc/composer:5.5') {
                sh 'scripts/jenkins/compile.sh'
            }

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
