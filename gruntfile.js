module.exports = function (grunt) {
    require("load-grunt-tasks")(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON("package.json"),

        makepot: {
            options: {
                exclude: ["node_modules/.*"],
                domainPath: "/languages",
                type: "wp-plugin",
                potHeaders: {
                    "report-msgid-bugs-to":
                        "https://github.com/eric-mathison/wpforms-bigmailer-integration/issues",
                    poedit: true,
                    "x-poedit-keywordslist": true,
                },
            },
            files: {
                src: ["**/*.php"],
            },
        },

        addtextdomain: {
            options: {
                textdomain: "wpforms-bigmailer-integration",
                updateDomains: true,
            },
            php: {
                files: {
                    src: ["**/*.php", "!node_modules/**/*.php"],
                },
            },
        },

        version: {
            project: {
                src: ["package.json"],
            },
            main: {
                options: {
                    prefix: "\\*\\s+Version:\\s+|.*VERSION',\\s+'",
                },
                src: ["wpforms-bigmailer-integration.php"],
            },
        },

        clean: {
            build: {
                src: ["build/"],
            },
        },

        copy: {
            build: {
                src: [
                    "**",
                    "!node_modules/**",
                    "!gruntfile.js",
                    "!package.json",
                    "!package-lock.json",
                ],
                dest: "build/",
            },
        },

        compress: {
            build: {
                options: {
                    archive: "build/<%= pkg.name %>.zip",
                },
                files: [
                    {
                        expand: true,
                        cwd: "build/",
                        src: ["**/*"],
                        dest: "<%= pkg.name %>/",
                    },
                ],
            },
        },
    });

    grunt.registerTask("build", ["addtextdomain", "makepot"]);
    grunt.registerTask("release", [
        "clean:build",
        "copy:build",
        "compress:build",
    ]);
};
