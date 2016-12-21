node('master') {

    currentBuild.result = "SUCCESS"

    env.BUILD_DIR="build/"
    env.BUILD_FILE="octopush-${env.BUILD_NUMBER}.tar.gz"
    env.IMAGE_NAME = "quay.io/olx_inc/octopush:${env.BUILD_NUMBER}"
    

    def octopush_img=docker.image("quay.io/olx_inc/composer:5.5")

    try {

        stage 'Checkout'

            checkout scm

        
        stage 'Cleanup'

            sh 'rm -Rf build/octopush*.tar.gz'

        
        stage 'Test & Build'

            octopush_img.inside {
                sh 'scripts/jenkins/build.sh'
            }

            step([$class: 'ArtifactArchiver', artifacts: "${env.BUILD_DIR}/${env.BUILD_FILE}", fingerprint: true])


        stage 'Create & Push image'
       
            def image=docker.build(env.IMAGE_NAME, "--build-arg VERSION=${env.BUILD_NUMBER} ${env.BUILD_DIR}")
            image.push()


        stage 'Deploy Testing'

            sh "oc tag ${env.IMAGE_NAME} octopush/octopush:latest"

        
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
