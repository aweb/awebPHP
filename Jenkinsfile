pipeline {
	stages {
		stage('Build') {
			steps {
				echo "start build"
					node {
						checkout scm
							def customImage = docker.build("my-image:${env.BUILD_ID}")
							customImage.inside {
								sh './run.sh'
							}
					}
			}
		}
	}
}
