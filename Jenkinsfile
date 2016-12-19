node('master') {


    currentBuild.result = "SUCCESS"
    env.BUILD_DIR="build/"
    env.BUILD_FILE="octopush-${env.BUILD_NUMBER}.tar.gz"
    def octopush_img=docker.image("quay.io/olx_inc/composer:5.5")

    try {

        stage 'Checkout'

            checkout scm

        stage 'Test & Build'

            octopush_img.inside {
                sh 'scripts/jenkins/build.sh'
            }

            step([$class: 'ArtifactArchiver', artifacts: '*.tar.gz', fingerprint: true])

        stage 'Create & Push image'

            env.NODE_ENV = "test"

            print "Environment will be : ${env.NODE_ENV}"

            docker.withRegistry( url: 'quay.io', credentialsId: '4344abd5-437a-48f7-bca9-981e0bad88fd') {

                docker.build("quay.io/olx_inc/octopush:${env.BUILD_NUMBER}", "--build-arg VERSION=${env.BUILD_NUMBER}").push()
            }

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
