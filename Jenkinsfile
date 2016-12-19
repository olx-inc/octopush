node('master') {


    currentBuild.result = "SUCCESS"
    env.BUILD_DIR="build/"
    env.BUILD_FILE="octopush-${env.BUILD_NUMBER}.tar.gz"
    def octopush_img=docker.image("quay.io/olx_inc/composer:5.5")

    try {

        stage 'Checkout'

            checkout scm

        stage 'Cleanup'

          sh 'rm -Rf octopush*.tar.gz'

        stage 'Test & Build'

            octopush_img.inside {
                sh 'scripts/jenkins/build.sh'
            }

            step([$class: 'ArtifactArchiver', artifacts: 'build/*.tar.gz', fingerprint: true])

        stage 'Create & Push image'

            env.NODE_ENV = "test"

            print "Environment will be : ${env.NODE_ENV}"

            withCredentials([usernamePassword(credentialsId: '823fa80c-a916-4be5-931d-feb3a3a4f778', passwordVariable: 'PASSWORD', usernameVariable: 'USERNAME')]) {
              sh 'docker login -u="$USERNAME" -p="$PASSWORD" quay.io'
            }

            docker.withRegistry('https://quay.io') {

                def image=docker.build("olx_inc/octopush:${env.BUILD_NUMBER}", "--build-arg VERSION=${env.BUILD_NUMBER} build/")
                image.push()
            }

       stage 'Deploy'

            echo 'Push Env'

       stage 'Communicate'

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
