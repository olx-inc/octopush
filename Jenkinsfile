node('node') {


    currentBuild.result = "SUCCESS"

    try {

       stage 'Checkout'

            checkout scm

       stage 'Build'

            env.NODE_ENV = "test"

            print "Environment will be : ${env.NODE_ENV}"

            sh 'scripts/jenkins/compile.sh'

       stage 'Test'

            echo 'Test'

       stage 'Deploy'

            echo 'Push Env'

       stage 'Cleanup'

            mail body: 'project build successful',
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
