'use strict';

exports.config = {
    optimize: true,
    paths: {
        watched: ["app"],
        public: "web"
    },
    files: {
        stylesheets: {
            "joinTo": "app.min.css"
        },
        javascripts: {
            "joinTo": "app.min.js"
        }
    },
    conventions: {
        ignored: [
            /^app\/cache/,
            /^app\/logs/,
            /^app\/tmp/,
            /^app\/assets/,
            /^app\/locales/,
        ],
    },
    overrides: {
        production: {
            optimize: true,
            sourceMaps: false,
            plugins: {
                autoReload: {
                    enabled: false
                }
            }
        }
    }

}