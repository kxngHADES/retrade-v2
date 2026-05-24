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

        stage('Generate Compose .env file') {
            steps {
                withCredentials([
                    string(credentialsId: 'MYSQL_ROOT_PASSWORD', variable: 'MYSQL_ROOT_PASSWORD'),
                    string(credentialsId: 'MYSQL_DATABASE', variable: 'MYSQL_DATABASE'),
                    string(credentialsId: 'MYSQL_USER', variable: 'MYSQL_USER'),
                    string(credentialsId: 'MYSQL_PASSWORD', variable: 'MYSQL_PASSWORD'),
                    string(credentialsId: 'MINIO_ROOT_USER', variable: 'MINIO_ROOT_USER'),
                    string(credentialsId: 'MINIO_ROOT_PASSWORD', variable: 'MINIO_ROOT_PASSWORD'),
                    string(credentialsId: 'MONGO_ROOT_USERNAME', variable: 'MONGO_ROOT_USERNAME'),
                    string(credentialsId: 'MONGO_ROOT_PASSWORD', variable: 'MONGO_ROOT_PASSWORD'),
                    string(credentialsId: 'NEO4J_AUTH', variable: 'NEO4J_AUTH'),
                    string(credentialsId: 'QDRANT_API_KEY', variable: 'QDRANT_API_KEY'),
                    string(credentialsId: 'GF_SECURITY_ADMIN_PASSWORD', variable: 'GF_SECURITY_ADMIN_PASSWORD')
                ]) {
                    sh """
                        cat > .env <<EOF
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
MYSQL_DATABASE=${MYSQL_DATABASE}
MYSQL_USER=${MYSQL_USER}
MYSQL_PASSWORD=${MYSQL_PASSWORD}
MINIO_ROOT_USER=${MINIO_ROOT_USER}
MINIO_ROOT_PASSWORD=${MINIO_ROOT_PASSWORD}
MONGO_ROOT_USERNAME=${MONGO_ROOT_USERNAME}
MONGO_ROOT_PASSWORD=${MONGO_ROOT_PASSWORD}
NEO4J_AUTH=${NEO4J_AUTH}
QDRANT_API_KEY=${QDRANT_API_KEY}
GF_SECURITY_ADMIN_PASSWORD=${GF_SECURITY_ADMIN_PASSWORD}
EOF
                    """
                }
            }
        }

        stage('Build & Deploy') {
            steps {
                script {
                    try {
                        sh 'docker compose up -d --build'
                    } catch (Exception e) {
                        sh 'docker-compose up -d --build'
                    }
                }
            }
        }
    }

    post {
        always {
            sh 'rm -f admin/.env backend/.env frontend/.env .env'
        }
    }
}