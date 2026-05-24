pipeline {
    agent any
    stages {
        stage('Get latest code') {
            steps {
                script {
                    if (fileExists('.git')) {
                        sh 'git pull origin main'
                    } else {
                        git branch: 'main', url: 'https://github.com/kxngHADES/retrade-v2.git'
                    }
                }
            }
        }


        stage('Inject .env files') {
            steps {
                withCredentials([file(credentialsId: 'admin-env', variable: 'ADMIN_ENV')]) {
                    sh 'cp $ADMIN_ENV admin/.env'
                }
                withCredentials([file(credentialsId: 'backend-env', variable: 'BACKEND_ENV')]) {
                    sh 'cp $BACKEND_ENV backend/.env'
                }
                withCredentials([file(credentialsId: 'frontend-env', variable: 'FRONTEND_ENV')]) {
                    sh 'cp $FRONTEND_ENV frontend/.env'
                }
            }
        }

        stage('Build & Deploy') {
            steps {
                sh 'docker-compose up -d'
            }
        }

    }

    post {
        always {
            sh 'rm -f admin/.env backend/.env frontend/.env'
        }
    }
}