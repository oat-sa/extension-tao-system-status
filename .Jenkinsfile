pipeline {
    agent {
        label 'builder'
    }
    parameters {
        string(name: 'branch', defaultValue: '')
    }
    environment {
        REPO_NAME='oat-sa/extension-tao-system-status'
        EXT_NAME='taoSystemStatus'
    }
    stages {
        stage('Prepare') {
            steps {
                sh(
                    label : 'Create build directory',
                    script: 'mkdir -p build'
                )
            }
        }
        stage('Install') {
            agent {
                docker {
                    image 'alexwijn/docker-git-php-composer'
                    reuseNode true
                }
            }
            environment {
                HOME = '.'
            }
            options {
                skipDefaultCheckout()
            }
            steps {
                script {
                    String COMMIT_ID = sh (returnStdout: true, script: "git rev-parse HEAD").trim()
                    echo COMMIT_ID
                }
                dir('build') {
                    script {
                        def branch
                        if (env.CHANGE_BRANCH != null) {
                            branch = CHANGE_BRANCH
                        } else {
                            branch = BRANCH_NAME
                        }
                        env.branch = branch
                        writeFile(file: 'composer.json', text: """
                        {
                            "repositories": [
                                {
                                    "type" : "vcs",
                                    "url" : "https://github.com/${REPO_NAME}"
                                }
                            ],
                            "require": {
                                "oat-sa/extension-tao-devtools" : "dev-develop",
                                "${REPO_NAME}" : "dev-${branch}#${GIT_COMMIT}"
                            },
                            "minimum-stability": "dev",
                            "require-dev": {
                                "phpunit/phpunit": "~8.5"
                            }
                        }
                        """
                       )
                    }
                    withCredentials([string(credentialsId: 'jenkins_github_token', variable: 'GIT_TOKEN')]) {
                        sh(
                            label: 'Install/Update sources from Composer',
                            script: "COMPOSER_AUTH='{\"github-oauth\": {\"github.com\": \"$GIT_TOKEN\"}}\' composer update --no-interaction --no-ansi --no-progress --prefer-source"
                        )
                    }
                }
            }
        }
        stage('Tests') {
            parallel {
                stage('Backend Tests') {
                    agent {
                        docker {
                            image 'alexwijn/docker-git-php-composer'
                            reuseNode true
                        }
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        dir('build'){
                            sh(
                                label: 'Run backend tests',
                                script: "./vendor/bin/phpunit ${EXT_NAME}/test"
                            )
                        }
                    }
                }
            }
        }
        stage('Checks') {
            parallel {
                stage('Backend Checks') {
                    agent {
                        docker {
                            image 'alexwijn/docker-git-php-composer'
                            reuseNode true
                        }
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        script {
                            composerJson = readJSON text: readFile('composer.json').toString()
                            try {
                                assert composerJson['require'].toString().indexOf('":"dev-') == -1
                            } catch(Throwable t) {
                                error("dev- dependencies found in composer.json")
                            }
                        }
                    }
                }
            }
        }
    }
}
